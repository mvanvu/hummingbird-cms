<?php

namespace App\Helper;

use MaiVu\Php\Filter;

class MetaData
{
	protected $title = null;
	protected $metadata = [];
	protected $customTags = [];

	protected function __construct()
	{
		$this->title    = Config::get('siteName');
		$this->metadata = [
			'keywords'    => Config::get('siteMetaKeys'),
			'description' => Config::get('siteMetaDesc'),
			'rights'      => Config::get('siteContentRights'),
			'robots'      => Config::get('siteRobots') ?: 'index, follow',
		];
	}

	public static function getInstance()
	{
		static $instance = null;

		if (null === $instance)
		{
			$instance = new MetaData;
		}

		return $instance;
	}

	public function addTag(string $name, array $properties = [])
	{
		if ($properties)
		{
			$customTags = [];

			foreach ($properties as $property => $value)
			{
				$customTags[(string) $property] = trim($value);
			}

			$this->customTags[$name][] = $customTags;
		}
	}

	public function setTitle(string $title)
	{
		return $this->setProperty('title', $title);
	}

	protected function setProperty($property, $value)
	{
		$value = trim($value);

		if (!empty($value))
		{
			'title' === $property ? $this->title = $value : $this->metadata[$property] = $value;
		}

		return $this;
	}

	public function setKeys(string $value)
	{
		return $this->setProperty('metaKeys', $value);
	}

	public function setDescription(string $value)
	{
		return $this->setProperty('metaDesc', $value);
	}

	public function setContentRights(string $value)
	{
		return $this->setProperty('siteContentRights', $value);
	}

	public function setRobots(string $robots)
	{
		$this->metadata['siteRobots'] = trim($robots);
	}

	public function render()
	{
		$title = trim(htmlspecialchars($this->title));

		if (empty($title))
		{
			$title = Config::get('siteName');
		}

		$tags = [
			'<meta name="languageIsoCode" content="' . Language::getActiveCode() . '"/>',
			'<title>' . $title . '</title>',
		];

		foreach ($this->metadata as $property => $value)
		{
			if (!empty($value))
			{
				$tags[] = '<meta name="' . $property . '" content="' . htmlspecialchars(Filter::clean($value)) . '"/>';
			}
		}

		$tags[] = '<meta property="og:title" content="' . $title . '"/>';

		if (!empty($this->metadata['description']))
		{
			$tags[] = '<meta property="og:description" content="' . htmlspecialchars(Filter::clean($this->metadata['description'])) . '"/>';
		}

		foreach ($this->customTags as $tagName => $customTags)
		{
			foreach ($customTags as $properties)
			{
				$customTag = '<' . $tagName;

				foreach ($properties as $property => $value)
				{
					$customTag .= ' ' . $property . '="' . htmlspecialchars(Filter::clean($value)) . '"';
				}

				$customTag .= '/>';
				$tags[]    = $customTag;
			}
		}

		return implode(PHP_EOL, array_unique($tags));
	}
}