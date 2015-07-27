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
	 * Создаёт экземпляры всех имеющихся сущностей и вызывает для них метод "updateTable"
	 */
	private function updateTables()
	{
		foreach (glob(__DIR__ . '/../Entity/*.php') as $path) {
			$className = '\\Src\Entity\\' . pathinfo($path)['filename'];
			$this->updateTable($className);
		}

		foreach (glob(__DIR__ . '/../Module/**/Entity/*.php') as $path) {
			preg_match('/\\/(([A-z]+)Module)/', $path, $matches);
			$className = '\\Src\\Module\\' . $matches[1] . '\\Entity\\' . pathinfo($path)['filename'];
//			var_dump($matches[1]);
//			var_dump($className);
			$this->updateTable($className);
		}
	}

	/**
	 * @param $className
	 */
	private function updateTable($className)
	{
		if (!class_exists($className, true)) {
			echo "<h4 style='color: #8b0000;'>Ошибка: Класс $className не определён</h4>";
			return;
		}

		try {
			$entity = new $className();
			$entity->updateTable();
		} catch (\Exception $error) {
			//
		}
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
