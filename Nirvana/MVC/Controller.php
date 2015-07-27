<?php
/**
 * Базовый класс констроллеров
 *
 * @category   Nirvana
 * @package    MVC
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\MVC;


class Controller
{
	private $moduleName;

	public function __construct($moduleName)
	{
		$this->moduleName = $moduleName;

	}


	/**
	 * Рендерит шаблон
	 *
	 * @param $name - Имя шаблона
	 * @param array $data - Параметры передаваемые в шаблон
	 * @param null $path - Путь к папке с шаблонами
	 * @throws \Exception
	 */
	public function render($name, $data = array(), $path = null)
	{
		try {
			if (is_null($path)) $path = 'Src/views';

			if ($this->moduleName) {
				$path = 'Src/Module/' . $this->moduleName . '/views';
			}

			// Проверка существования шаблона
			if (!is_file($path . '/' . $name)) {
				throw new \Exception('Template "' . $name . '" not found');
			}

			$loader = new \Twig_Loader_Filesystem($path);
			$twig = new \Twig_Environment($loader);
			if (isset($_SESSION)) $twig->addGlobal('session', $_SESSION);

			// Десериализация
			$filter = new \Twig_SimpleFilter('unserialize', 'unserialize');
			$twig->addFilter($filter);

			echo $twig->render($name, $data);
		} catch (\Exception $e) {
			throw new \Exception('Template "' . $name . '" not exists in "' . $path . '"');
		}
	}

	/**
	 * Проверяет, соответствует ли тип метода запроса переданному в параметре
	 *
	 * @param $type - тип медода запроса (POST,GET,...)
	 * @return bool
	 */
	public function isRequestMethod($type)
	{
		if ($_SERVER['REQUEST_METHOD'] === $type) {
			return true;
		}
		return false;
	}

	/**
	 * Редирект
	 *
	 * @param $url - Страница перенаправления
	 */
	public function redirect($url)
	{
		header('Location: ' . $url);
	}

	/**
	 * Возвращает экземпляр класса Repository для конкретного типа сущности
	 *
	 * @param $entityClassName - Имя класса сущности
	 * @return \Nirvana\ORM\Repository
	 */
	public function getRepository($entityClassName)
	{
		return new \Nirvana\ORM\Repository($entityClassName);
	}
}
