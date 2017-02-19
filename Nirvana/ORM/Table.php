<?php

// Table.php


namespace Nirvana\ORM;


abstract class Table extends ORM
{
	public $table_name;
	public $class_name;
	public $properties;

	public function __construct($table_name, $class_name)
	{
		$this->table_name = $table_name;
		$this->class_name = $class_name;
		$this->properties = new \ReflectionClass($this->class_name);
		//var_dump($this->properties);
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

		// 	$alters = array_merge($alters, $this->getAlterIndex($columnsT, $columnsM));
	}

	/**
	 * Удаление
	 */
	public function delete()
	{
		$this->query("DROP TABLE {$this->escapeString($this->table_name)};");
	}

	/**
	 * Парсит комментарий к свойству
	 *
	 * @param $comment - Комментарий
	 * @return array
	 */
	protected function columnOptions($comment)
	{
		$result = array();
		preg_match_all('/@ORM\\\(.*)/', $comment, $fragments);

		// Фрагменты комментария к свойству класаа (т.е. строки вида @ORM\Column(type="string", length=100))
		foreach ($fragments[1] as $commentFragment) {
			$options = preg_replace_callback('/^([a-z]+)(\\((.*)\\))?/i', function ($matches) {
				if (isset($matches[3])) {
					preg_match_all('/([a-z]+)="?([a-z0-9_]+ ?[a-z0-9_]*)/i', $matches[3], $matches2);
					$res = array_combine($matches2[1], $matches2[2]);
				} else {
					$res = array();
				}
				return json_encode(array('type' => $matches[1], 'parameters' => $res));
			}, $commentFragment);

			$options = json_decode($options, true);
			$result[$options['type']] = $options['parameters'];
		}

		return $result;
	}

	/**
	 * Возвращает комментарий к свойству
	 * 
	 * $property
	 */
	protected function getPropertyComment($property)
	{
		$rp = new \ReflectionProperty($property->class, $property->name);
		return $rp->getDocComment();
	}

	/**
	 * Возвращает параметры для колонок таблицы основвываясь на модели
	 *
	 * @return array
	 */
	protected function getColumnsByModel()
	{
		$result = array();
		$rc = new \ReflectionClass($this->class_name);

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
