<?php

namespace App\Widget;

use App\Mvc\View\ViewBase;
use MaiVu\Php\Registry;
use ReflectionClass;

class Widget
{
	/** @var Registry */
	protected $widget;

	final public function __construct(Registry $widget)
	{
		$this->widget = $widget;

		if (method_exists($this, 'onConstruct'))
		{
			$this->onConstruct();
		}
	}

	public function render($wrapper = null): ?string
	{
		$title   = $this->getTitle();
		$content = $this->getContent();

		if ($title || $content)
		{
			$widgetData = [
				'title'   => $title,
				'content' => $content,
			];

			if (null === $wrapper)
			{
				$wrapper = 'Wrapper';
			}

			return $this->getRenderer()->getPartial('Wrapper/' . $wrapper, $widgetData);
		}

		return null;
	}

	public function getTitle(): ?string
	{
		return $this->widget->get('title');
	}

	public function getContent(): ?string
	{
		$content = $this->widget->get('params.content', null);

		if (null !== $content && is_string($content))
		{
			return $content;
		}

		return $this->getRenderer()->getPartial('Content/' . $this->getPartialId());
	}

	public function getRenderer(): ViewBase
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
				TPL_SITE_PATH . '/Widget',
				dirname($reflectionClass->getFileName()) . '/Tmpl/',
				TPL_SYSTEM_PATH . '/Widget/',
			]
		);


		$renderers[$class]->disable();
		$renderers[$class]->setVars(
			array_merge(
				$this->getRenderData(),
				[
					'instance' => $this,
					'renderer' => $renderers[$class],
					'widget'   => $this->widget,
				]
			)
		);

		return $renderers[$class];
	}

	public function getRenderData(): array
	{
		return [];
	}

	public function getPartialId(): string
	{
		return $this->widget->get('params.displayLayout', $this->widget->get('manifest.name'));
	}
}