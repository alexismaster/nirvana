<?php
/**
 * Cущность, базовый класс (модель данных умеющая сохранять себя в БД)
 * 
 * Ключевые слова postgresql
 * https://www.postgresql.org/docs/8.2/static/sql-keywords-appendix.html
 * 
 * Типы данных postgresql
 * http://postgresql.ru.net/manual/datatype.html
 * 
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */


namespace Nirvana\ORM;

use \Nirvana\MVC\Application as App;
use Nirvana\MVC\Relation\RelationFactory;


class Entity extends ORM
{
	/**
	 * Пока так. Позже нужно будет разделить MySQL и Postgres по отдельным классам
	 */
	private function isPostgres()
	{
		$DB = App::getConfigSection('DB');
		return (isset($DB['TYPE']) && $DB['TYPE'] === 'postgres');
	}

	/**
	 * Создаёт/обновляет таблицы так чтобы они соответствовали имеющимся моделям
	 */
	public function updateTable()
	{
		if ($this->isPostgres()) {
			$table = new PostgresTable($this->getTableName(), $this->getClass());
		} else {
			$table = new MysqlTable($this->getTableName(), $this->getClass());
		}

		$table->update();
		//$rc = new \ReflectionClass($this->getClass());

		// $columnsSQL = array();

		// // Комментарии свойств
		// foreach ($rc->getProperties() as $property) {
		// 	$rp = new \ReflectionProperty($property->class, $property->name);
		// 	$parameters = $this->columnOptions($rp->getDocComment());
		// 	if (!isset($parameters['Column'])) continue;
		// 	$sql = $this->columnSQL($property->name, $parameters);
		// 	$columnsSQL[] = $sql;
		// }
		
		// // Установка таблицы
		// if (!$this->getColumnsByTable()) {
		// 	echo "\r\n# Создание таблицы \r\n";
		// 	$tableSQL = $this->tableSQL($this->getTableName(), implode(",\r\n", $columnsSQL));
		// 	echo $tableSQL . "\r\n";

		// 	$res = $this->query($tableSQL); // Создание таблицы
		// 	$row = $res->fetch(\PDO::FETCH_ASSOC);
		// 	if (!$res) var_dump(mysql_error());
		// }

		// // Модификация таблицы
		// $this->modifyTable();
	}

	/**
	 * Возвращает параметры для колонок таблицы основвываясь на модели
	 *
	 * @return array
	 */
	private function getColumnsByModel()
	{
		$result = array();
		$rc = new \ReflectionClass($this->getClass());

		// Комментарии свойств
		foreach ($rc->getProperties() as $property) {
			$rp = new \ReflectionProperty($property->class, $property->name);
			$options = $this->columnOptions($rp->getDocComment());
			if (!isset($options['Column'])) continue;
			$result[$property->name] = $options; // Парсинг комментариев над свойствами
		}

		return $result;
	}

	/**
	 * Возвращает имя класса сущности
	 *
	 * @return string
	 */
	public function getClass()
	{
		return get_class($this);
	}

	/**
	 * Возвращает имя таблицы
	 *
	 * @return mixed
	 */
	public function getTableName()
	{
		$name = preg_replace('/^.*\\\/', '', strtolower($this->getClass()));
		return $this->getTablesPrefix() . $this->getModulePrefix() . $name;
	}

	/**
	 * Префикс таблицы модуля
	 *
	 * @return string
	 */
	private function getModulePrefix()
	{
		preg_match("/([^\\\]+)Module/", $this->getClass(), $matches);

		if (!isset($matches[1])) return '';

		return $this->camelCase2underscore($matches[1]) . '_';
	}

	/**
	 * Префикс всех таблиц
	 *
	 * @return string
	 */
	private function getTablesPrefix()
	{
		return '';
	}

	/**
	 * Сеттеры свойств
	 *
	 * @param $name - Имя метода
	 * @param $arguments
	 * @return
	 * @throws \Exception
	 */
	public function __call($name, $arguments)
	{
		// Сеттеры свойств
		if (strpos($name, 'set') === 0) {
			$column = $this->camelCase2underscore(str_replace('set', '', $name));
			$this->$column = $arguments[0];
		}

		// Геттеры свойств
		if (strpos($name, 'get') === 0) {
			$column = $this->camelCase2underscore(str_replace('get', '', $name));
			$rp     = new \ReflectionProperty($this->getClass(), $column);

			// Поле является защищенным (это связи)
			if ($rp->isProtected()) {
				$relation = Relation\RelationFactory::factory($rp);
				return $relation->getItems($this->id);
			}

			return $this->$column;
		}
	}

	/**
	 * Флаг сохранения сущности в БД
	 *
	 * @var bool
	 */
	private $saved = false;

	/**
	 * 
	 */
	public function saveQueryString()
	{
		$names  = array();
		$values = array();

		foreach ($this->getColumnsByModel() as $name => $options) {
			if (isset($options['GeneratedValue']) && $options['GeneratedValue']['strategy'] === 'AUTO') {
				continue;
			}

			$names[] = $name;

			$val = $this->$name;
			$val = (is_null($val) && isset($options['Column']['default'])) ? $options['Column']['default'] : $val;
			$val = (is_null($val)) ? "NULL" : $val;

			if ($options['Column']['type'] !== 'string' && $options['Column']['type'] !== 'text' && $options['Column']['type'] !== 'datetime') {
				$values[] = $val;
			} else {
				$values[] = ($val === "NULL") ? $val : "'" . $this->$name . "'";
			}
		}

		//var_dump($values);
		$table  = $this->getTableName();
		$names  = implode(', ', $names);
		$values = implode(', ', $values);
		
		return "INSERT INTO {$table} ($names) VALUES ($values)";
	}

	/**
	 * Сохранение сущности в БД в БД
	 */
	public function save()
	{
		$mysql = App::getAdapter();
		$res = $mysql->query($this->saveQueryString());

		if ($res) {
			$this->saved = true;
			$this->id = $mysql->lastInsertId();
			return $this->id;
		}

		return false;
	}

	/**
	 * Обновление сущности
	 */
	public function update()
	{
		$table = $this->getTableName();
		$values = array();

		foreach ($this->getColumnsByModel() as $name => $options) {
			if (isset($options['GeneratedValue']) && $options['GeneratedValue']['strategy'] === 'AUTO') continue;

			if ($options['Column']['type'] !== 'string' && $options['Column']['type'] !== 'text' && $options['Column']['type'] !== 'datetime') {
				$values[] = $name . '=' . $this->$name;
			} else {
				$values[] = $name . "='" . $this->$name . "'";
			}
		}

		$values = implode(', ', $values);
		$sql = "UPDATE `$table` SET $values WHERE `id` = {$this->id};";
		$res = $this->query($sql);
		return $res;
	}

	/**
	 * Удаление сущности
	 */
	public function delete()
	{
		// Если метода нет он попадёт в __call
		$this->beforeDelete();

		$table = $this->getTableName();
		$query = "DELETE FROM `$table` WHERE `id` = {$this->id};";
		$res = $this->query($query);
//		$query = 'DELETE FROM :table WHERE id = :id;';
//		$res = $this->query($query, array('table' => $table, 'id' => $this->id));

		$this->afterDelete();

		return $res;
	}

	/**
	 * Обеспечивает фильтрацию данных от XSS по принципу белых списков.
	 *
	 * @param $propertyName - Имя свойства
	 * @return mixed
	 */
	public function filterXSS($propertyName)
	{
		$body = $this->$propertyName;

		// Фильтрация тегов
		$tags = '<p><a><span><br><img><hr><ul><li><table><tr><td><h1><h2><h3><em><blockquote><tbody><div>';
		$body = strip_tags($body, $tags);

		// Поиск тегов
		$body = preg_replace_callback('/<[^>]*>/', function ($matches) {
			// Поиск названий атрибутов
			$matches[0] = preg_replace_callback('/([a-z]+) *=/i', function ($matches) {
				if (preg_match('/(style|src|class|id|name|href)/i', $matches[1])) {
					return $matches[0];
				}
				return 'data-' . $matches[0]; // "экранирование" запрещённых аттрибутов
			}, $matches[0]);
			return $matches[0];
		}, $body);

		return $body;
	}

	/**
	 * Возвращает экземпляр класса Repository для конкретного типа сущности
	 *
	 * @param $entityClassName - Имя класса сущности
	 * @return Repository
	 */
	public function getRepository($entityClassName)
	{
		return new Repository($entityClassName);
	}
}

