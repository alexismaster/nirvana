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

	/**
	 * Отправка запроса на подтверждение регистрации
	 */
	public function sendConfirmMessage()
	{
		try {
			$transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
				->setUsername('username')
				->setPassword('********')
			;

			$mailer = \Swift_Mailer::newInstance($transport);

			$message = \Swift_Message::newInstance('Подтверждение регистрации')
				->setFrom(array('nirvana@gmail.com' => 'Nirvana Boot'))
				->setTo(array('alexismaster@yandex.ru'))
				->setBody('Подтвердите регистрацию на сайте')
			;

			$mailer->send($message);
		}
		catch (\Exception $error) {
			var_dump($error->getMessage());
		}
	}
}
