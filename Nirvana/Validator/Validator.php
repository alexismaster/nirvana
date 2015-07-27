<?php
/**
 * Validator
 *
 * @category   Nirvana
 * @package    Validator
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */


namespace Nirvana\Validator;


class Validator
{
	/**
	 * Проверяет корректность email адреса
	 * @param $email
	 * @return mixed
	 */
	public static function isEmail($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * @param $str
	 * @param $min
	 * @return bool
	 */
	public static function minLength($str, $min)
	{
		return mb_strlen($str, 'utf8') >= $min;
	}

	/**
	 * @param $str
	 * @param $max
	 * @return bool
	 */
	public static function maxLength($str, $max)
	{
		return mb_strlen($str, 'utf8') <= $max;
	}
}