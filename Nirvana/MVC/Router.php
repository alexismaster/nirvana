<?php
/**
 * Router
 *
 * @category   Nirvana
 * @package    MVC
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\MVC;


class Router implements \Countable
{
	/**
	 * Маршруты
	 *
	 * @var array
	 */
	private $_routes = array();
	private $uri;

	public function __construct($uri)
	{
		$this->uri = (isset($uri)) ? $uri : $_SERVER['REQUEST_URI'];
	}

	/**
	 * Добавление маршрута
	 *
	 * @param $name - Имя маршрута
	 * @param Route $route
	 * @throws \Exception
	 */
	public function addRoute($name, Route $route)
	{
		if (isset($this->_routes[$name])) {
			throw new \Exception("Route \"{$name}\" already exists");
		}

		$this->_routes[$name] = $route;
	}

	/**
	 * Возврашает маршрут соответствующий текущему URL страницы
	 *
	 * @return Route
	 */
	public function getRoute()
	{
		foreach ($this->_routes as $route) if ($route->test($this->uri)) {
			return $route;
		}
	}

	/**
	 * Реализация интерфейса Countable
	 */
	public function count()
	{
		return count($this->_routes);
	}

	/**
	 * Пытается интерпретировать URL как [module]/controller/action
	 */
	public function getAutoRoute()
	{
		$url = $this->uri;
		$url = preg_replace("/^\\//", "", $url);
		$url = preg_replace("/\\/$/", "", $url);
		$url = explode("/", $url);
		$route = null;
		if (count($url) === 2) {
			$route = new Route($_SERVER['REQUEST_URI'], array('controller' => ucfirst($url[0]), 'action' => ucfirst($url[1])));
			$route->test($this->uri);
		} else if (count($url) === 3) {
			$route = new Route($_SERVER['REQUEST_URI'], array('module' => ucfirst($url[0]), 'controller' => ucfirst($url[1]), 'action' => ucfirst($url[2])));
			$route->test($this->uri);
		}
		return $route;
	}
}
