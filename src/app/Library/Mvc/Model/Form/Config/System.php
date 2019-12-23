<?php

return [
	[
		'name'          => 'development',
		'type'          => 'Check',
		'label'         => 'development-mode',
		'checkboxValue' => 'Y',
		'value'         => 'Y',
		'filters'       => ['yesNo'],
	],
	[
		'name'     => 'adminPrefix',
		'type'     => 'Text',
		'label'    => 'admin-prefix-path',
		'required' => true,
		'value'    => 'admin',
		'filters'  => ['path'],
	],
	[
		'name'  => 'sysSendFromMail',
		'type'  => 'Email',
		'label' => 'send-from-mail',
		'rules' => ['Email'],
	],
	[
		'name'    => 'sysSendFromName',
		'type'    => 'Text',
		'label'   => 'send-from-name',
		'filters' => ['string', 'trim'],
	],
	[
		'name'    => 'sysSmtpHost',
		'type'    => 'Text',
		'label'   => 'smtp-host',
		'value'   => 'smtp.gmail.com',
		'filters' => ['string', 'trim'],
	],
	[
		'name'    => 'sysSmtpPort',
		'type'    => 'Text',
		'label'   => 'smtp-port',
		'value'   => '465',
		'filters' => ['int'],
	],
	[
		'name'    => 'sysSmtpSecurity',
		'type'    => 'Select',
		'label'   => 'smtp-security',
		'options' => [
			'none' => 'none',
			'ssl'  => 'ssl-tls',
			'tls'  => 'tls',
		],
	],
	[
		'name'    => 'sysSmtpUsername',
		'type'    => 'Text',
		'label'   => 'smtp-username',
		'filters' => ['string', 'trim'],
	],
	[
		'name'  => 'sysSmtpPassword',
		'type'  => 'Password',
		'label' => 'smtp-password',
	],
];