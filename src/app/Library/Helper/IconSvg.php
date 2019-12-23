<?php

namespace MaiVu\Hummingbird\Lib\Helper;

class IconSvg
{
	public static function render($name, $width = 20, $height = 20)
	{
		static $iconCss = false;

		if (strpos($name, '<') === 0)
		{
			return $name;
		}

		if (!$iconCss)
		{
			$iconCss = true;
			Asset::addFile('icon.css');
		}

		$icon = ROOT_URI . '/assets/images/icons.svg';

		return <<<SVG
<svg class="icon-{$name}" width="{$width}" height="{$height}"><use xlink:href="{$icon}#icon-{$name}"></use></svg>
SVG;

	}
}
