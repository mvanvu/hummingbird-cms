<?php

namespace App\Helper;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Image\Enum;

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
		$this->imageUri      = preg_replace('/^' . preg_quote(BASE_PATH . '/public', '/') . '/', Uri::getHost(), $this->imageFile);
		$this->imageThumbUri = dirname($this->imageUri) . '/thumbs';
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

	public function exists()
	{
		return is_file($this->imageFile);
	}

	public function getResize($width = null, $height = null, $crop = false)
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

			if ($crop)
			{
				$offsetX = (($handler->getWidth() - $width) / 2);
				$offsetY = (($handler->getHeight() - $height) / 2);
				$handler->crop($width, $height, $offsetX, $offsetY);
			}
			else
			{
				$handler->resize($width, $height, $master);
			}

			$handler->save($thumbPath . '/' . $thumbName);
		}

		return $this->imageThumbUri . '/' . $thumbName;
	}

	public function getUri($base = false)
	{
		if ($base)
		{
			return preg_replace('/^' . preg_quote(Uri::getHost(), '/') . '/', '', $this->imageUri);
		}

		return $this->imageUri;
	}
}
