<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Hummingbird\Lib\Factory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mail extends PHPMailer
{
	public static function getInstance(array $smtpData = [])
	{
		$mailer   = new Mail(true);
		$smtpData = array_merge(
			[
				'host'     => Config::get('sysSmtpHost'),
				'port'     => Config::get('sysSmtpPort'),
				'security' => Config::get('sysSmtpSecurity'),
				'username' => Config::get('sysSmtpUsername'),
				'password' => Config::get('sysSmtpPassword'),
				'fromMail' => Config::get('sysSendFromMail'),
				'fromName' => Config::get('sysSendFromName'),
			],
			$smtpData
		);
		$fromMail = Config::get('sysSendFromMail');
		$fromName = Config::get('sysSendFromName');
		//$mailer->SMTPDebug = 2;
		$mailer->isSMTP();
		$mailer->Host       = $smtpData['host'];
		$mailer->Port       = $smtpData['port'];
		$mailer->SMTPSecure = $smtpData['security'];
		$mailer->Username   = $smtpData['username'];
		$mailer->Password   = $smtpData['password'];
		$mailer->SMTPAuth   = true;
		$mailer->setFrom($fromMail, $fromName);

		return $mailer;
	}

	public static function sendMail($recipient, $subject, $body, $isHtml = false, $cc = null, $bcc = null, $attachment = null, $replyTo = null, $replyToName = null)
	{
		$recipients = is_array($recipient) ? $recipient : [$recipient];

		try
		{
			$mailer = self::getInstance();

			foreach ($recipients as $recipient)
			{
				$mailer->addAddress($recipient);
			}

			$mailer->Subject = $subject;
			$mailer->Body    = $body;

			if ($isHtml)
			{
				$mailer->isHTML(true);
			}

			if ($cc)
			{
				$mailer->addCC($cc);
			}

			if ($bcc)
			{
				$mailer->addBCC($bcc);
			}

			if ($attachment)
			{
				$attachments = is_array($attachment) ? $attachment : [$attachment];

				foreach ($attachments as $attachment)
				{
					$mailer->addAttachment($attachment);
				}
			}

			if ($replyTo)
			{
				$mailer->addReplyTo($replyTo, $replyToName);
			}

			return $mailer->send();
		}
		catch (PHPMailerException $e)
		{
			Factory::getService('flashSession')->warning(Text::_('failed-send-mail', ['mail' => join(', ', $recipients)]));

			return false;
		}
	}
}
