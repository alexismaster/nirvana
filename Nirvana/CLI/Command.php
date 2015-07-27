<?php
/**
 * Базовый класс всех комманд генератора кода
 *
 *
 */

namespace Nirvana\CLI;


class Command
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
	 * @param $argv
	 */
	public function __construct($argv)
	{
		$this->argv = $argv;
	}


	/**
	 * Создаёт файл по шаблону
	 *
	 * @param $path - Путь с коздаваемому файлу
	 * @param $templateName - Имя шаблона
	 * @param $params - Параметры шаблона
	 */
	public function createFile($path, $templateName, $params)
	{
		if (!$this->twig) {
			$loader = new \Twig_Loader_Filesystem('Nirvana/CLI/templates');
			$this->twig = new \Twig_Environment($loader);
		}

		file_put_contents($path, $this->twig->render($templateName, $params));
	}


	/**
	 * Точка входа
	 */
	public function run()
	{
		//
	}
}