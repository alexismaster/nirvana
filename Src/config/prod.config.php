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
	),


	// Настройки маршрутизатора
	'ROUTER' => array_merge(

		require('_routes_.php'),

		require(__DIR__ . '/../Module/SampleForumModule/config/_routes_.php')
	)
);