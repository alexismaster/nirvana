<?php
/**
 * DefaultController
 *
 * @category   App
 * @package    Controllers
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace SRC\Controller;

use \Nirvana\MVC as MVC;
use \Nirvana\ORM as ORM;

class DefaultController extends MVC\Controller
{
	/**
	 * Главная страница
	 */
	public function indexAction()
	{
		$this->render('default/index.twig');
	}

	/**
	 * Установка/обновление таблиц БД
	 */
	public function ormAction()
	{
		ob_start();
        ORM\ORM::updateTables();
		$content = ob_get_contents();
		ob_end_clean();

		$this->render('default/orm.twig', array('content' => $content));
	}

	/**
	 * Страница 404-й ошибки
	 *
	 * @param $error
	 */
	public function NotFoundAction($error)
	{
		$this->render('default/404.twig', array('error' => $error));
	}

}
