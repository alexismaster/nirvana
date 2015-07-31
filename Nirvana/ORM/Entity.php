<?php
/**
 * Cущность, базовый класс (модель данных умеющая сохранять себя в БД)
 *
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\ORM;

use \Nirvana\MVC\Application as App;


class Entity extends ORM
{

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
			$sql = $this->columnSQL($property->name, $parameters);
			$columnsSQL[] = $sql;
		}


		// Установка таблицы
		if (!$this->getColumnsByTable()) {
			echo "\r\n\r\n";
			$tableSQL = $this->tableSQL($this->getTableName(), implode(",\r\n", $columnsSQL));
			echo $tableSQL . "\r\n---------------------------------------------------\r\n";

			$res = $this->query($tableSQL); // Создание таблицы
			if (!$res) var_dump(mysql_error());
		}

		// Модификация таблицы
		$this->modifyTable();
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
		foreach ($columnsT as $name => $properties) if (!$columnsM[$name]) {
			$alters[] = $this->dropColumnSql($name);
		}

		// Колонки по модели
		foreach ($columnsM as $name => $properties) {
			if (!$columnsT[$name]) {
				$alters[] = $this->addColumnSql($name, $this->getTypeColumn($properties));
			} else {
				// изменение типа колонки
				$type = $columnsT[$name]['Type'];
				if ($columnsT[$name]['Extra'] === 'auto_increment') $type .= ' AUTO_INCREMENT PRIMARY KEY';

				if ($this->getTypeColumn($properties) !== $type) {
					$alters[] = $this->modifyColumnSql($name, $this->getTypeColumn($properties));
				}
			}
		}

		$alters = array_merge($alters, $this->getAlterIndex($columnsT, $columnsM));

		foreach ($alters as $sql) {
			$res = App::getAdapter()->query($sql);
			echo "<font color='blue'>$sql</font>";
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

		// Колонки по модели
		foreach ($columnsM as $name => $properties) {
			// Создание уникального индекса
			if (isset($properties['Column']['unique']) && $properties['Column']['unique'] == 'true' &&
				$columnsT[$name]['Key'] != 'UNI'
			) {
				$alters[] = "ALTER TABLE `{$this->getTableName()}` ADD UNIQUE INDEX `$name` (`$name`);";
			}

			// Удаление уникального индекса
			if ($columnsT[$name]['Key'] === 'UNI' and (!isset($properties['Column']['unique']) ||
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
	public function getColumnsByModel()
	{
		$result = array();
		$rc = new \ReflectionClass($this->getClass());

		// Комментарии свойств
		foreach ($rc->getProperties() as $property) {
			$rp = new \ReflectionProperty($property->class, $property->name);
			$result[$property->name] = $this->columnOptions($rp->getDocComment()); // Парсинг комментариев над свойствами
		}

		return $result;
	}

	/**
	 * Возвращает параметры для колонок таблицы основвываясьна структуре таблицы
	 *
	 * @return array
	 */
	public function getColumnsByTable()
	{
		$columns = array();
		$result  = $this->query('SHOW COLUMNS FROM ' . $this->getTableName());

		if (!$result) return;

		while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
			$columns[$row['Field']] = $row;
		}

		return $columns;
	}

	/**
	 * Парсит комментарий к свойству
	 *
	 * @param $comment - Комментарий
	 * @return array
	 */
	public function columnOptions($comment)
	{
		$result = array();
		preg_match_all('/@ORM\\\(.*)/', $comment, $fragments);

		// Фрагменты комментария к свойству класаа (т.е. строки вида @ORM\Column(type="string", length=100))
		foreach ($fragments[1] as $commentFragment) {
			$options = preg_replace_callback('/^([a-z]+)(\\((.*)\\))?/i', function ($matches) {
				if (isset($matches[3])) {
					preg_match_all('/([a-z]+)="?([a-z0-9]+)/i', $matches[3], $matches2);
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
	public function columnSQL($name, $parameters)
	{
		$type = $this->getTypeColumn($parameters);
		return "\t`$name` $type NOT NULL";
	}

	/**
	 * Возвращает тип колонки по комментариям в модели
	 *
	 * @param $properties
	 * @return string
	 */
	public function getTypeColumn($properties)
	{
		if ($properties['Column']['type'] === 'integer') {
			$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : '11';
			// AUTO_INCREMENT
			if (isset($properties['GeneratedValue']) && $properties['GeneratedValue']['strategy'] === 'AUTO') {
				return 'int(' . $ln . ') AUTO_INCREMENT PRIMARY KEY';
			}
			return 'int(' . $ln . ')';
		}

		if ($properties['Column']['type'] === 'string') {
			$ln = (isset($properties['Column']['length'])) ? $properties['Column']['length'] : '250';
			return 'varchar(' . $ln . ')';
		}

		return $properties['Column']['type'];
	}

	/**
	 * Возвращает SQL "CREATE TABLE ..." для отражения сущности в БД
	 *
	 * @param $name string - Название таблицы
	 * @param $columnsSQL string - Колонки
	 * @return string
	 */
	public function tableSQL($name, $columnsSQL)
	{
		// Adapter::DEFAULT_CHARSET
		return "CREATE TABLE `$name` (\r\n$columnsSQL\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	}

	/**
	 * @param $name
	 * @return string
	 */
	public function dropColumnSql($name)
	{
		return 'ALTER TABLE `' . $this->getTableName() . '` DROP COLUMN ' . $name;
	}

	/**
	 * @param $name
	 * @param $type
	 * @return string
	 */
	public function addColumnSql($name, $type)
	{
		return 'ALTER TABLE `' . $this->getTableName() . '` ADD ' . $name . ' ' . $type;
	}

	/**
	 * @param $name
	 * @param $type
	 * @return string
	 */
	public function modifyColumnSql($name, $type)
	{
		return 'ALTER TABLE `' . $this->getTableName() . '` MODIFY ' . $name . ' ' . $type;
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
		preg_match('/([^\\\]+)Module/', $this->getClass(), $matches);

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
	 * Сохранение сущности в БД в БД
	 */
	public function save()
	{
		$names = array();
		$values = array();

		foreach ($this->getColumnsByModel() as $name => $options) {
			if (isset($options['GeneratedValue']) && $options['GeneratedValue']['strategy'] === 'AUTO') {
				continue;
			}

			$names[] = $name;

			if ($options['Column']['type'] !== 'string' && $options['Column']['type'] !== 'text') {
				$values[] = $this->$name;
			} else {
				$values[] = "'" . $this->$name . "'";
			}
		}

		$table = $this->getTableName();
		$names = implode(', ', $names);
		$values = implode(', ', $values);

		$mysql = App::getAdapter();
		$sql = "INSERT INTO {$table} ($names) VALUES ($values)";
		$res = $mysql->query($sql);

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

			if ($options['Column']['type'] !== 'string' && $options['Column']['type'] !== 'text') {
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

