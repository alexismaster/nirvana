<?php
/**
 * Application
 */

namespace Nirvana\MVC;


class Application
{

	/**
	 * Экземпляр класса
	 *
	 * @var Application
	 */
	protected static $_instance;

	/**
	 * @var
	 */
	private $env;

	/**
	 * Конструктор
	 *
	 * @param $env - Окружение
	 */
	private function __construct($env)
	{
		$this->env = $env;
	}

	/**
	 * disable __clone
	 */
	private function __clone()
	{
	}

	/**
	 * Возвращает экземпляр класса
	 *
	 * @param $env - Тип окрудения (dev,prod,...)
	 * @return Application - Экземпляр класса
	 */
	public static function init($env)
	{
		if (null === self::$_instance) {
			self::$_instance = new self($env);
		}
		return self::$_instance;
	}

	/**
	 * @param $controllerName - Имя контроллера
	 * @param $actionName - Имя экшена
	 * @param $params - Параметры
	 * @return mixed
	 * @throws \Exception
	 */
	public function callAction($controllerName, $actionName, $params)
	{
		$controllerName = "\\SRC\\$controllerName";
		$module = new $controllerName();

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
	 * @return mixed
	 */
	private static function getMySQLConfigs()
	{
		$configs = include_once 'src/config/db.php';
		if (self::$_instance) return $configs[self::$_instance->env];
	}

	/**
	 * Возвращает адаптер БД
	 *
	 * @return ORM\Adapter
	 */
	public static function getAdapter()
	{
		$configs = self::getMySQLConfigs();
		$adapter = \Nirvana\ORM\Adapter::getInstance($configs);
		return $adapter;
	}

	/**
	 * @param $env
	 * @throws \Exception
	 */
	public function run($env)
	{
		$this->env = $env;

		try {
			$router = new \Nirvana\MVC\Router();    // Маршрутизатор
			include_once 'src/config/routes.php';   // Настойки маршрутизатора (список роутов)
			$route = $router->getRoute();           // Маршрут соответствующий текущему URL

			if (!$route) {
				throw new Exception('Страница не найдена', 404);
			}

			// Запуск экшена
			$this->callAction($route->getControllerName(), $route->getActionName(), $route->getParams());
		} catch (Exception $error) {
			// Страница 404-й ошибки
			$this->callAction('SRC\\Controller\\DefaultController', 'NotFoundAction', array('error' => $error));
		}
	}
}