<?php
/**
 * Команда создания сущности
 */

namespace Nirvana\CLI\Command;

use \Nirvana\CLI as CLI;


class CreateEntity extends CLI\Command
{
	/**
	 * Точка входа
	 */
	public function run()
	{
		//....
	}

    public function getSyntax()
    {
        return '[green]create_entity [cyan]Name1,Name2,NameN [white][--module NameM]';
    }

    public function getDescription()
    {
        return '';
    }

    public function getExample()
    {
        return '[cyan]create_entity Product,Category --module Catalog';
    }
}