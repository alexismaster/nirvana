<?php
/**
 * Контроллер топиков
 *
 * @category   SampleForumModule
 * @package    Controllers
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */


namespace SRC\Module\SampleForumModule\Controller;

use \Nirvana\MVC as MVC;


class TopicController extends MVC\Controller
{

	public function addAction()
	{
		$this->render('topic/add.twig');
	}
}