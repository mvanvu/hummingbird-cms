<?php

defined('BASE_PATH') or die;

if (!version_compare(PHP_VERSION, '7.2', 'ge'))
{
	die('Hummingbird CMS require PHP version 7.2 or greater');
}

if (!class_exists('PDO')
	|| !in_array('mysql', PDO::getAvailableDrivers())
)
{
	die('Hummingbird CMS require Pdo Mysql driver version 5.7 or greater');
}

if (!class_exists('Phalcon\\Version')
	|| !version_compare(Phalcon\Version::get(), '4.0', 'ge')
)
{
	die('Hummingbird CMS require Phalcon version 4.0 or greater');
}

$envIniFile = BASE_PATH . '/config.ini';

if (is_file($envIniFile))
{
	die('The INI config file is already exists at ' . $envIniFile . ', remove it and reinstall and don\'t forget you know what you are doing.');
}

use Phalcon\Loader;
use Phalcon\Http\Request;
use Phalcon\Security;
use Phalcon\Db\Adapter\Pdo\Mysql;
use MaiVu\Hummingbird\Lib\Helper\Database;

// We are in a docker if this is a cli env
$request = new Request;

if ($request->isAjax() && $request->isPost())
{
	$appPath = BASE_PATH . '/app';
	$loader  = new Loader;
	$loader->registerNamespaces(
		[
			'MaiVu\\Hummingbird\\Lib' => $appPath . '/Library',
		]
	);

	$loader->register();
	$dbParams = [
		'host'     => $request->getPost('dbHost', 'string', 'mysql'),
		'username' => $request->getPost('dbUser', 'string', 'dbuser'),
		'password' => $request->getPost('dbPass', 'string', 'dbpass'),
		'dbname'   => $request->getPost('dbName', 'string', 'hummingbird_cms'),
		'charset'  => 'utf8mb4',
	];

	try
	{
		$security   = new Security;
		$connection = new Mysql($dbParams);

		// Drop all tables for new install (for dev)
		$listTables = $connection->listTables($dbParams['dbname']);

		foreach ($listTables as $dbTable)
		{
			$connection->execute('DROP TABLE ' . $dbTable);
		}

		do
		{
			$dbPrefix  = $security->getSaltBytes(3);
			$firstChar = substr($dbPrefix, 0, 1);
		} while (is_numeric($firstChar));

		$dbPrefix   = strtolower($dbPrefix . '_');
		$sqlContent = str_replace('#__', $dbPrefix, file_get_contents(BASE_PATH . '/install.sql'));
		$queries    = Database::splitSql($sqlContent);

		foreach ($queries as $query)
		{
			$connection->execute($query);
		}

		$siteName    = $request->getPost('siteName', null, 'Hummingbird CMS');
		$language    = $request->getPost('language', null, 'en-GB');
		$adminPrefix = $request->getPost('adminPrefix', null, 'admin');
		$configData  = $connection->fetchOne('SELECT data FROM ' . $dbPrefix . 'config_data WHERE context = \'cms.config\'');
		$configData  = json_decode($configData['data'], true);

		// Update config
		$configData['siteName']              = $siteName;
		$configData['siteLanguage']          = $language;
		$configData['administratorLanguage'] = $language;
		$configData['administratorLanguage'] = $language;
		$configData['adminPrefix']           = $adminPrefix;
		$configData['development']           = 'Y';
		$connection->execute('UPDATE ' . $dbPrefix . 'config_data SET data = :data WHERE context = :context',
			[
				'context' => 'cms.config',
				'data'    => json_encode($configData),
			]
		);

		// Set ROOT user
		$userSecret = $security->getRandom()->uuid();
		$connection->execute('UPDATE ' . $dbPrefix . 'users SET secret = :secret WHERE username = :admin',
			[
				'secret' => $userSecret,
				'admin'  => 'admin',
			]
		);

		// Create a ini config
		$env = <<<INI
[DB]
HOST   = {$dbParams['host']}
USER   = {$dbParams['username']}
PASS   = {$dbParams['password']}
NAME   = {$dbParams['dbname']}
PREFIX = {$dbPrefix}

[SECRET]
CRYPT_KEY = {$security->hash($security->getRandom()->uuid())}
ROOT_KEY  = {$userSecret}
INI;
		if (false === file_put_contents($envIniFile, $env))
		{
			throw new Exception('Can\'t write the INI config file: ' . $envIniFile);
		}

		$installFile = __DIR__ . '/install.php';
		rename($installFile, $installFile . '-dist');
		chmod($envIniFile, 0444);
		$protocol = 'http';

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		{
			$protocol .= 's';
		}

		$url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . $adminPrefix;

		echo json_encode(
			[
				'success'     => true,
				'message'     => 'Install success.',
				'redirectUrl' => $url,
			]
		);
	}
	catch (Exception $e)
	{
		echo json_encode(
			[
				'success' => false,
				'message' => $e->getMessage(),
			]
		);
	}

	die;
}