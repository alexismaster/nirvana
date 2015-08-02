<?php
/**
 * Nirvana Framework
 *
 * Настройки автозагрузки
 */


// Автозагрузчик шаблонизатора Twig
require_once 'Nirvana/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();


// Автозагрузчик классов фреймворка и приложения
spl_autoload_register(function ($class) {

	if (0 !== strpos($class, 'Nirvana') && 0 !== strpos($class, 'Src')) {
		return;
	}

	require str_replace('\\', '/', $class) . '.php';
});


// Автозагрузчик Composer
if (is_file($file = 'vendor/autoload.php')) {
	require $file;
}
