<?php

namespace App\Queue;

use App\Helper\Mail;

class SendMail extends QueueAbstract
{
	public function handle(): bool
	{
		$recipient   = $this->data['recipient'];
		$subject     = $this->data['subject'];
		$body        = $this->data['body'];
		$isHtml      = $this->data['isHtml'] ?? false;
		$cc          = $this->data['cc'] ?? null;
		$bcc         = $this->data['bcc'] ?? null;
		$attachment  = $this->data['attachment'] ?? null;
		$replyTo     = $this->data['replyTo'] ?? null;
		$replyToName = $this->data['replyToName'] ?? null;

		return Mail::sendMail($recipient, $subject, $body, $isHtml, $cc, $bcc, $attachment, $replyTo, $replyToName);
	}
}