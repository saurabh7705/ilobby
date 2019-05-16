<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'iLobby',

	// preloading 'log' component
	'preload'=>array('log'),

	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	// application components
	'components'=>array(
		'db'=>array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=ilobby;',
			'emulatePrepare' => true,  	
			'username' => 'root',
			'password' => 'ilobby',
			'charset' => 'utf8',  	
			'tablePrefix' => '',
		),

		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'rules'=>array(
				"<controller:\w+>/<id:\d+>"=>'<controller>/view',
				"<controller:\w+>/<action:\w+>/<id:\d+>"=>'<controller>/<action>',
				"<controller:\w+>/<action:\w+>"=>'<controller>/<action>',
			)
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),
);