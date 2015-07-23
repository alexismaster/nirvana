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
  $path = preg_replace('/\\\/', '/', $className);
  $path = preg_replace('/^Nirvana/', 'framework', $path);   // Классы фреймворка
  $path = preg_replace('/^SRC/', 'src', $path);             // Классы приложения
  $path = $path . '.php';

  if (is_file($path)) require_once $path;
});