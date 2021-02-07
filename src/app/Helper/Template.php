<?php

namespace App\Helper;

use App\Mvc\Model\Template as Tmpl;

class Template
{
	/**
	 * @var Tmpl | null
	 */
	protected static $defaultTemplate = null;

	/**
	 * @var boolean
	 */
	protected static $canChangeDefaultTemplate = true;

	public static function getTemplatePath($id = 'default')
	{
		$path = APP_PATH . '/Tmpl/Site/Template-' . (static::getTemplate($id)->id ?? $id);

		if (!is_dir($path))
		{
			$path = APP_PATH . '/Tmpl/System/Template/Site/Default';
		}

		return $path;
	}

	public static function getTemplate($id = 'default')
	{
		$templates = static::getTemplates();

		if ($id === 'default' || !isset($templates[$id]))
		{
			return static::$defaultTemplate;
		}

		return $templates[$id];
	}

	public static function getTemplates()
	{
		static $templates = null;

		if (null === $templates)
		{
			$templates = [];

			foreach (Tmpl::find(['order' => 'name asc']) as $template)
			{
				if ($template->isDefault === 'Y')
				{
					static::setDefaultTemplate($template);
				}

				$templates[$template->id] = $template;
			}
		}

		return $templates;
	}

	public static function setDefaultTemplate(Tmpl $template)
	{
		if (static::$canChangeDefaultTemplate)
		{
			static::$defaultTemplate = $template;
		}
	}

	public static function canChangeDefaultTemplate(): bool
	{
		return static::$canChangeDefaultTemplate;
	}

	public static function initialize()
	{
		if (Uri::isClient('site'))
		{
			$templates = static::getTemplates();

			foreach (Menu::getActiveSiteMenus() as $menu)
			{
				if ($menu->templateId && isset($templates[$menu->templateId]))
				{
					static::setDefaultTemplate($templates[$menu->templateId]);
					static::$canChangeDefaultTemplate = false;
					break;
				}
			}
		}
	}
}