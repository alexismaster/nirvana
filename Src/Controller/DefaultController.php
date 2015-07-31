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
	 * Установка/обновление таблиц БД
	 */
	public function ormAction()
	{
		ob_start();
		$this->updateTables();
		$content = ob_get_contents();
		ob_end_clean();

		$this->render('orm.twig', array('content' => $content));
	}

	/**
	 * Обновление таблиц в соответствии с моделями (Entity)
	 */
	private function updateTables()
	{
		ORM\ORM::updateTables();
	}

	/**
	 * Страница 404-й ошибки
	 *
	 * @param $error
	 */
	public function NotFoundAction($error)
	{
		$this->render('404.twig', array('error' => $error));
	}

}
