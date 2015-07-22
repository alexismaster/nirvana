<?php
/**
 * Адаптер (Singleton)
 *
 * @category   Framework
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\ORM;


class Adapter
{

	/**
	 * Экземпляр класса
	 *
	 * @var Adapter
	 */
	protected static $_instance;

	/**
	 * Ресурс подключения к БД
	 *
	 * @var resource
	 */
	private $link;

	/**
	 * Конструктор
	 *
	 * @param array $config - Настройки подключения к БД
	 * @throws \Exception
	 */
	private function __construct(array $config)
	{
		$this->link = mysql_connect($config['MYSQL_HOST'], $config['MYSQL_USER'], $config['MYSQL_PASS']);

		if (!$this->link) {
			throw new \Exception('Ошибка подключения к БД');
		}

		mysql_set_charset('utf8', $this->link);

		$db_selected = mysql_select_db($config['MYSQL_BASE'], $this->link);
		if (!$db_selected) throw new \Exception('Ошибка выбора БД');
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
	 * @param $config - Настройки подключения к БД
	 * @return Adapter - Экземпляр класса
	 */
	public static function getInstance($config)
	{
		if (null === self::$_instance) {
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}

	/**
	 * Выполняет запрос к БД
	 *
	 * @param $sql - Запрос
	 * @return resource - Результат
	 */
	public function query($sql)
	{
		$res = mysql_query($sql, $this->link);
		return $res;
	}

	/**
	 * queryOne
	 *
	 * @param $sql
	 * @return array
	 */
	public function fetchOne($sql)
	{
		$result = $this->query($sql);

		if ($result) {
			return mysql_fetch_array($result, MYSQL_ASSOC);
		}
	}
}
