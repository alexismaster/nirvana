<?php
/**
 * Настройки подключения к MySQL
 */


return array(

	// Production config
		'prod' => array(
				'MYSQL_HOST' => 'localhost',
				'MYSQL_USER' => 'root',
				'MYSQL_PASS' => '13271327',
				'MYSQL_BASE' => 'viliot',
		),

	// Develop config
		'dev' => array(
				'MYSQL_HOST' => 'localhost',
				'MYSQL_USER' => 'root',
				'MYSQL_PASS' => '',
				'MYSQL_BASE' => 'viliot',
		)
);