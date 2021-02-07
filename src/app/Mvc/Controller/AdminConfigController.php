<?php

namespace App\Mvc\Controller;

use App\Factory\Factory;
use App\Helper\Assets;
use App\Helper\Config as CMSConfig;
use App\Helper\Mail as MailHelper;
use App\Helper\Text;
use App\Helper\Toolbar;
use App\Mvc\Model\Config as ConfigModel;
use App\Traits\Permission;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class AdminConfigController extends AdminControllerBase
{
	/**
	 * @var ConfigModel
	 */
	public $model = 'Config';

	/**
	 * @var string
	 */
	public $role = 'super';

	use Permission;

	public function indexAction()
	{
		Assets::add('js/config.js');
		$formsManager = $this->model->getFormsManager();
		$formsManager->bind(CMSConfig::get());
		$formsManager->get('system')->getField('apiSecretKey')->setValue(Factory::getConfig()->get('secret.apiKey'));
		$this->tag->setTitle(Text::_('configuration'));
		$this->view->setVar('formsManager', $formsManager);
		$this->indexToolBar();
	}

	protected function indexToolBar($activeState = null, $excludes = [])
	{
		Toolbar::add('save', $this->uri->routeTo('save'), 'cloud-check');
	}

	public function saveAction()
	{
		if ($this->request->isPost())
		{
			$formsManager = $this->model->getFormsManager();

			if (!$formsManager->isValidRequest())
			{
				$this->flashSession->error(Text::_('cannot-save-item-msg'));

				return $this->uri::redirect($this->uri->routeTo('index'));
			}

			$validData = $formsManager->getData(true);

			if ($this->getEntity()->assign(['data' => $validData])->save())
			{
				$this->flashSession->success(Text::_('config-saved'));
				$this->uri->setVar('adminPrefix', $validData['adminPrefix']);
			}
			else
			{
				$this->flashSession->error(Text::_('cannot-save-item-msg'));
			}
		}

		$this->uri::redirect($this->uri->routeTo('index'));
	}

	public function getEntity()
	{
		$entity = $this->model->findFirst(
			[
				'conditions' => 'context = :context:',
				'bind'       => [
					'context' => 'cms.config',
				],
			]
		);

		if (!$entity)
		{
			$entity          = new ConfigModel;
			$entity->id      = 0;
			$entity->context = 'cms.config';
		}

		return $entity;
	}

	public function testMailAction()
	{
		if ($this->request->isPost() && $this->request->isAjax())
		{
			try
			{
				$data   = $this->request->getPost(null, null, []);
				$mailer = MailHelper::getInstance(
					[
						'host'     => $data['sysSmtpHost'] ?? CMSConfig::get('sysSmtpHost'),
						'port'     => $data['sysSmtpPort'] ?? CMSConfig::get('sysSmtpPort'),
						'username' => $data['sysSmtpUsername'] ?? CMSConfig::get('sysSmtpUsername'),
						'password' => $data['sysSmtpPassword'] ?? CMSConfig::get('sysSmtpPassword'),
						'security' => $data['sysSmtpSecurity'] ?? CMSConfig::get('sysSmtpSecurity'),
					]
				);

				$site     = $data['siteName'] ?? CMSConfig::get('siteName');
				$fromMail = $data['sysSendFromMail'] ?? CMSConfig::get('sysSendFromMail');
				$fromName = $data['sysSendFromName'] ?? CMSConfig::get('sysSendFromName');
				$mailer->setFrom($fromMail, $fromName);
				$mailer->addAddress($fromMail, $fromName);
				$mailer->Subject = Text::_('send-test-mail');
				$mailer->Body    = Text::_('test-mail-body', ['site' => $site]);

				if ($mailer->send())
				{
					$this->response->setJsonContent(
						[
							'status'  => 'success',
							'message' => Text::_('send-test-mail-success'),
						]
					);
				}
				else
				{
					$this->response->setJsonContent(
						[
							'status'  => 'danger',
							'message' => Text::_('send-test-mail-fail'),
						]
					);
				}
			}
			catch (PHPMailerException $e)
			{
				$this->response->setJsonContent(
					[
						'status'  => 'danger',
						'message' => $e->getMessage(),
					]
				);
			}

			return $this->response;
		}
	}
}
