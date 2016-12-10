<?php
/**
 * DevelopController
 *
 * @category   App
 * @package    Controllers
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace SRC\Controller;

use \Nirvana\MVC as MVC;
use \Nirvana\ORM as ORM;
use \Src\Entity as Entity;

class DevelopController extends MVC\Controller
{
	/**
	 * Главная страница
	 */
	public function indexAction()
	{
		return $this->render('default/index.twig');
	}

	/**
	 * Установка/обновление таблиц БД
	 */
	public function ormUpdateAction()
	{
		ob_start();
		ORM\ORM::updateTables();
		$content = ob_get_contents();
		ob_end_clean();

		return $this->render('default/orm.twig', array('content' => $content));
	}

	public function postgresAction()
	{
		//$db = new \PDO('pgsql:dbname=nirvana host=localhost', 'postgres', '13271327');

		// Insert:
		// $entity = new Entity\Test();
		// $entity->setTitle('test');
		// $entity->setBody('body');
		// $entity->save();

		// Select:
		$repository = $this->getRepository('Test');
		$entity = $repository->findById(1);
		var_dump($entity);

		return "";
	}

	/**
	 * Страница 404-й ошибки
	 *
	 * @param $error
	 */
	public function NotFoundAction($error)
	{
		return $this->render('default/404.twig', array('error' => $error));
	}

}
