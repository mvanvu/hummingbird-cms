<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Image\Enum;
use function MaiVu\Hummingbird\Lib\debugVar;

class Image
{
	protected $imageUri;
	protected $imageFile;
	protected $imageThumbUri;

	public function __construct($imageFile)
	{
		if (strpos($imageFile, BASE_PATH . '/public/upload/') !== 0)
		{
			$imageFile = BASE_PATH . '/public/upload/' . $imageFile;
		}

		$this->imageFile     = $imageFile;
		$this->imageUri      = str_replace(BASE_PATH . '/public', ROOT_URI, $this->imageFile);
		$this->imageThumbUri = dirname($this->imageUri) . '/thumbs';
	}

	public function getResize($width = null, $height = null)
	{
		if (null === $width && null === $height)
		{
			$width = 100;
		}

		preg_match('#^.*(\.[^.]*)$#', $this->imageFile, $matches);
		$extension = $matches[1];
		$thumbName = basename($this->imageFile, $extension) . '_' . ($width ?: 0) . 'x' . ($height ?: 0) . $extension;
		$thumbPath = dirname($this->imageFile) . '/thumbs';

		if (!is_file($thumbPath . '/' . $thumbName))
		{
			if (!is_dir($thumbPath))
			{
				mkdir($thumbPath, 0755, true);
			}

			if ($width && $height)
			{
				$master = Enum::AUTO;
			}
			elseif ($width)
			{
				$master = Enum::WIDTH;
			}
			else
			{
				$master = Enum::HEIGHT;
			}

			$handler = new Gd($this->imageFile);
			$handler->resize($width, $height, $master);
			$handler->save($thumbPath . '/' . $thumbName, 90);
		}

		return $this->imageThumbUri . '/' . $thumbName;
	}

	public function getUri()
	{
		return $this->imageUri;
	}

	public function exists()
	{
		return is_file($this->imageFile);
	}

	public static function loadImage($imageString, $returnFirst = true)
	{
		$imageString = trim($imageString);
		$imageList   = [];

		if (strpos($imageString, '[') === 0
			|| strpos($imageString, '{') === 0
		)
		{
			$images = json_decode($imageString, true) ?: [];
		}
		else
		{
			$images = [$imageString];
		}

		if ($images)
		{
			foreach ($images as $image)
			{
				$handler = new Image($image);

				if ($handler->exists())
				{
					$imageList[] = $handler;
				}
			}

			if ($imageList)
			{
				return $returnFirst ? $imageList[0] : $imageList;
			}
		}

		return false;
	}
}
