<?php
/**
 * Команда создания модуля
 */


namespace Nirvana\CLI\Command;

use \Nirvana\CLI as CLI;


class CreateModule extends CLI\Command
{
	public function run()
	{
		// Не указано имя модуля
		if (!isset($this->argv[2])) {
			exit("Not name module\r\n\r\n");
		}

		$name = ucfirst($this->argv[2]);

		// Модуль уже существует
		if (is_dir("Src/Module/{$this->argv[2]}Module")) {
			exit("Module \"{$name}\" already exists!\r\n\r\n");
		}

		$path = "Src/Module/{$name}Module";

		// Структура каталогов
		mkdir($path, 0777);
		mkdir($path . '/config', 0777);
		mkdir($path . '/Controller', 0777);
		mkdir($path . '/Entity', 0777);
		mkdir($path . '/views', 0777);

		// _routes_.php
		$this->createFile($path . '/config/_routes_.php', 'routes.twig', array('name' => $name));
	}

    public function getSyntax()
    {
        return '[green]create_module [cyan]Name1,Name2,NameN';
    }

    public function getDescription()
    {
        return '';
    }

    public function getExample()
    {
        return '[cyan]create_module Catalog,Blog,Forum';
    }
}

