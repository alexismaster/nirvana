<?php
/**
 * Конфиг
 */


return array(

	// Настройки подключения к СУБД
	'DB' => array(
		'MYSQL_HOST' => 'localhost',
		'MYSQL_USER' => 'root',
		'MYSQL_PASS' => '13271327',
		'MYSQL_BASE' => 'viliot',
		'TABLE_PREF' => '',             // Префикс таблиц
	),


	// Настройки маршрутизатора
	'ROUTER' => array_merge(

		require('_routes_.php'),

		require(__DIR__ . '/../modules/SampleForumModule/config/_routes_.php'),

		array(
			'orm-update' => array('url' => '/orm-update/', 'controller' => 'Default', 'action' => 'orm'),
		)
	)
);