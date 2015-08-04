<?php
/**
 * Базовый класс всех комманд генератора кода
 *
 * @category   Nirvana
 * @package    CLI.Command
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\CLI;


abstract class Command implements ICommand
{
	/**
	 * Параметры командной строки
	 *
	 * @var
	 */
	public $argv;


	/**
	 * @var \Twig_Environment
	 */
	public $twig;


	/**
	 * Конструктор
	 *
	 * @param $argv - Аргументы командной оболочки
	 */
	public function __construct($argv)
	{
		$this->argv = $argv;
	}


	/**
	 * Создаёт файл по шаблону
	 *
	 * @param $path - Путь к шаблону
	 * @param $templateName - Имя шаблона
	 * @param $params - Параметры передаваемы в шаблон
	 */
	public function createFile($path, $templateName, $params)
	{
		if (!$this->twig) {
			$loader = new \Twig_Loader_Filesystem('Nirvana/CLI/templates');
			$this->twig = new \Twig_Environment($loader);
		}

		file_put_contents($path, $this->twig->render($templateName, $params));
	}
}