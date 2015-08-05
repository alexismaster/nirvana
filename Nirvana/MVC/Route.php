<?php
/**
 * Route (Маршрут)
 *
 * @category   Nirvana
 * @package    MVC
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\MVC;


class Route
{
	/**
	 * Имя контроллера
	 *
	 * @var string
	 */
	protected $controller;

	/**
	 * Имя экшена
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * @var
	 */
	protected $module;

	/**
	 * Шаблон URL
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $regExp;

	/**
	 * Конструктор
	 *
	 * @param $url - Шаблон URL
	 * @param array $options - Параметры (контроллер, экшен)
	 */
	public function __construct($url, array $options)
	{
		$this->url = $url;
		$this->controller = (isset($options['controller'])) ? $options['controller'] : 'Default';
		$this->action = (isset($options['action'])) ? $options['action'] : 'Index';

		if (isset($options['module'])) {
			$this->module = $options['module'];
		}
	}

	/**
	 * Проверяет соответствие шаблона URL
	 *
	 * @return bool
	 */
	public function test()
	{
		$tpl = preg_replace('/:[a-z]+/i', '([a-z0-9_-]+)', $this->url);
		$tpl = '/^' . preg_replace('/\//', '\/', $tpl) . '$/i';
		$this->regExp = $tpl;

		if (preg_match($tpl, $_SERVER['REQUEST_URI'])) {
			return true;
		}

		return false;
	}

	/**
	 * Возвращает список параметров из URL
	 *
	 * @return array
	 */
	public function getParams()
	{
		preg_match($this->regExp, $_SERVER['REQUEST_URI'], $values);    // Значения
		preg_match_all('/:([a-z0-9_-]+)/i', $this->url, $keys);              // Ключи
		$params = array_combine($keys[1], array_slice($values, 1));
		if (!is_array($params)) $params = array();
		return $params;
	}

	/**
	 * Возвращает имя класса контроллера
	 *
	 * @return string
	 */
	public function getControllerName()
	{
		if ($this->module) {
			return $this->module . 'Module\\Controller\\' . $this->controller . 'Controller';
		}
		return 'Controller\\' . $this->controller . 'Controller';
	}

	/**
	 * Возвращает имя экшена (метода контроллера)
	 *
	 * @return string
	 */
	public function getActionName()
	{
		return $this->action . 'Action';
	}

	public function getModuleName()
	{
		if ($this->module) {
			return $this->module . 'Module';
		}

		return false;
	}

}
