<?php
/**
 * Тестовая сущность
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
