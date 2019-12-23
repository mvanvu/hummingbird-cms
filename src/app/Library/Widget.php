<?php

namespace MaiVu\Hummingbird\Lib;

use MaiVu\Php\Registry;
use MaiVu\Hummingbird\Lib\Mvc\View\ViewBase;
use ReflectionClass;

class Widget
{
	/** @var Registry */
	protected $widget;

	final public function __construct(Registry $widget)
	{
		$this->widget = $widget;
		$this->onConstruct();
	}

	public function onConstruct()
	{

	}

	protected function getTitle()
	{
		return $this->widget->get('title', null);
	}

	protected function getRenderData()
	{
		return [
			'widget' => $this->widget,
		];
	}

	protected function getPartialId()
	{
		return $this->widget->get('params.displayLayout', $this->widget->get('manifest.name'));
	}

	protected function getContent()
	{
		$content = $this->widget->get('params.content', null);

		if (null !== $content && is_string($content))
		{
			return $content;
		}

		return $this->getRenderer()
			->getPartial('Content/' . $this->getPartialId(), $this->getRenderData());
	}

	protected function getRenderer()
	{
		static $renderers = [];
		$class = get_class($this);

		if (isset($renderers[$class]))
		{
			return $renderers[$class];
		}

		$renderers[$class] = ViewBase::getInstance();
		$reflectionClass   = new ReflectionClass($this);
		$renderers[$class]->setViewsDir(
			[
				TPL_SITE_PATH . '/Tmpl',
				TPL_SITE_PATH,
				dirname($reflectionClass->getFileName()) . '/Tmpl',
				TPL_SYSTEM_PATH,
			]
		);

		$renderers[$class]->disable();

		return $renderers[$class];
	}

	public function render($wrapper = null)
	{
		$title   = $this->getTitle();
		$content = $this->getContent();

		if ($title || $content)
		{
			$widgetData = [
				'widget'  => $this->widget,
				'title'   => $title,
				'content' => $content,
			];

			if (null === $wrapper)
			{
				$wrapper = 'Wrapper';
			}

			return $this->getRenderer()
				->getPartial('Widget/Wrapper/' . $wrapper, $widgetData);
		}

		return null;
	}
}