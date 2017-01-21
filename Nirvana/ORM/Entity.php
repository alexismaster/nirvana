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
		$rc = new \ReflectionClass($this->getClass());

		$columnsSQL = array();

		// Комментарии свойств
		foreach ($rc->getProperties() as $property) {
			$rp = new \ReflectionProperty($property->class, $property->name);
			$parameters = $this->columnOptions($rp->getDocComment());
			if (!isset($parameters['Column'])) continue;
			$sql = $this->columnSQL($property->name, $parameters);
			$columnsSQL[] = $sql;
		}

		// Установка таблицы
		if (!$this->getColumnsByTable()) {
			echo "\r\n# Создание таблицы \r\n";
			$tableSQL = $this->tableSQL($this->getTableName(), implode(",\r\n", $columnsSQL));
			echo $tableSQL . "\r\n";

			$res = $this->query($tableSQL); // Создание таблицы
			$row = $res->fetch(\PDO::FETCH_ASSOC);
			if (!$res) var_dump(mysql_error());
		}

		// Модификация таблицы
		$this->modifyTable();
	}

	// NOT NULL;
	// NOT NULL DEFAULT '10'; !!!!
	// 
	// NULL DEFAULT '10';
	// NULL; === NULL DEFAULT NULL;


	private function getDefaultByTable($table)
	{
		$result;

		if ($table['Null'] === 'YES') {
			if (!isset($table['Default']) || is_null($table['Default'])) {
				$result = "NULL DEFAULT NULL";
			} else {
				$result = "NULL DEFAULT {$table['Default']}";
			}
		} else {
			if (!isset($table['Default']) || is_null($table['Default'])) {
				$result = "NOT NULL";
			} else {
				$result = "NOT NULL DEFAULT {$table['Default']}";
			}
		}

		return " " . $result;
	}

// array(2) {
//   ["type"]=>
//   string(7) "integer"
//   ["default"]=>
//   string(4) "NULL"
// }

	private function getDefaultByModel($model)
	{
		$result;

		if (isset($model['GeneratedValue'])) {
			return " NOT NULL";
		}

		// По дефолту все required=false
		if (!isset($model['Column']['required']) || $model['Column']['required'] === "false") {
			if (!isset($model['Column']['default']) || $model['Column']['default'] === "NULL") {
				$result = "NULL DEFAULT NULL";
			} else {
				$result = "NULL DEFAULT {$model['Column']['default']}";
			}
		} else {
			if (!isset($model['Column']['default']) || $model['Column']['default'] === "NULL") {
				$result = "NOT NULL";
			} else {
				$result = "NOT NULL DEFAULT {$model['Column']['default']}";
			}
		}

		return " " . $result;
	}

	/**
	 * Подстройка таблицы под модель
	 */
	private function modifyTable()
	{
		$alters = array();
		$columnsT = $this->getColumnsByTable();
		$columnsM = $this->getColumnsByModel();

		// Колонки по таблице
		foreach ($columnsT as $name => $properties) if (!isset($columnsM[$name]) || is_null($columnsM[$name])) {
			$alters[] = $this->dropColumnSql($name);
		}

		// Колонки по модели
		foreach ($columnsM as $name => $properties) {
			if (!isset($columnsT[$name]) || is_null($columnsT[$name])) {
				$alters[] = $this->addColumnSql($name, $this->getTypeColumn($properties));
			} else {
				// изменение типа колонки
				$type = $columnsT[$name][($this->isPostgres() ? 'data_type' : 'Type')];

				if ($this->isPostgres() && isset($columnsT[$name]['character_maximum_length'])) {
					$type = $type . ' (' . $columnsT[$name]['character_maximum_length'] . ')';
				}

				if (isset($columnsT[$name]['Extra']) && $columnsT[$name]['Extra'] === 'auto_increment') $type .= ' AUTO_INCREMENT PRIMARY KEY';

				// Значение по умолчанию
				$defaultT = $this->getDefaultByTable($columnsT[$name]);
				$defaultM = $this->getDefaultByModel($properties);


				// Если типы по БД и по модели различаются
				if ($this->getTypeColumn($properties) !== $type || $defaultT !== $defaultM) {
					echo "<b>{$this->getTableName()}.{$name}</b>\r\n";
					echo "<b>model: {$this->getTypeColumn($properties)}</b>\r\n";
					echo "<b>table: {$type}</b>\r\n";
					// var_dump($defaultT, $defaultM);
					// var_dump($properties);
					// echo "<br>";
					// echo "<br>";
					
					if ($this->getTypeColumn($properties) === 'datetime') { 
						$alters[] = $this->dropColumnSql($name); 
						$alters[] = $this->addColumnSql($name, $this->getTypeColumn($properties)) . $defaultM; 
					} 
					else { 
						$alters[] = $this->modifyColumnSql($name, $this->getTypeColumn($properties)) . $defaultM; 
					} 
				}
			}
		}

		$alters = array_merge($alters, $this->getAlterIndex($columnsT, $columnsM));

		if (count($alters)) {
			$table = $this->getTableName();
			echo "\r\n# Обновление полей таблицы \"{$table}\"\r\n";
		}

		foreach ($alters as $sql) {
			$res = App::getAdapter()->query($sql);
			echo "<font>$sql</font>\r\n";
			if (!$res) echo '<div>' . mysql_error() . '</div>';
		}
	}

	/**
	 * Возвращает массив инструкций "ALTER TABLE" для создания уникальных индексов
	 *
	 * @param $columnsT - Данные полученные из БД (SHOW COLUMNS FROM...)
	 * @param $columnsM - Данные полученные по модели (Парсинг комментариев)
	 * @return array
	 */
	private function getAlterIndex($columnsT, $columnsM)
	{
		$alters = array();

		if ($this->isPostgres()) return $alters;

		// Колонки по модели
		foreach ($columnsM as $name => $properties) {
			if (!isset($columnsT[$name])) $columnsT[$name] = array();
			
			// Создание уникального индекса
			if (isset($properties['Column']['unique']) && $properties['Column']['unique'] == 'true' &&
				$columnsT[$name] && $columnsT[$name]['Key'] != 'UNI'
			) {
				$alters[] = "ALTER TABLE `{$this->getTableName()}` ADD UNIQUE INDEX `$name` (`$name`);";
			}

			// Удаление уникального индекса
			if ($columnsT[$name] && $columnsT[$name]['Key'] === 'UNI' and (!isset($properties['Column']['unique']) ||
					$properties['Column']['unique'] != 'true')
			) {
				$alters[] = "ALTER TABLE `{$this->getTableName()}` DROP INDEX `$name`;";
			}
		}

		return $alters;
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
	 * Возвращает параметры для колонок таблицы основвываясьна структуре таблицы
	 *
	 * @return array
	 */
	private function getColumnsByTable()
	{
		$columns = array();
		
		if ($this->isPostgres()) {
			$result  = $this->query("SELECT * FROM information_schema.columns WHERE table_name ='{$this->getTableName()}';");

			if (!$result) return;

			while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
				// var_dump(array(
				// 		$row['column_name'],
				// 		$row['is_nullable'],
				// 		$row['data_type'],
				// 	))
				$columns[$row['column_name']] = $row;
			}
		}
		else {
			$result  = $this->query('SHOW COLUMNS FROM ' . $this->getTableName());

			if (!$result) return;

			while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
				$columns[$row['Field']] = $row;
			}
		}

		return $columns;
	}

	/**
	 * Парсит комментарий к свойству
	 *
	 * @param $comment - Комментарий
	 * @return array
	 */
	private function columnOptions($comment)
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
	 * Возвращает часть запроса CREATE TABLE для конкретного столбца
	 * @param $name
	 * @param $parameters
	 * @return string
	 */
	private function columnSQL($name, $parameters)
	{
		$type = $this->getTypeColumn($parameters);
		
		if ($this->isPostgres()) {
			return "\t$name $type NOT NULL";
		}

		return "\t`$name` $type NOT NULL";
	}

	/**
	 * Возвращает тип колонки по комментариям в модели
	 * 
	 * 
	 * http://artemfedorov.ru/etc/mysql/field-types/
	 * 
	 * @param $properties
	 * @return string
	 */
	private function getTypeColumn($properties)
	{
		$type = $properties['Column']['type'];
		
		if ($this->isPostgres()) {
			// дата и время (без часового пояса)
			if ($type === 'timestamp') {
				return 'timestamp without time zone';
			}
			// дата и время, включая часовой пояс
			if ($type === 'timestamptz') {
				return 'timestamp with time zone';
			}

			if ($type === 'char') {
				$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : 1;
				return 'character ('.$ln.')';
			}
		}
		// MySQL
		else {
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
		}

		if ($type === 'integer') {
			$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : '11';
			// AUTO_INCREMENT
			if (isset($properties['GeneratedValue']) && $properties['GeneratedValue']['strategy'] === 'AUTO') {
				if ($this->isPostgres()) {
					//return 'SERIAL PRIMARY KEY';
					return 'integer';
				} else {
					return 'int(' . $ln . ') AUTO_INCREMENT PRIMARY KEY';
				}
			}
			if ($this->isPostgres()) {
				return 'integer';
			} else {
				return 'int(' . $ln . ')';
			}
		}

		// varchar(N)
		if ($type === 'string') {
			$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : '250';
			if ($this->isPostgres()) {
				return 'character varying (' . $ln . ')';
			} else {
				return 'varchar(' . $ln . ')';
			}
		}

		return $type;
	}

	/**
	 * Возвращает SQL "CREATE TABLE ..." для отражения сущности в БД
	 *
	 * @param $name string - Название таблицы
	 * @param $columnsSQL string - Колонки
	 * @return string
	 */
	private function tableSQL($name, $columnsSQL)
	{
		// Adapter::DEFAULT_CHARSET
		if ($this->isPostgres()) {
			return "CREATE TABLE \"$name\" (\r\n$columnsSQL\r\n);";
		}

		return "CREATE TABLE `$name` (\r\n$columnsSQL\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	}

	/**
	 * @param $name
	 * @return string
	 */
	private function dropColumnSql($name)
	{
		$table = $this->getTableName();

		if ($this->isPostgres()) {
			return "ALTER TABLE \"{$table}\" DROP COLUMN \"{$name}\"";
		} else {
			//return 'ALTER TABLE `' . $this->getTableName() . '` DROP COLUMN ' . $name;
			return "ALTER TABLE `{$table}` DROP COLUMN `{$name}`";
		}
	}

	/**
	 * Добавление колонки в таблицу
	 * 
	 * @param $name
	 * @param $type
	 * @return string
	 */
	private function addColumnSql($name, $type)
	{
		$table = $this->getTableName();

		if ($this->isPostgres()) {
			return 'ALTER TABLE "' . $table . '" ADD "' . $name . '" ' . $type;
		}
		else {
			return 'ALTER TABLE `' . $table . '` ADD ' . $name . ' ' . $type;
		}
	}

	/**
	 * Изменение типа колонки (не учитывает изменение значения по умолчанию)
	 * 
	 * 
	 * @param $name
	 * @param $type
	 * @return string
	 */
	private function modifyColumnSql($name, $type)
	{
		$table = $this->getTableName();

		if ($this->isPostgres()) {
			if ($type === "uuid") {
				return "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$name}\" TYPE {$type} USING uuid::uuid;";
			} else {
				return "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$name}\" TYPE {$type};";
			}
		} else {
			// uuid - аналог!!!!!!
			return "ALTER TABLE `{$table}` MODIFY {$name} {$type}";
		}
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

