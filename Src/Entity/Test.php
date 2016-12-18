<?php
/**
 * Тестовая сущность
 * 
 * 
 * 
 * 
 * Доступные типы данных:
 * ======================
 * 
 * 
 * строка с переменной длиной
 * @ORM\Column(type="string", length=100)
 * --------------------------------------
 * postgres:	character varying (n)
 * mysql:			varchar(n)
 * 
 * строка с фиксированной длиной
 * @ORM\Column(type="char", length=5)
 * ------------------------
 * postgres: character [ (n) ]
 * mysql: 
 * 
 * @ORM\Column(type="json")
 * ------------------------
 * postgres:
 * mysql:
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */

namespace Src\Entity;

use \Nirvana\ORM as ORM;


class Test extends ORM\Entity
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", length=100, unique=true)
	 */
	public $title;

	/**
	 * @ORM\Column(type="text")
	 */
	public $body;
}
