<?php

require_once 'framework/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();



// Автозагрузчик классов приложения
spl_autoload_register(function ($className) {
  $path = preg_replace('/\\\/', '/', $className);
  $path = preg_replace('/^Nirvana/', 'framework', $path);
  $path = preg_replace('/^SRC/', 'src', $path);
  $path = $path . '.php';
  include_once $path;
});