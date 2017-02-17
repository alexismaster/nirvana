<?php

// MysqlTable.php


namespace Nirvana\ORM;


class MysqlTable extends Table
{
	public function __construct($table_name, $class_name)
	{
		parent::__construct($table_name, $class_name);
	}

	public function install()
	{
		//...
	}

	public function update()
	{
		//...
	}

	public function delete()
	{
		//...
	}
}
