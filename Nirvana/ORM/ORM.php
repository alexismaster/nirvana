<?php
/**
 * Базовый класс для всех классов ORM
 *
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\ORM;

use Nirvana\MVC as MVC;


class ORM
{
	/**
	 * Преобразует camelCase строку в underscore
	 *
	 * Метод используется для преобразования вещей вида "setUserId" в "user_id". CamelCase отлично подходит
	 * для именования методов классов, но не очень красиво смотрится в названиях таблиц.
	 *
	 * @param $str - Исходная строка в camelCase нотации
	 * @return mixed
	 */
	public function camelCase2underscore($str)
	{
		$str = preg_replace('/([a-z])([A-Z])/', '$1_$2', $str);
		return strtolower($str);
	}

	/**
	 * Выполняет запрос к БД
	 *
	 * @param $sql
	 * @param array $params
	 * @return \PDOStatement
	 */
	public function query($sql, $params = array())
	{
		$adapter = MVC\Application::getAdapter();
		$result  = $adapter->query($sql, $params);
		return $result;
	}
}
