<?php
/**
 * Nirvana Framework
 *
 * Настройки автозагрузки
 */


// Автозагрузчик шаблонизатора Twig
require_once 'framework/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();



// Автозагрузчик классов приложения
spl_autoload_register(function ($className) {
  $path = str_replace('\\', '/', $className);
  $path = str_replace('Nirvana/', 'framework/', $path);               // Классы фреймворка
  $path = str_replace('SRC/', 'src/', $path);                         // Классы приложения
  $path = preg_replace('/\\/([A-z]+Module)/', '/modules/$1', $path);  // Классы модулей приложения
  $path = $path . '.php';

  require_once $path;
});
