<?php
/**
 * Router
 *
 * @category   Framework
 * @package    App
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\MVC;


class Router
{
	/**
	 * Маршруты
	 *
	 * @var array
	 */
	private $_routes = array();

	/**
	 * Добавление маршрута
	 *
	 * @param $name - Имя маршрута
	 * @param $route
	 */
	public function addRoute($name, Route $route)
	{
		$this->_routes[$name] = $route;
	}

	/**
	 * Возврашает маршрут соответствующий текущему URL страницы
	 *
	 * @return Route
	 */
	public function getRoute()
	{
		foreach ($this->_routes as $route) if ($route->test()) {
			return $route;
		}
	}
}
