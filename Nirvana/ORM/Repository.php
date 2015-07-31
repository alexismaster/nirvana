<?php
/**
 * Repository
 *
 * Класс рапозитория создаётся под конкретный тип сущностей (конкретную таблицу)
 *
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\ORM;

use \Nirvana\MVC as MVC;


class Repository extends ORM
{

	/**
	 * Имя класса сущности (Product, User, Post и т.п.)
	 *
	 * @var string
	 */
	private $entityClassName;

	/**
	 * Конструктор
	 *
	 * @param $entityClassName - Имя класса сущности
	 */
	public function __construct($entityClassName)
	{
		$this->entityClassName = $entityClassName;
	}

	/**
	 * __call
	 *
	 * @param $name - Имя метода
	 * @param $arguments - Аргументы
	 * @return array
	 */
	public function __call($name, $arguments)
	{
		if (
			strpos($name, 'findBy')   === 0 ||
			strpos($name, 'deleteBy') === 0
		) {
			$by = strpos($name, 'By');

			$method = substr($name, 0, $by + 2);
			$column = substr($name, $by + 2);
			$column = $this->camelCase2underscore($column);

			return $this->$method(array(
				$column => array_shift($arguments)
			));
		}
	}

	/**
	 * Выборка
	 *
	 * @param $values - Массив вида "поле_таблицы" => "значение"
	 * @return array
	 */
	public function findBy($values)
	{
		$result = $this->makeQuery('SELECT *', $values);

		if ($result && $result->rowCount()) {
			return $this->mapResult($result);
		}
	}

	/**
	 * Конструирует запрос
	 *
	 * @param $type - Тип запроса (строка перед FROM)
	 * @param $values - Массив вида "колонка_таблицы" => "значение"
	 * @param string $glue - Логический оперетор объединения условий
	 * @return \PDOStatement
	 */
	private function makeQuery($type, $values, $glue = 'AND')
	{
		$params = array();
		$table  = strtolower($this->entityClassName);

		foreach ($values as $column => $value) {
			$values[$column] = $column . ' = :' . $column;
			$params[$column] = $value;
		}

		$where  = implode(' '.$glue.' ', $values);
		$result = $this->query($type . ' FROM `'.$table.'` WHERE '.$where.';', $params);

		return $result;
	}

	/**
	 * Удаление
	 *
	 * @param $values
	 * @return resource
	 */
	public function deleteBy($values)
	{
		return $this->makeQuery('DELETE', $values);
	}

	/**
	 * findBySql
	 *
	 * @param $sql
	 * @param array $params
	 * @return array|\PDOStatement
	 */
	public function findBySql($sql, $params = array())
	{
		$result = $this->query($sql, $params);

		if ($result && $result->rowCount()) {
			return $this->mapResult($result);
		}

		return ($result) ? array() : $result;
	}

	/**
	 * Маппит результаты запроса в массив сущностей
	 *
	 * @param $result - Результаты запроса к БД
	 * @return array
	 */
	public function mapResult($result)
	{
		$resArr = array();
		$classN = '\\Src\\Entity\\' . $this->entityClassName;

		while ($line = $result->fetch(\PDO::FETCH_ASSOC)) {
			$entity = new $classN();

			foreach ($line as $key => $value) {
				$setter = 'set' . ucfirst($key);
				$entity->$setter($value);
			}

			$resArr[] = $entity;
		}

		return $resArr;
	}
}
