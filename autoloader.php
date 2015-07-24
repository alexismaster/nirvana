<?php
/**
 * Nirvana Framework
 *
 * Настройки автозагрузки
 */


// Автозагрузчик шаблонизатора Twig
//require_once 'framework/vendor/Twig/lib/Twig/Autoloader.php';
require_once 'Nirvana/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();



// Автозагрузчик классов приложения
spl_autoload_register(function ($className) {
  require str_replace('\\', '/', $className) . '.php';
});
