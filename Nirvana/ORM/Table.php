<?php

// Table.php


namespace Nirvana\ORM;


abstract class Table extends ORM
{
	public $table_name;
	public $class_name;
	public $properties;

	/**
	 * Конструктор
	 */
	public function __construct($table_name, $class_name)
	{
		$this->table_name = $table_name;
		$this->class_name = $class_name;
		$this->properties = new \ReflectionClass($this->class_name);
	}

	/**
	 * Установка
	 */
	public function install()
	{
		$columnsSQL = array();

		// Комментарии свойств
		foreach ($this->properties->getProperties() as $property) {
			$parameters = $this->columnOptions($this->getPropertyComment($property));
			
			if (isset($parameters['Column'])) {
				$columnsSQL[] = $this->columnSQL($property->name, $parameters);
			}
		}

		$columnsSQL = implode(",\r\n", $columnsSQL);
		$tableSql = "CREATE TABLE {$this->escapeString($this->table_name)} (\r\n$columnsSQL\r\n);";
		print($tableSql);
		$this->query($tableSql);
	}

	/**
	 * Обновление
	 */
	public function update()
	{
		// Если таблица еще не установлена
		if (!$this->getColumnsByTable()) {
			$this->install();
		}

		// Модификация таблицы
		$this->modifyTable();

		// $alters = array_merge($alters, $this->getAlterIndex($columnsT, $columnsM));
	}

	/**
	 * Удаление
	 */
	public function delete()
	{
		$this->query("DROP TABLE {$this->escapeString($this->table_name)};");
	}

	/**
	 * Возвращает TRUE если по условиям модели значения колонки могут быть NULL
	 */
	protected function isNullableByModel($column)
	{
		if (isset($column['Id'])) {
			return false;
		}
		if (isset($column['Column']['required']) && $column['Column']['required'] === 'true') {
			return false;
		}
		return true;
	}

	abstract protected function isNullableByTable($column);
	abstract protected function escapeString($string);
}
