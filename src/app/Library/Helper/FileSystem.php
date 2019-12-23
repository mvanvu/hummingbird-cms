<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Exception;

class FileSystem
{
	public static function cleanPath($path)
	{
		return rtrim($path, DIRECTORY_SEPARATOR);
	}

	public static function scanDirs($path, $recurse = false, $excludes = [])
	{
		$arr  = [];
		$path = static::cleanPath($path);

		// Read the source directory
		if (!($handle = @opendir($path)))
		{
			return $arr;
		}

		while (false !== ($file = readdir($handle)))
		{
			if ($file !== '.'
				&& $file !== '..'
				&& !in_array($file, $excludes)
				&& !in_array(preg_replace('#^' . preg_quote(BASE_PATH, '#') . '#', '', $path . '/' . $file), $excludes)
			)
			{
				$fullPath = $path . '/' . $file;

				if (is_dir($fullPath))
				{
					$arr[] = $fullPath;

					if ($recurse)
					{
						$arr = array_merge($arr, static::scanDirs($fullPath, $recurse, $excludes));
					}
				}
			}
		}

		closedir($handle);
		asort($arr);

		return $arr;
	}

	public static function scanFiles($path, $recurse = false, $excludes = [])
	{
		$arr  = [];
		$path = static::cleanPath($path);

		// Read the source directory
		if (!($handle = @opendir($path)))
		{
			return $arr;
		}

		while (false !== ($file = readdir($handle)))
		{
			if ($file !== '.'
				&& $file !== '..'
				&& !in_array($file, $excludes)
				&& !in_array(preg_replace('#^' . preg_quote(BASE_PATH, '#') . '#', '', $path . '/' . $file), $excludes)
			)
			{
				$fullPath = $path . '/' . $file;

				if (!in_array($file, $excludes))

					if (is_file($fullPath))
					{
						$arr[] = $fullPath;
					}
					elseif ($recurse && is_dir($fullPath))
					{
						$arr = array_merge($arr, static::scanFiles($fullPath, $recurse, $excludes));
					}
			}
		}

		closedir($handle);
		asort($arr);

		return $arr;
	}

	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	public static function remove(string $source)
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

	public static function makeSafe($path)
	{
		$regex = '#[^A-Za-z0-9_\\\/\(\)\[\]\{\}\#\$\^\+\.\'~`!@&=;,-]#';

		return preg_replace($regex, '', $path);
	}

	/**
	 * @param   string  $source
	 * @param   string  $dest
	 * @param   bool    $force
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
		}
		elseif (is_dir($source))
		{
			if (!$force && !is_dir($dest))
			{
				throw new Exception('The destination directory not exists.');
			}

			foreach (static::scanFiles($source, true) as $file)
			{
				$dir = $dest . preg_replace('#^' . preg_quote($source, '#') . '#', '', $file, 1);
				static::copy($file, $dir);
			}
		}
	}

	/**
	 * @param   string  $source
	 * @param   string  $dest
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
}