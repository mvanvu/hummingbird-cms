<?php

defined('BASE_PATH') or die;

use App\Helper\Database;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Http\Request;
use Phalcon\Loader;
use Phalcon\Security;
use Phalcon\Version;

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
	|| !version_compare(Version::get(), '4.0', 'ge')
)
{
	die('Hummingbird CMS require Phalcon version 4.0 or greater');
}

$configFile = BASE_PATH . '/config.php';
$sqlFile    = BASE_PATH . '/install.sql';

if (is_file($configFile))
{
	die('The config file is already exists, remove it and reinstall and don\'t forget you know what you are doing.');
}

if (!is_file($sqlFile))
{
	die('The data file is not exists.');
}

// We are in a docker if this is a cli env
$request = new Request;

if ($request->isAjax() && $request->isPost())
{
	$appPath = BASE_PATH . '/app';
	(new Loader)->registerNamespaces(['App' => $appPath])->register();

	$dbParams = [
		'host'     => $request->getPost('dbHost', null, 'mysql'),
		'username' => $request->getPost('dbUser', null, 'dbuser'),
		'password' => $request->getPost('dbPass', null, 'dbpass'),
		'dbname'   => $request->getPost('dbName', null, 'hummingbird_cms'),
		'charset'  => 'utf8mb4',
	];

	try
	{
		$security   = new Security;
		$connection = new Mysql($dbParams);

		if (!empty($isEnvDev))
		{
			// Drop all tables for new install (for dev)
			$listTables = $connection->listTables($dbParams['dbname']);

			foreach ($listTables as $dbTable)
			{
				$connection->execute('DROP TABLE ' . $dbTable);
			}
		}

		do
		{
			$dbPrefix  = $security->getSaltBytes(3);
			$firstChar = substr($dbPrefix, 0, 1);
		} while (is_numeric($firstChar));

		$dbPrefix   = strtolower($dbPrefix . '_');
		$sqlContent = str_replace('#__', $dbPrefix, file_get_contents($sqlFile));
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
		$apiSecret  = $security->getRandom()->uuid();
		$now        = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
		$connection->execute('UPDATE ' . $dbPrefix . 'users SET secret = :secret, createdAt =:now, createdBy = 0 WHERE username = :admin',
			[
				'secret' => $userSecret,
				'admin'  => 'admin',
				'now'    => $now,
			]
		);
		$connection->execute('UPDATE ' . $dbPrefix . 'templates SET createdAt =:now, createdBy = 1 WHERE 1',
			[
				'now' => $now,
			]
		);
		$connection->execute('UPDATE ' . $dbPrefix . 'roles SET createdAt =:now, createdBy = 1 WHERE 1',
			[
				'now' => $now,
			]
		);

		// Create a ini config
		$phpContent = <<<PHP_CONTENT
<?php

defined('BASE_PATH') or die;

return [
	'db'          => [
		'host'   => '{$dbParams['host']}',
		'user'   => '{$dbParams['username']}',
		'pass'   => '{$dbParams['password']}',
		'name'   => '{$dbParams['dbname']}',
		'prefix' => '{$dbPrefix}',
	],
	'secret'      => [
		'cryptKey' => '{$security->hash($security->getRandom()->uuid())}',
		'rootKey'  => '{$userSecret}',
		'apiKey'   => '{$apiSecret}',
	],
];
PHP_CONTENT;

		if (false === file_put_contents($configFile, $phpContent))
		{
			throw new Exception('Can\'t write the config file: ' . $configFile);
		}

		$installFile = __DIR__ . '/install.php';
		@rename($installFile, $installFile . '-dist');
		@chmod($configFile, 0444);
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

	exit(0);
}