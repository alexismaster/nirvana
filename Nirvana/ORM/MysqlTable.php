<?php

// MysqlTable.php


namespace Nirvana\ORM;


class MysqlTable extends Table
{
	/**
	 * Конструктор
	 */
	public function __construct($table_name, $class_name)
	{
		parent::__construct($table_name, $class_name);
	}

	/**
	 * 
	 */
	protected function escapeString($string)
	{
		return '`'.$string.'`';
	}

	/**
	 * Возвращает часть запроса CREATE TABLE для конкретного столбца
	 * @param $name
	 * @param $parameters
	 * @return string
	 */
	private function columnSQL($name, $parameters)
	{
		$type = $this->getTypeColumn($parameters);
		// $default = $this->getDefaultByModel($parameters);

		// if (strpos($type, 'character varying') !== false && $default !== 'NULL') {
		// 	$default = "'{$default}'";
		// }

		// if ($this->isNullableByModel($parameters)) {
		// 	return "\t\"$name\" $type DEFAULT {$default}";
		// } else {
		// 	return "\t\"$name\" $type NOT NULL DEFAULT {$default}";
		// }
	}

	/**
	 * Возвращает тип колонки по комментариям в модели
	 * 
	 * http://artemfedorov.ru/etc/mysql/field-types/
	 * 
	 * @param $properties
	 * @return string
	 */
	private function getTypeColumn($properties)
	{
		$type = $properties['Column']['type'];
		
		if ($type === 'uuid') {
			return 'varchar(18)';
		}
		if ($type === 'json') {
			return 'text';
		}
		if ($type === 'bigint') {
			return 'bigint(20)';
		}
		if ($type === 'bigint unsigned') {
			return 'bigint(20) unsigned';
		}
		// Строка фиксированной длины
		if ($type === 'char') {
			$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : 1;
			return 'char('.$ln.')';
		}

		if ($type === 'integer') {
			$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : '11';
			// AUTO_INCREMENT
			if (isset($properties['GeneratedValue']) && $properties['GeneratedValue']['strategy'] === 'AUTO') {
				return 'int(' . $ln . ') AUTO_INCREMENT PRIMARY KEY';
			}
			return 'int(' . $ln . ')';
		}

		// varchar(N)
		if ($type === 'string') {
			$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : '250';
			return 'varchar(' . $ln . ')';
		}

		return $type;
	}


	/**
	 * Возвращает параметры для колонок таблицы основвываясьна структуре таблицы
	 *
	 * @return array
	 */
	private function getColumnsByTable()
	{
		$columns = array();
		$result  = $this->query('SHOW COLUMNS FROM ' . $this->table_name);

		if (!$result) return;

		while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
			$columns[$row['Field']] = $row;
		}
		return $columns;
	}

	/**
	 * ....
	 */
	private function modifyTable()
	{
		$alters = array();
		$columnsT = $this->getColumnsByTable();
		$columnsM = $this->getColumnsByModel($this->class_name);

		// Удаление колонок которых нет в модели но есть в БД
		foreach ($columnsT as $name => $properties) if (!isset($columnsM[$name]) || is_null($columnsM[$name])) {
			$alters[] = $this->dropColumnSql($name);
		}

		// Колонки которые нужно добавить (нет в БД, но есть в модели)
		foreach ($columnsM as $name => $properties) if (!isset($columnsT[$name]) || is_null($columnsT[$name])) {
			$alters[] = $this->addColumnSql($name, $this->getTypeColumn($properties));
		}

		// Колонки которые есть в модели и в БД
		foreach ($columnsM as $name => $properties) if (isset($columnsT[$name]) && !is_null($columnsT[$name])) {
			$typeM = $this->getTypeColumn($properties);
			// $typeT = $columnsT[$name]['data_type'];
			// if (isset($columnsT[$name]['character_maximum_length'])) {
			// 	$typeT = $typeT . ' (' . $columnsT[$name]['character_maximum_length'] . ')';
			// }

			// Значение по умолчанию
			$defaultT  = $this->getDefaultByTable($columnsT[$name]);
			$defaultM  = $this->getDefaultByModel($properties);
			$nullableT = $this->isNullableByTable($columnsT[$name]);
			$nullableM = $this->isNullableByModel($columnsM[$name]);
			$isCompare = $this->compareDefaultValue($defaultM, $defaultT);

			// Есть различия между колонкой в БД и колонкой в модели
			if ($typeM !== $typeT || !$isCompare) {
				echo "<b>{$this->table_name}.{$name}</b>\r\n";
				echo "<b>model:</b> {$typeM}\r\n";
				echo "<b>table:</b> {$typeT}\r\n";
				echo "<b>model_default:</b>" . $defaultM . "\r\n";
				echo "<b>table_default:</b>" . $defaultT . "\r\n";

				// Типы данных не отличаются
				if ($typeM === $typeT) {
					// // Удаление метки "NOT NULL"
					// if (!$nullableT && $nullableM) {
					// 	$alters[] = "ALTER TABLE \"{$this->table_name}\" ALTER COLUMN \"{$name}\" DROP NOT NULL;";
					// }
					// // Удаление метки "NOT NULL"
					// if ($nullableT && !$nullableM) {
					// 	$alters[] = "ALTER TABLE \"{$this->table_name}\" ALTER COLUMN \"{$name}\" SET NOT NULL;";
					// }
					// // Не равны значения по умолчанию 
					// if (!$isCompare) {
					// 	$alters[] = "ALTER TABLE \"{$this->table_name}\" ALTER COLUMN \"{$name}\" SET DEFAULT {$defaultM};";
					// }
				}
				// Типы данных отличаются
				else {
					$alters[] = $this->modifyColumnSql($name, $typeM);
				}

				echo "===================================\r\n";
			}
		}

		if (count($alters)) {
			foreach ($alters as $sql) {
				echo "<code>{$sql}</code>\r\n";
				$res = App::getAdapter()->query($sql);
			}
		}
	}

	/**
	 * Сравнение значений по умолчанию
	 */
	private function compareDefaultValue($byModel, $byTable) {
		// if (preg_match('/^NULL/', $byTable)) {
		// 	$byTable = 'NULL';
		// }
		// if ("'{$byModel}'::character varying" === $byTable) {
		// 	return true;
		// }
		return ($byModel === $byTable);
	}

	/**
	 * 
	 */
	protected function isNullableByTable($column)
	{
		//return ($column['is_nullable'] === 'YES');
	}

	/**
	 * 
	 * 
	 */
	private function getDefaultByTable($column)
	{
		// if (is_null($column['column_default'])) {
		// 	return "NULL";
		// }
		// return $column['column_default'];
	}

	/**
	 * 
	 * $model
	 */
	private function getDefaultByModel($model)
	{
		if (!isset($model['Column']) || !isset($model['Column']['default'])) {
			return "NULL";
		}
		// if ($model['Column']['default'] === 'CURRENT_TIMESTAMP') {
		// 	return "('now'::text)::timestamp without time zone";
		// }
		return $model['Column']['default'];
	}

	/**
	 * Возвращает SQL для создания колонки
	 */
	private function addColumnSql($column_name, $type)
	{
		$columns = $this->getColumnsByModel($this->class_name);
		$default = $this->getDefaultByModel($columns[$column_name]);

		// if (strpos($type, 'character varying') !== false && $default !== 'NULL') {
		// 	$default = "'{$default}'";
		// }

		if ($this->isNullableByModel($columns[$column_name])) {
			//return "ALTER TABLE \"{$this->table_name}\" ADD \"{$column_name}\" {$type} DEFAULT {$default};";
		} else {
			//return "ALTER TABLE \"{$this->table_name}\" ADD \"{$column_name}\" {$type} NOT NULL DEFAULT {$default};";
		}
	}

	/**
	 * Возвращает SQL для удаления колонки
	 */
	private function dropColumnSql($column_name)
	{
		//return "ALTER TABLE \"{$this->table_name}\" DROP COLUMN \"{$column_name}\";";
	}

	/**
	 * Возвращает SQL для изменения типа колонки
	 */
	private function modifyColumnSql($column_name, $type)
	{
		if ($type === "uuid") {
			//return "ALTER TABLE \"{$this->table_name}\" ALTER COLUMN \"{$column_name}\" TYPE {$type} USING uuid::uuid";
		} else {
			//return "ALTER TABLE \"{$this->table_name}\" ALTER COLUMN \"{$column_name}\" TYPE {$type};";
		}
	}
}
