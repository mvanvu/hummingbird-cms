<?php

namespace MaiVu\Hummingbird\Lib;

use MaiVu\Hummingbird\Lib\Mvc\View\ViewBase;
use MaiVu\Php\Registry;
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

	public function getTitle()
	{
		return $this->widget->get('title');
	}

	public function getRenderData()
	{
		return [
			'widget' => $this->widget,
		];
	}

	public function getPartialId()
	{
		return $this->widget->get('params.displayLayout', $this->widget->get('manifest.name'));
	}

	public function getContent()
	{
		$content = $this->widget->get('params.content', null);

		if (null !== $content && is_string($content))
		{
			return $content;
		}

		return $this->getRenderer()
			->getPartial('Content/' . $this->getPartialId(), $this->getRenderData());
	}

	public function getRenderer()
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
				TPL_SITE_PATH . '/Tmpl/Widget',
				TPL_SITE_PATH . '/Widget',
				dirname($reflectionClass->getFileName()) . '/Tmpl/',
				TPL_SYSTEM_PATH . '/Widget/',
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
				->getPartial('Wrapper/' . $wrapper, $widgetData);
		}

		return null;
	}
}