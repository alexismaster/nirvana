<?php
/**
 * Nirvana Framework
 *
 * @category   Nirvana
 * @package    MVC
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */


namespace Nirvana\MVC;

use \Nirvana\MVC as MVC;
use \Nirvana\ORM as ORM;


class Application
{

	/**
	 * Экземпляр класса
	 *
	 * @var Application
	 */
	protected static $_instance;

	/**
	 * Флаг режима отладки
	 *
	 * @var
	 */
	private $debug;

	/**
	 * Настройки
	 *
	 * @var
	 */
	private $config;

	/**
	 * Конструктор
	 *
	 * @param $config - Настройки
	 */
	private function __construct($config)
	{
		$this->config = $config;
	}

	/**
	 * Закрытие доступа к __clone
	 */
	private function __clone()
	{
	}

	/**
	 * Возвращает экземпляр класса
	 *
	 * @param $config array - Настройки
	 * @return Application - Экземпляр класса
	 */
	public static function init($config)
	{
		if (null === self::$_instance) {
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}


	/**
	 * Вызов экшена
	 *
	 * @param $controllerName string - Имя контроллера
	 * @param $actionName string - Имя экшена
	 * @param $params - Параметры
	 * @return mixed
	 * @throws \Exception
	 */
	public function callAction($controllerName, $actionName, $moduleName, $params)
	{


		if ($moduleName) {
			$controllerName = "\\Src\\Module\\$controllerName";
		}
		else {
			$controllerName = "\\Src\\$controllerName";
		}

		$module = new $controllerName($moduleName);

		// Запуск экшена
		if (method_exists($module, $actionName)) {
			$par = $this->getParameters($controllerName, $actionName); // Список параметров экшена

			// Проверка допустимости параметра
			foreach ($params as $name => $value) if (!in_array($name, $par)) {
				throw new \Exception('Не известный параметр "' . $name . '" экшена "' . $actionName . '"');
			}

			// Проверка возможности параметра принимать NULL
			foreach ($par as $id => $name) {
				// Нет значения для параметра либо оно равно null
				if (!array_key_exists($name, $params) or is_null($params[$name])) {
					$param = new ReflectionParameter(array($controllerName, $actionName), $name);
					// Параметр не является опциональным
					if (!$param->isOptional()) {
						throw new \Exception('Параметр "' . $name . '" является обязательным. Экшен: ' . $actionName);
					}
				}
			}

			$data = call_user_func_array(array($module, $actionName), $params);
		} else {
			throw new \Exception('Экшен "' . $actionName . '" не найден. Контроллер  "' . $controllerName . '"');
		}

		return $data;
	}

	/**
	 * Возвращает список параметров экшена
	 *
	 * @param $className - Имя класса контроллера
	 * @param $actionName - Имя экшена
	 * @return array
	 */
	private function getParameters($className, $actionName)
	{
		$ref = new \ReflectionClass($className);
		$par = $ref->getMethod($actionName)->getParameters();

		$par = array_map(function ($param) {
			return $param->name;
		}, $par);

		return $par;
	}

	/**
	 * Возвращает настройки подключения к БД
	 *
	 * @return array
	 */
	private static function getMySQLConfigs()
	{
		if (self::$_instance) {
			return self::$_instance->config['DB'];
		}

		return array();
	}

	/**
	 * Возвращает адаптер БД
	 *
	 * @return ORM\Adapter
	 */
	public static function getAdapter()
	{
		$configs = self::getMySQLConfigs();
		$adapter = ORM\Adapter::getInstance($configs);
		return $adapter;
	}

	private $router;

	/**
	 * Запуск приложения
	 *
	 * @param $debug - Режим отладки
	 * @throws \Exception
	 */
	public function run($debug)
	{
		$this->debug = $debug;

		try {
			// В режиме отладки показываем все ошибки
			if ($this->debug) { //И...
				error_reporting(E_ALL);
				ini_set('display_errors', 1);
			}

			session_start();
			session_name('nirvana');

			// Перехват исключений
			set_error_handler(function ($severity, $message, $filename, $lineNo) {
				echo $message;
				return;
			});

			$route = $this->initRouter()->getRoute();  // Маршрут соответствующий текущему URL

			if (!$route) {
				throw new \Exception('Страница не найдена', 404);
			}

			// Запуск экшена
			$this->callAction($route->getControllerName(), $route->getActionName(), $route->getModuleName(), $route->getParams());
		} catch (\Exception $error) {
			// Страница 404-й ошибки
			$this->callAction('Controller\\DefaultController', 'NotFoundAction', false, array('error' => $error));
		}
	}

	/**
	 * Инициализация роутера
	 *
	 * @return Router
	 */
	public function initRouter()
	{
		$this->router = new MVC\Router();

		// Маршруты основного приложения и модулей
		foreach ($this->config['ROUTER'] as $name => $route) {
			$this->router->addRoute($name,  new MVC\Route($route['url'], $route));
		}

		return $this->router;
	}
}