<?php

return [
	[
		'name'    => 'development',
		'type'    => 'Switcher',
		'label'   => 'development-mode',
		'checked' => true,
		'value'   => 'Y',
		'filters' => ['yesNo'],
	],
	[
		'name'    => 'gzip',
		'type'    => 'Switcher',
		'label'   => 'gzip-page-compression',
		'value'   => 'Y',
		'filters' => ['yesNo'],
	],
	[
		'name'     => 'adminPrefix',
		'type'     => 'Text',
		'label'    => 'admin-prefix-path',
		'required' => true,
		'value'    => 'admin',
		'filters'  => ['path'],
		'class'    => 'uk-input uk-width-medium',
	],
	[
		'name'        => 'apiSecretKey',
		'type'        => 'Text',
		'label'       => 'api-secret-key',
		'description' => 'api-registration-key-desc',
		'class'       => 'uk-input uk-width-medium uk-background-muted',
		'readonly'    => true,
	],
	[
		'name'    => 'reCaptchaSiteKey',
		'type'    => 'Text',
		'label'   => 're-captcha-site-key',
		'filters' => ['string', 'trim'],
		'class'   => 'uk-input uk-width-medium',
	],
	[
		'name'    => 'reCaptchaSecretKey',
		'type'    => 'Text',
		'label'   => 're-captcha-secret-key',
		'filters' => ['string', 'trim'],
		'class'   => 'uk-input uk-width-medium',
	],
	[
		'name'    => 'packagesChannel',
		'type'    => 'Text',
		'label'   => 'install-packages-channel',
		'filters' => ['string', 'trim'],
		'class'   => 'uk-input uk-width-medium',
		'hint'    => 'https://raw.githubusercontent.com/mvanvu/hummingbird-packages/master/packages.json',
	],
	[
		'name'    => 'sessionAdapter',
		'type'    => 'Select',
		'label'   => 'session-adapter',
		'rules'   => ['Options'],
		'class'   => 'uk-select uk-width-medium',
		'value'   => 'database',
		'options' => [
			'database' => 'Database',
			'stream'   => 'File',
		],
	],
	[
		'name'  => 'sysSendFromMail',
		'type'  => 'Email',
		'label' => 'send-from-mail',
		'rules' => ['Email'],
		'class' => 'uk-input uk-width-medium',
	],
	[
		'name'    => 'sysSendFromName',
		'type'    => 'Text',
		'label'   => 'send-from-name',
		'filters' => ['string', 'trim'],
		'class'   => 'uk-input uk-width-medium',
	],
	[
		'name'    => 'sysSmtpHost',
		'type'    => 'Text',
		'label'   => 'smtp-host',
		'value'   => 'smtp.gmail.com',
		'filters' => ['string', 'trim'],
		'class'   => 'uk-input uk-width-medium',
	],
	[
		'name'    => 'sysSmtpPort',
		'type'    => 'Text',
		'label'   => 'smtp-port',
		'value'   => '465',
		'filters' => ['int'],
		'class'   => 'uk-input uk-width-medium',
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
		'class'   => 'uk-select uk-width-medium',
	],
	[
		'name'    => 'sysSmtpUsername',
		'type'    => 'Text',
		'label'   => 'smtp-username',
		'filters' => ['string', 'trim'],
		'class'   => 'uk-input uk-width-medium',
	],
	[
		'name'  => 'sysSmtpPassword',
		'type'  => 'Password',
		'label' => 'smtp-password',
		'class' => 'uk-input uk-width-medium',
	],
];