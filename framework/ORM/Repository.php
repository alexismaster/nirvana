<?php
/**
 * Repository
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
	 * Имя класса сущности
	 *
	 * @var string
	 */
	private $entityClassName;

	/**
	 * Конструктор
	 *
	 * @param $entityClassName
	 */
	public function __construct($entityClassName)
	{
		$this->entityClassName = $entityClassName;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return array
	 */
	public function __call($name, $arguments)
	{
		// findBy[Column]
		if (preg_match('/findBy[a-z]+/i', $name)) {
			$column = $this->camelCase2underscore(str_replace('findBy', '', $name));
			return $this->findBy(array($column => $arguments[0]));
		}

		// deleteBy[Column]
		if (preg_match('/deleteBy[a-z]+/i', $name)) {
			$column = $this->camelCase2underscore(str_replace('deleteBy', '', $name));
			return $this->deleteBy(array($column => $arguments[0]));
		}
	}

	/**
	 * @param $values
	 * @return array
	 */
	public function findBy($values)
	{
		$table = strtolower($this->entityClassName);

		foreach ($values as $column => $value) {
			$values[$column] = $column . "='$value'";
		}

		$where = implode(' AND ', $values);
		$adapter = MVC\Application::getAdapter();
		$result = $adapter->query("SELECT * FROM `$table` WHERE $where;");

		if ($result && mysql_num_rows($result)) {
			return $this->mapResult($result);
		}
	}

	/**
	 * @param $values
	 * @return resource
	 */
	public function deleteBy($values)
	{
		$table = strtolower($this->entityClassName);
		$where = $this->getWhere($values);
		$query = "DELETE FROM `{$table}` WHERE {$where};";

		$result = MVC\Application::getAdapter()->query($query);
		return $result;
	}

	/**
	 * @param $values
	 * @return string
	 */
	public function getWhere($values)
	{
		foreach ($values as $column => $value) {
			$values[$column] = $column . "='$value'";
		}

		return implode(' AND ', $values);
	}

	/**
	 * @param $sql
	 * @return array
	 */
	public function findBySql($sql)
	{
		$adapter = MVC\Application::getAdapter();
		$result = $adapter->query($sql);

		if ($result && mysql_num_rows($result)) {
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
		$classN = "\\SRC\\Entity\\$this->entityClassName";

		while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$entity = new $classN();

			foreach ($line as $key => $value) {
				$setter = "set" . ucfirst($key);
				$entity->$setter($value);
			}

			$resArr[] = $entity;
		}

		return $resArr;
	}
}
