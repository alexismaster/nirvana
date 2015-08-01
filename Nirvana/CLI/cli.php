<?php
/**
 * Генератор кода
 */

use Nirvana\CLI as CLI;


// Корень проекта
chdir(dirname(dirname(__DIR__)));


// Автозагрузчик шаблонизатора Twig
require_once 'Nirvana/vendor/Twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();


// Автозагрузчик классов приложения
spl_autoload_register(function ($className) {
	require str_replace('\\', '/', $className) . '.php';
});


CLI\Console::println();
CLI\Console::println('[green] ----------------------------------');
CLI\Console::println('[green] | --- [red]Nirvana Code Generator[green] --- |');
CLI\Console::println('[green] ----------------------------------');
CLI\Console::println();


// Нет аргументов - выводим справку
if (!isset($argv[1]) || $argv[1] === 'help') {
	include 'Nirvana/CLI/help.php';
	exit();
}


// Имя скрипта содержащего класс команды
$script = explode('_', $argv[1]);
$script = array_map('ucfirst', $script);
$script = implode('', $script);


// Если скрипт команды существует
if (!is_file('Nirvana/CLI/Command/' . $script . '.php')) {
	CLI\Console::println('[red] Undefined Command');
	CLI\Console::println();
	exit();
}


// Выполнение команды
$className = "Nirvana\\CLI\\Command\\$script";
$command = new $className($argv);
$command->run();


CLI\Console::println();
CLI\Console::println('[green] Complete!');
CLI\Console::println();
