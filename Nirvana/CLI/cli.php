<?php

// Корень проекта
chdir(dirname(dirname(__DIR__)));


// Автозагрузчик шаблонизатора Twig
require_once 'Nirvana/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new \Twig_Loader_Filesystem('Nirvana/CLI/templates');
$twig = new \Twig_Environment($loader);


echo "\r\n";
echo " ----------------------------------\r\n";
echo " | --- Nirvana Code Generator --- |\r\n";
echo " ----------------------------------\r\n";
echo "\r\n";


// Если скрипт команды существует
if (!isset($argv[1]) or !is_file('Nirvana/CLI/commands/' . $argv[1] . '.php')) {
  exit("Undefined Command\r\n");
}

// Выполнение команды
require 'commands/' . $argv[1] . '.php';


echo "\r\nComplete!\r\n\r\n";