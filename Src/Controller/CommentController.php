<?php
/**
 * Контроллер комментариеев
 *
 * @category   Application
 * @package    Controllers
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace SRC\Controller;


use \Nirvana\MVC as MVC;
use \SRC\Entity as Entity;


class CommentController extends MVC\Controller
{
	/**
	 * Создание комментария
	 *
	 * @param $postId - ID-шник поста
	 */
	public function addAction($postId)
	{

		// Отправлена форма && пользователь авторизован
		if ($this->isRequestMethod('POST') and isset($_SESSION['user'])) {
			$comment = new Entity\Comment();
			$comment->setComment(mysql_real_escape_string($_POST['comment']));
			$comment->setParentId($_POST['parent']);
			$comment->setUserId(unserialize($_SESSION['user'])->id);
			$comment->setTopicId($postId);

			if ($comment->save()) {
				return $this->redirect('/post/' . $postId . '#comment_' . $comment->id);
			} else {
				$errors['mysql'] = mysql_error();
			}
		}

		$this->redirect($_SERVER['HTTP_REFERER']);
	}

	/**
	 * Удаление комментария
	 *
	 * @param $id
	 */
	public function deleteAction($id)
	{
		$id = (int)$id;
		$adapter = MVC\Application::getAdapter();
		$result = $adapter->fetchOne("SELECT count(*) as `count` FROM `comment` WHERE `parent_id` = {$id}");

		if ($result and (int)$result['count'] > 0) {
			$this->render('comment/delete.twig', array('success' => false, 'url' => $_SERVER['HTTP_REFERER']));
		} else {
			$repository = $this->getRepository('Comment');
			$repository->deleteById($id);

			$this->redirect($_SERVER['HTTP_REFERER']);
		}
	}

}
