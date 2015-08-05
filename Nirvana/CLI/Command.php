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

    /**
     * @var bool
     */
    protected $isModule = false;


    /**
     * @var null
     */
    protected $moduleName = null;


    /**
     * Возвращает список имён классов которые необходимо создать
     *
     * @param string $type
     * @return array
     */
    public function getNames($type = 'controller')
    {
        $res   = array();
        $names = array_slice($this->argv, 2);

        foreach ($names as $i => $item) {
            // Указан модуль
            if ($item === '--module' or $this->isModule) {
                $this->isModule = true;
                if ($item !== '--module') {
                    $this->moduleName = $item;
                }
                unset($names[$i]);
                continue;
            }

            $names[$i] = str_replace(' ', '', $names[$i]);
            $names[$i] = str_replace($type, '', $names[$i]);
            $names[$i] = str_replace(ucfirst($type), '', $names[$i]);
            $names[$i] = explode(',', $names[$i]);
        }

        $names = array_filter($names, function ($item) {
            return ($item !== false);
        });

        foreach ($names as $item) {
            $res = array_merge($res, $item);
        }

        return $res;
    }
}