<?php
/**
 * Пользователь
 */

namespace Src\Entity;


class User extends \Nirvana\ORM\Entity
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	public $username;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	public $password;

	/**
	 * @ORM\Column(type="string", length=45, unique=true)
	 */
	public $email;

	/**
	 * @param $password
	 * @return bool
	 */
	public function checkPassword($password)
	{
		return $this->password === md5($password);
	}
}
