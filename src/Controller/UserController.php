<?php
/**
 * Констроллер пользователей
 *
 * @category   Application
 * @package    Controllers
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace SRC\Controller;

use \Nirvana\MVC as MVC;
use \Nirvana\Validator as Validator;
use \SRC\Entity as Entity;


class UserController extends MVC\Controller
{
	/**
	 * Регистрация
	 */
	public function signUpAction()
	{
		$errors = array();

		// Отправлена форма
		if ($this->isRequestMethod('POST')) {

			// Валидация
			if (!Validator\Validator::isEmail($_POST['email'])) $errors['email'] = "Не корректный адрес электронной почты";
			if (strlen($_POST['password']) < 3) $errors['password'] = "Слишком короткий пароль";
			if ($_POST['password'] !== $_POST['password_confirm']) $errors['password_confirm'] = "Пароли не совпадают";

			if (!count($errors)) {
				$user = new Entity\User();
				$user->setEmail($_POST['email']);
				$user->setPassword(md5($_POST['password']));
				$user->setUsername(preg_replace('/@.*$/i', '', $_POST['email']));

				// Сохранение пользователя в БД
				if ($user->save()) {
					return $this->redirect('/sign-up_success/');
				} else {
					$errors['mysql'] = mysql_error();
				}
			}
		}

		$this->render('user/signUp.twig', array('values' => $_POST, 'errors' => $errors));
	}

	/**
	 * Успешная рагистрация
	 */
	public function signUpSuccessAction()
	{
		$this->render('user/signUpSuccess.twig');
	}

	/**
	 * Авторизация
	 */
	public function loginAction()
	{
		$errors = array();

		if ($this->isRequestMethod('POST')) {
			$repository = $this->getRepository('User');
			$user = $repository->findByEmail($_POST['email']);

			// Пользователь существует и пароль указан верно
			if ($user && $user[0]->checkPassword($_POST['password'])) {
				$_SESSION['user'] = serialize($user[0]);
				return $this->redirect('/');
			} else {
				$errors['email'] = "Не верно указан логин или пароль";
			}
		}

		$this->render('user/login.twig', array('values' => $_POST, 'errors' => $errors));
	}

	/**
	 * Выход
	 */
	public function logoutAction()
	{
		unset($_SESSION['user']);
		$this->render('user/logout.twig', array('name' => '123', 'text' => '11111'));
	}

}
