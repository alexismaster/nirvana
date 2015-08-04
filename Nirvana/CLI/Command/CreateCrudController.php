<?php
/**
 * Команда создания CRUD контроллера
 */

namespace Nirvana\CLI\Command;

use \Nirvana\CLI as CLI;


class CreateCrudController extends CLI\Command
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
        return '[green]create_crud_controller [cyan]Name1,Name2,NameN [white][--module NameM]';
    }

    public function getDescription()
    {
        return '';
    }

    public function getExample()
    {
        return '[cyan]create_crud_controller Product,Category,Basket --module Catalog';
    }
}