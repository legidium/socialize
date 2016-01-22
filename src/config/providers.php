<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| List of Providers
	|--------------------------------------------------------------------------
	|
	| Providers, which can be used by the application with auth data
	|
	*/

	'services' => [
		'facebook' => [
			'enabled' => true,
			'id' => '',
			'secret' => '',
			'scopes' => ['email'],
			'version' => '2.1'
		],
		'google' => [
			'enabled' => true,
			'id' => '',
			'secret' => '',
			'scopes' => ['profile', 'email']
		],
		'vkontakte' => [
			'enabled' => true,
			'id' => '',
			'secret' => '',
			'additional' => [
				'v' => '5.25'
			]
		]
	]

);
