<?php

namespace App\Helper;

use Exception;
use ZipArchive;

class FileSystem
{
	public static function stripExt(string $file): string
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	public static function findInPath(string $needed, string $path, bool $recurse = false): ?string
	{
		$found   = null;
		$path    = static::cleanPath($path);
		$subDirs = [];

		// Read the source directory
		if (!is_dir($path) || !($handle = @opendir($path)))
		{
			return null;
		}

		while (false !== ($file = readdir($handle)))
		{
			if ($found)
			{
				return $found;
			}

			if ($file !== '.' && $file !== '..')
			{
				$fullPath = $path . '/' . $file;

				if (is_file($fullPath) && $file === $needed)
				{
					$found = $fullPath;
				}
				elseif ($recurse && is_dir($fullPath))
				{
					$subDirs[] = $fullPath;
				}
			}
		}

		closedir($handle);

		if ($subDirs)
		{
			while ($subDirs)
			{
				$tmp = [];

				foreach ($subDirs as $subDir)
				{
					if ($found = static::findInPath($needed, $subDir))
					{
						return $found;
					}

					$tmp = array_merge($tmp, static::scanDirs($subDir));
				}

				$subDirs = $tmp;
			}
		}

		return $found;
	}

	public static function cleanPath($path): string
	{
		return rtrim($path, '/\\\\');
	}

	public static function scanDirs(string $path, $recurse = false, $callback = null)
	{
		$arr         = [];
		$path        = static::cleanPath($path);
		$hasCallback = $callback && is_callable($callback);

		// Read the source directory
		if (!($handle = @opendir($path)))
		{
			return $arr;
		}

		while (false !== ($file = readdir($handle)))
		{
			if ($file !== '.' && $file !== '..')
			{
				$fullPath = $path . '/' . $file;

				if (is_dir($fullPath))
				{
					if ($hasCallback && is_bool($result = call_user_func($callback, $fullPath)))
					{
						if ($result)
						{
							closedir($handle);

							return $fullPath;
						}

						continue;
					}

					$arr[] = $fullPath;

					if ($recurse)
					{
						$arr = array_merge($arr, static::scanDirs($fullPath, $recurse, $callback));
					}
				}
			}
		}

		closedir($handle);
		asort($arr);

		return $arr;
	}

	/**
	 * @param string $source
	 * @param string $dest
	 *
	 * @throws Exception
	 */

	public static function move(string $source, string $dest)
	{
		$source = static::cleanPath($source);
		$dest   = static::cleanPath($dest);

		if (!file_exists($source))
		{
			throw new Exception('The copy source is not exists.');
		}

		if (!@rename($source, $dest))
		{
			throw new Exception('Failed to move ' . $source . ' to ' . $dest);
		}
	}

	public static function sizeToBytes($value)
	{
		$unit  = $value[strlen($value) - 1];
		$value = (int) $value;

		switch ($unit)
		{
			case 'M':
			case 'm':
				return $value * 1048576;

			case 'K':
			case 'k':
				return $value * 1024;

			case 'G':
			case 'g':
				return $value * 1073741824;

			default:
				return $value;
		}
	}

	public static function isImage($fileName)
	{
		static $imageTypes = 'xcf|odg|gif|jpg|jpeg|png|bmp|webp|svg';

		return preg_match('/\.(?:' . $imageTypes . ')$/i', $fileName);
	}

	public static function write($file, $buffer)
	{
		if (function_exists('set_time_limit') && function_exists('ini_get'))
		{
			@set_time_limit(ini_get('max_execution_time'));
		}

		return is_int(file_put_contents(static::cleanPath($file), $buffer));
	}

	/**
	 * @param string $folder
	 * @param int    $chunk
	 *
	 * @throws Exception
	 */

	public static function streamFolder(string $folder, string $filename = null, int $chunk = 1)
	{
		if (!is_dir(TMP_PATH))
		{
			mkdir(TMP_PATH, 0755);
		}

		$package = TMP_PATH . '/' . basename($folder) . '.' . time() . '.zip';
		$zip     = new ZipArchive;

		if (true !== $zip->open($package, ZipArchive::CREATE | ZipArchive::OVERWRITE))
		{
			throw new Exception('Can\'t open the ZipArchive.');
		}

		FileSystem::scanFiles(
			$folder,
			true,
			function ($file) use ($zip, $folder) {
				$zip->addFile($file, preg_replace('#^' . preg_quote($folder, '#') . '/#', '', $file));
			}
		);
		$zip->close();
		FileSystem::stream($package, $filename, $chunk, true);
	}

	public static function scanFiles(string $path, bool $recurse = false, $callback = null)
	{
		$arr         = [];
		$path        = static::cleanPath($path);
		$hasCallback = $callback && is_callable($callback);

		// Read the source directory
		if (!is_dir($path) || !($handle = @opendir($path)))
		{
			return $arr;
		}

		while (false !== ($file = readdir($handle)))
		{
			if ($file !== '.' && $file !== '..')
			{
				$fullPath = $path . '/' . $file;

				if (is_file($fullPath))
				{
					if ($hasCallback && is_bool($result = call_user_func($callback, $fullPath)))
					{
						if ($result)
						{
							closedir($handle);

							return $fullPath;
						}

						continue;
					}

					$arr[] = $fullPath;
				}
				elseif ($recurse && is_dir($fullPath))
				{
					$arr = array_merge($arr, static::scanFiles($fullPath, $recurse, $callback));
				}
			}
		}

		closedir($handle);
		asort($arr);

		return $arr;
	}

	/**
	 * @param string      $file
	 * @param string|null $filename
	 * @param int         $chunk
	 * @param bool        $removeWhenDone
	 *
	 * @throws Exception
	 */

	public static function stream(string $file, string $filename = null, int $chunk = 1, bool $removeWhenDone = false)
	{
		if (!is_file($file))
		{
			throw new Exception('File not found', 404);
		}

		if (function_exists('ini_get'))
		{
			if (function_exists('ini_set') && ini_get('zlib.output_compression'))
			{
				ini_set('zlib.output_compression', 'Off');
			}

			if (function_exists('set_time_limit') && !ini_get('safe_mode'))
			{
				@set_time_limit(0);
			}
		}

		if (null === $filename)
		{
			$filename = static::makeSafe(basename($file));
		}

		$headers = [
			'Content-Disposition'       => 'attachment; filename="' . $filename . '"',
			'Cache-Control'             => 'no-store, no-cache',
			'Pragma'                    => 'no-cache',
			'Accept-Ranges'             => 'bytes',
			'Content-Transfer-Encoding' => 'binary',
			'Connection'                => 'close',
		];

		switch (strtolower(static::getExt($file)))
		{
			case 'zip':
				$headers['Content-Type'] = 'application/zip';
				break;

			default:
				$headers['Content-Type'] = 'application/octet-stream';
				break;
		}

		@ob_end_clean();
		@clearstatcache();
		$response = Service::response();

		foreach ($headers as $name => $value)
		{
			$response->setHeader($name, $value);
		}

		$response->sendHeaders();
		flush();

		$blockSize = $chunk * 1048576; // 1048576 = 1M chunks
		$handle    = @fopen($file, 'r');

		if ($handle !== false)
		{
			while (!@feof($handle))
			{
				echo @fread($handle, $blockSize);
				@ob_flush();
				flush();
			}
		}

		if ($handle !== false)
		{
			@fclose($handle);
		}

		if ($removeWhenDone)
		{
			static::remove($file);
		}

		exit(0);
	}

	public static function makeSafe($path)
	{
		$regex = '#[^A-Za-z0-9_\\\/\(\)\[\]\{\}\#\$\^\+\.\'~`!@&=;,-]#';

		return preg_replace($regex, '', $path);
	}

	public static function getExt($file)
	{
		preg_match('/\.([a-z0-9]+)(\?.*)?$/i', $file, $matches);

		return $matches[1] ?? '';
	}

	public static function remove(string $source): bool
	{
		@chmod($source, 0777);

		if (is_file($source))
		{
			return @unlink($source);
		}

		if (is_dir($source))
		{
			// Read the source directory
			if (!($handle = @opendir($source)))
			{
				return false;
			}

			while (false !== ($file = readdir($handle)))
			{
				if ($file !== '.' && $file !== '..')
				{
					if (!static::remove($source . '/' . $file))
					{
						return false;
					}
				}
			}

			closedir($handle);

			return @rmdir($source);
		}

		return true;
	}

	/**
	 * @param string $source
	 * @param string $dest
	 * @param bool   $force
	 *
	 * @throws Exception
	 */

	public static function copy(string $source, string $dest, bool $force = true)
	{
		$source = static::cleanPath($source);
		$dest   = static::cleanPath($dest);

		if (!file_exists($source))
		{
			throw new Exception('The copy source is not exists.');
		}

		if (is_file($source))
		{
			$dir = dirname($dest);

			if (!$force && !is_dir($dir))
			{
				throw new Exception('The destination directory not exists.');
			}

			if (!is_dir($dir))
			{
				mkdir($dir, 0755, true);
			}

			if (!@copy($source, $dest))
			{
				throw new Exception('Failed to copy ' . $source . ' to ' . $dest);
			}

			@chmod($dest, 0644);
		}
		elseif (is_dir($source))
		{
			if (!$force && !is_dir($dest))
			{
				throw new Exception('The destination directory not exists.');
			}

			static::scanFiles($source, true, function ($file) use ($dest, $source) {
				$dir = $dest . preg_replace('#^' . preg_quote($source, '#') . '#', '', $file, 1);
				static::copy($file, $dir);
			});
		}
	}

	public static function download(string $url, string $path, int $chunk = 1): bool
	{
		$blockSize   = $chunk * 1048576; // 1048576 = 1M chunks
		$readHandle  = @fopen($url, 'rb');
		$writeHandle = @fopen($path, 'wb');

		if ($readHandle !== false && $writeHandle !== false)
		{
			while (!@feof($readHandle))
			{
				fwrite($writeHandle, fread($readHandle, $blockSize), $blockSize);
			}

			@fclose($readHandle);
			@fclose($writeHandle);

			return true;
		}

		return false;
	}

	public static function splitExt(&$file)
	{
		$regex = '/\.([a-z0-9]+)(\?.*)?$/i';
		preg_match($regex, $file, $matches);
		$file = preg_replace($regex, '', $file);

		return $matches[1] ?? '';
	}
}