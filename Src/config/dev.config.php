<?php
/**
 * Конфиг
 */


return array(

	// Настройки подключения к СУБД
	'DB' => array(
		'MYSQL_HOST' => 'localhost',    // Хост
		'MYSQL_USER' => 'root',         // Имя пользователя БД
		'MYSQL_PASS' => '13271327',     // Пароль пользователя БД
		'MYSQL_BASE' => 'viliot',       // Имя базы данных
		'TABLE_PREF' => '',             // Префикс таблиц
	),


	// Настройки маршрутизатора
	'ROUTER' => array_merge(

		require('_routes_.php'),

		//require(__DIR__ . '/../Module/SampleForumModule/config/_routes_.php'),

		array(
			'orm-update' => array('url' => '/orm-update/', 'controller' => 'Default', 'action' => 'orm'),
		)
	)
);