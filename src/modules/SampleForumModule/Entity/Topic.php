<?php


namespace SRC\SampleForumModule\Entity;

use \Nirvana\ORM as ORM;


class Topic extends ORM\Entity
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $user_id;
}