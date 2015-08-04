<?php
/**
 * Базовый класс констроллеров
 *
 * @category   Nirvana
 * @package    MVC
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\MVC;

use \Nirvana\ORM as ORM;


class Controller
{
	/**
	 * Имя модуля к которому относится контроллер
	 *
	 * @var
	 */
	private $moduleName;

	/**
	 * Имя экшена
	 *
	 * @var
	 */
	protected $actionName;

	/**
	 * @var
	 */
	protected $twig;

	/**
	 * Конструктор
	 *
	 * @param $actionName - Имя экшена
	 * @param $moduleName - Имя модуля
	 */
	public function __construct($actionName, $moduleName = false)
	{
		$this->actionName = $actionName;
		$this->moduleName = $moduleName;
	}

	/**
	 * Функция выполняющаяся перед рендерингом шаблона
	 *
	 * @return bool
	 */
	public function beforeRender()
	{
		return true;
	}

    /**
     * Рендерит шаблон
     *
     * @param $name - Имя шаблона
     * @param array $data - Параметры передаваемые в шаблон
     * @param null $path - Путь к папке с шаблонами
     * @return string
     * @throws \Exception
     */
	public function render($name, $data = array(), $path = null)
	{
		try {
			$loader = new \Twig_Loader_Filesystem();
            $loader->addPath('Src/views');              // Основная папка с шаблонами

            // Шаблоны модуля
            if ($this->moduleName) {
                $loader->prependPath('Src/Module/' . $this->moduleName . '/views');
            }

            // Пользовательская папка шаблонов
            if ($path) {
                $loader->prependPath($path);
            }

			$this->twig = new \Twig_Environment($loader);
			if (isset($_SESSION)) $this->twig->addGlobal('session', $_SESSION);

			// Десериализация
			$filter = new \Twig_SimpleFilter('unserialize', 'unserialize');
			$this->twig->addFilter($filter);

			$this->beforeRender();

			return $this->twig->render($name, $data);
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
	 * @return ORM\Repository
	 */
	public function getRepository($entityClassName)
	{
		return new ORM\Repository($entityClassName);
	}

	/**
	 * Возвращает адаптер к БД
	 *
	 * @return ORM\Adapter
	 */
	public function getAdapter()
	{
		$adapter = Application::getAdapter();
		return $adapter;
	}
}
