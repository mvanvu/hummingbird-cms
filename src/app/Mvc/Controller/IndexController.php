<?php

namespace App\Mvc\Controller;

use Phalcon\Mvc\View;
use App\Helper\Config;
use stdClass;

class IndexController extends ControllerBase
{
	public function indexAction()
	{
		$metadata                = new stdClass;
		$metadata->metaTitle     = Config::get('siteName');
		$metadata->metaDesc      = Config::get('siteMetaDesc');
		$metadata->metaKeys      = Config::get('siteMetaKeys');
		$metadata->contentRights = Config::get('siteContentRights');
		$metadata->metaRobots    = Config::get('siteRobots');
		$this->view->setVar('metadata', $metadata);
		$this->view->disableLevel(View::LEVEL_ACTION_VIEW);
	}
}