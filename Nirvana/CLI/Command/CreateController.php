<?php
/**
 * Скрипт генерации контроллера
 */


namespace Nirvana\CLI\Command;

use \Nirvana\CLI as CLI;


class CreateController extends CLI\Command
{
	/**
	 * @var bool
	 */
	private $isCatalog = false;


	/**
	 * @var null
	 */
	private $catalogName = null;


	/**
	 * Точка входа
	 */
	public function run()
	{
		//global $twig;

		$res = $this->getControllers();

		if (!count($res)) {
			exit("Undefined controller name.\r\n");
		}

		foreach ($res as $name) {

			if (!preg_match('/[A-z]{3}/', $name)) {
				echo "Wrong controller name \"{$name}\".\r\n";
				continue;
			}

			// Контроллер модуля
			if ($this->isCatalog) {

				if (!is_dir("Src/Module/{$this->catalogName}Module")) {
					exit("Module {$this->catalogName} not found.");
				}

				$path = "Src/Module/{$this->catalogName}Module/Controller/{$name}Controller.php";

				if (is_file($path)) {
					echo "Controller \"{$name}Controller\" already exists!\r\n";
					continue;
				}

				$this->createFile($path, 'module_controller.twig', array('name' => $name, 'module' => $this->catalogName));
			} // Обычный контроллер
			else {
				$path = "Src/Controller/{$name}Controller.php";

				if (is_file($path)) {
					echo "Controller \"{$name}Controller\" already exists!\r\n";
					continue;
				}

				$this->createFile($path, 'controller.twig', array('name' => $name));
			}
		}
	}


	/**
	 * Возвращает список имён контроллеров которые необходимо создать
	 *
	 * @return array
	 */
	public function getControllers()
	{
		$res = array();

		$controllers = array_slice($this->argv, 2);

		$controllers = array_map(function ($item) {
			// Указан модуль
			if ($item === 'module' or $this->isCatalog) {
				$this->isCatalog = true;
				if ($item !== 'module') $this->catalogName = $item;
				return false;
			}

			$item = str_replace(' ', '', $item);
			$item = str_replace('controller', '', $item);
			$item = str_replace('Controller', '', $item);
			return explode(',', $item);
		}, $controllers);

		$controllers = array_filter($controllers, function ($item) {
			return ($item !== false);
		});

		foreach ($controllers as $item) {
			$res = array_merge($res, $item);
		}

		return $res;
	}
}



