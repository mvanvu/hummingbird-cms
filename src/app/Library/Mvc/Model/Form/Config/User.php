<?php

return [
	[
		'name'          => 'allowUserRegistration',
		'type'          => 'Check',
		'label'         => 'allow-user-registration',
		'checkboxValue' => 'Y',
		'value'         => 'N',
		'filters'       => ['yesNo'],
	],
	[
		'name'    => 'newUserActivation',
		'type'    => 'Select',
		'label'   => 'new-user-activation',
		'value'   => 'E',
		'showOn'  => 'allowUserRegistration : is checked',
		'options' => [
			'E' => 'activate-by-email',
			'A' => 'auto-activate',
			'N' => 'activate-by-admin',
		],
		'rules'   => ['Options'],
	],
	[
		'name'          => 'mailToAdminWhenNewUser',
		'type'          => 'Check',
		'label'         => 'email-admin-when-new-user',
		'checkboxValue' => 'Y',
		'value'         => 'N',
		'showOn'        => 'allowUserRegistration : is checked',
		'filters'       => ['yesNo'],
	],
	[
		'name'   => 'adminEmail',
		'type'   => 'Email',
		'label'  => 'admin-email',
		'showOn' => 'allowUserRegistration : is checked',
		'rules'  => ['Email'],
	],
];