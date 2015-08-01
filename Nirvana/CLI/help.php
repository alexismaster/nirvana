<?php
/**
 * Справка по генератору кода
 */


use Nirvana\CLI as CLI;


/*
 * Список команд
 */

CLI\Console::println('[green_bold] Command List:');
CLI\Console::println();
CLI\Console::println(' - [green]create_controller [cyan]Name1,Name2,NameN [white][module=NameM]');
CLI\Console::println(' - [green]create_crud_controller [cyan]Name1,Name2,NameN [white][module=NameM]');
CLI\Console::println(' - [green]create_module [cyan]Name1,Name2,NameN');
CLI\Console::println(' - [green]create_entity [cyan]Name1,Name2,NameN');


/*
 * Пример использования команд
 */

CLI\Console::println();
CLI\Console::println('[green_bold] Sample Usage:');
CLI\Console::println();

if (PHP_OS === 'Linux') {
    $cli = '[white] developer@debian:~/www/nirvana_project';
}
else {
    $cli = '[white] C:\WebServer\htdocs\nirvana_project>';
}

CLI\Console::println($cli . '[cyan] nirvana create_module Catalog');
CLI\Console::println($cli . '[cyan] nirvana create_controller Product,Category,Basket module=Catalog');
CLI\Console::println($cli . '[cyan] nirvana create_entity Product,Category,Basket module=Catalog');



CLI\Console::println();
CLI\Console::println();


