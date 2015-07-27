<?php
/**
 * 
 */

// Корень проекта
chdir(dirname(dirname(__DIR__)));


// Автозагрузчик шаблонизатора Twig
require_once 'Nirvana/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();


// Автозагрузчик классов приложения
spl_autoload_register(function ($className) {
	require str_replace('\\', '/', $className) . '.php';
});


echo "\r\n";
echo " ----------------------------------\r\n";
echo " | --- Nirvana Code Generator --- |\r\n";
echo " ----------------------------------\r\n";
echo "\r\n";


// Имя скрипта содержащего класс команды
$script = explode('_', $argv[1]);
$script = array_map('ucfirst', $script);
$script = implode('', $script);


// Если скрипт команды существует
if (!isset($argv[1]) or !is_file('Nirvana/CLI/Command/' . $script . '.php')) {
	exit("Undefined Command\r\n");
}


$className = "Nirvana\\CLI\\Command\\$script";
$command = new $className($argv);
$command->run();


echo "\r\nComplete!\r\n\r\n";