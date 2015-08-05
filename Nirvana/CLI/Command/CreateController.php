<?php
/**
 * Скрипт генерации контроллера
 */


namespace Nirvana\CLI\Command;

use \Nirvana\CLI as CLI;


class CreateController extends CLI\Command
{

	/**
	 * Точка входа
	 */
	public function run()
	{
		$res = $this->getNames();

		if (!count($res)) {
			exit("Undefined controller name.\r\n");
		}

		foreach ($res as $name) {

			if (!preg_match('/[A-z]{3}/', $name)) {
				echo "Wrong controller name \"{$name}\".\r\n";
				continue;
			}

			// Контроллер модуля
			if ($this->isModule) {

				if (!is_dir("Src/Module/{$this->moduleName}Module")) {
					exit("Module {$this->moduleName} not found.");
				}

				$path = "Src/Module/{$this->moduleName}Module/Controller/{$name}Controller.php";

				if (is_file($path)) {
					echo "Controller \"{$name}Controller\" already exists!\r\n";
					continue;
				}

				$this->createFile($path, 'module_controller.twig', array('name' => $name, 'module' => $this->moduleName));

                $views = "Src/Module/{$this->moduleName}Module/views/" . strtolower($name);
                if (!is_dir($views)) mkdir($views, 0777);

			} // Обычный контроллер
			else {
				$path = "Src/Controller/{$name}Controller.php";

				if (is_file($path)) {
					echo "Controller \"{$name}Controller\" already exists!\r\n";
					continue;
				}

				$this->createFile($path, 'controller.twig', array('name' => $name));

                $views = "Src/views/" . strtolower($name);
                if (!is_dir($views)) mkdir($views, 0777);
			}
		}
	}

    /**
     * Синтаксис команды
     *
     * @return string
     */
    public function getSyntax()
    {
        return '[green]create_controller [cyan]Name1,Name2,NameN [white][--module NameM]';
    }

    /**
     * Описание команды
     *
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Пример использования
     *
     * @return string
     */
    public function getExample()
    {
        return '[cyan]create_controller Product,Category,Basket --module Catalog';
    }
}



