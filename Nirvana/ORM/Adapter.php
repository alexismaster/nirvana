<?php
/**
 * Адаптер (Singleton)
 *
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\ORM;


class Adapter
{
	/**
	 * Порт MySQL по уоолчанию
	 */
	const DEFAULT_MYSQL_PORT = '3306';

	/**
	 * Кодировка соединения по умолчанию
	 */
	const DEFAULT_CHARSET = 'utf8';

	/**
	 * Экземпляр класса
	 *
	 * @var Adapter
	 */
	protected static $_instance;

	/**
	 * Ресурс подключения к БД
	 *
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * Конструктор
	 *
	 * @param array $config - Настройки подключения к БД
	 * @throws \Exception
	 */
	private function __construct(array $config)
	{
		if (!isset($config['CHARSET'])) {
			$config['CHARSET'] = self::DEFAULT_CHARSET;
		}

		if (!isset($config['MYSQL_PORT'])) {
			$config['MYSQL_PORT'] = self::DEFAULT_MYSQL_PORT;
		}

		try {
			$dsn = 'mysql:host=' . $config['MYSQL_HOST'] .
					';port='     . $config['MYSQL_PORT'] .
					';dbname='   . $config['MYSQL_BASE'] .
					';charset='  . $config['CHARSET'];

			$this->pdo = new \PDO($dsn, $config['MYSQL_USER'], $config['MYSQL_PASS']);
		}
		catch (\PDOException $error) {
			switch ($error->getCode()) {
				case 2005: $msg = 'Не верный хост.'; break;
				case 1045: $msg = 'Не верный логин и/или пароль.'; break;
				case 1049: $msg = 'База не существует.'; break;
				default:   $msg = 'Код ошибки: ' . $error->getCode();
			}

			throw new \Exception('Не удалось подключиться к БД. ' . $msg);
		}
		catch (\Exception $error) {
			//var_dump($error->getCode());
			throw new \Exception('Не удалось подключиться к БД');
		}
	}

	/**
	 * Запрет клонирования
	 */
	private function __clone()
	{
		// ...
	}

	/**
	 * Выполняет запрос к БД
	 *
	 * @param $sql - Это должен быть корректный запрос с точки зрения целевой СУБД.
	 * @param $input_parameters - Массив значений, содержащий столько элементов, сколько параметров заявлено в SQL запросе.
	 * @return \PDOStatement - Ассоциированный с запросом объект
	 */
	public function query($sql, array $input_parameters = array())
	{
		$request = $this->pdo->prepare($sql);
		$request->execute($input_parameters);

		return $request;
	}

	/**
	 * Возвращает ID последней вставленной строки или последовательное значение
	 *
	 * @param null $name
	 * @return string
	 */
	public function lastInsertId($name = null)
	{
		return $this->pdo->lastInsertId($name);
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
	 * fetchOne
	 *
	 * @param $sql - Это должен быть корректный запрос с точки зрения целевой СУБД.
	 * @return array
	 */
	public function fetchOne($sql)
	{
		$result = $this->query($sql);

		if ($result && $result->rowCount()) {
			return $result->fetch(\PDO::FETCH_ASSOC);
		}
	}

	/**
	 * fetchAll
	 *
	 * @param $sql
	 */
	public function fetchAll($sql)
	{
		//...
	}
}
