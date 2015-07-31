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
		foreach ($this->_routes as $route) if ($route->test()) {
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
}
