<?php

return [
	[
		'name' => 'id',
		'type' => 'Hidden',
	],
	[
		'name'     => 'role',
		'type'     => 'Select',
		'label'    => 'user-role',
		'required' => true,
		'options'  => [
			'R' => 'role-register',
			'A' => 'role-author',
			'M' => 'role-manager',
			'S' => 'role-super',
		],
		'class'    => 'not-chosen',
		'rules'    => ['Options'],
		'value'    => 'R',
	],
	[
		'name'     => 'active',
		'type'     => 'Select',
		'label'    => 'state',
		'required' => true,
		'options'  => [
			'Y' => 'active',
			'N' => 'banned',
		],
		'rules'    => ['Options'],
		'class'    => 'not-chosen',
		'value'    => 'Y',
	],
	[
		'name'     => 'name',
		'type'     => 'Text',
		'label'    => 'name',
		'required' => true,
		'filters'  => ['string', 'trim'],
	],
	[
		'name'     => 'email',
		'type'     => 'Email',
		'label'    => 'email',
		'required' => true,
		'filters'  => ['email'],
		'rules'    => ['Email'],
		'messages' => [
			'Email' => 'invalid-email-msg',
		],
	],
	[
		'name'         => 'username',
		'type'         => 'Text',
		'label'        => 'username',
		'required'     => true,
		'autocomplete' => 'off',
		'filters'      => ['username'],
	],
	[
		'name'         => 'password',
		'type'         => 'Password',
		'label'        => 'password',
		'autocomplete' => 'new-password',
		'confirmField' => 'confirmPassword',
		'rules'        => ['Confirm'],
		'messages'     => [
			'Confirm' => 'password-not-match',
		],
	],
	[
		'name'         => 'confirmPassword',
		'type'         => 'Password',
		'label'        => 'confirm-password',
		'autocomplete' => 'new-password',
	],
];