<?php
/**
 * Базовый класс для всех классов ORM
 *
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\ORM;


class ORM
{
	/**
	 * @param $str
	 * @return mixed
	 */
	public function camelCase2underscore($str)
	{
		$str = preg_replace('/([a-z])([A-Z])/', '$1_$2', $str);
		return strtolower($str);
	}
}


//$orm = new ORM();
//var_dump($orm->camelCase2underscore('UserId'));
