<?php
/**
 * Контроллер постов
 *
 * @category   Application
 * @package    Controllers
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Src\Controller;

use \Nirvana\MVC as MVC;
use \Src\Entity as Entity;


class PostController extends MVC\Controller
{
	/**
	 * Список постов
	 */
	public function indexAction()
	{
		$repository = $this->getRepository('Post');

		$posts = $repository->findBySql("
            SELECT
                `post`.*,
                (SELECT count(*) FROM `comment` WHERE `comment`.`topic_id` = `post`.`id`) as `count_comments`,
                `user`.`username`
            FROM
                `post`
            LEFT JOIN
                `user`
            ON
                `user`.`id` = `post`.`user_id`
            LIMIT 0, 100
        ");

		if ($posts) foreach ($posts as $post) {
			$post->body = preg_replace('/\[cut\/\].*$/', '........', $post->body);
		}

		$this->render('post/allPosts.twig', array('posts' => $posts));
	}

	/**
	 * Страница поста
	 *
	 * @param $id - Идентификатор поста
	 */
	public function showPostAction($id)
	{
		// Пост
		$repository = $this->getRepository('Post');
		$post = $repository->findById($id);
		$post[0]->body = preg_replace('/\[cut\/\]/', '', $post[0]->body);

		// Пользователь
		$repository = $this->getRepository('User');
		$user = $repository->findById($post[0]->user_id);

		/*// Комментарии (старый способ запроса)
		$repository = $this->getRepository('Comment');
		$comments = $repository->findBySql('
            SELECT
              comment.*, user.username
            FROM
              comment
            LEFT JOIN
              user
            ON
              user.id = comment.user_id
            WHERE
              topic_id = :topic_id
        ', array('topic_id' => $post[0]->id));*/

		$comments = $post[0]->getComments(); // Получение комментариев с помощью связей
		//var_dump($comments[0]);

		$count = count($comments);
		$tree = ($count) ? $this->tree($comments, 0) : array(); // Дерево комментариев

		$this->render('post/post.twig',
				array('post' => $post[0], 'user' => $user[0], 'comments' => $tree, 'count' => $count));
	}

	/**
	 * Возвращает дочерние комментарии
	 *
	 * @param $comments
	 * @param int $parent
	 * @return array
	 */
	public function child($comments, $parent = 0)
	{
		$result = array();

		foreach ($comments as $key => $item) {
			if ((int)$item->parent_id === (int)$parent) $result[$key] = $item;
		}

		return $result;
	}

	/**
	 * Строит дерево комметариев
	 *
	 * @param $array
	 * @param $parent
	 * @param null $comments
	 * @return array|null
	 */
	public function tree($array, $parent, & $comments = null)
	{
		if (!$comments) {
			$comments = $this->child($array, $parent);
		}

		foreach ($comments as $id => $comment) {
			$child = $this->tree($array, $comment->id); // Ищем детей для текущего комментария

			// Если найдены дети
			if (count($child)) {
				$comments[$id]->childs = $child;
				$this->tree($array, $comment->id, $comments[$id]->childs);
			} else {
				$comments[$id]->childs = array(); // fix
			}
		}

		return $comments;
	}

	/**
	 * Создание поста
	 */
	public function addAction()
	{
		$errors = array();

		// Отправлена форма && пользователь авторизован
		if ($this->isRequestMethod('POST') and isset($_SESSION['user'])) {
			// Валидация
			if (mb_strlen($_POST['title'], 'utf8') < 5) $errors['title'] = 'Слишком короткий заголовок';
			if (mb_strlen(strip_tags($_POST['body']), 'utf8') < 15) $errors['body'] = 'Слишком короткий пост (меньше 15 символов)';

			if (!count($errors)) {
				$post = new Entity\Post();
				$post->setTitle($_POST['title']);
				$post->setBody($_POST['body']);
				$post->setUserId(unserialize($_SESSION['user'])->id);

				// Сохранение пользователя в БД
				if ($post->save()) {
					return $this->redirect('/post/' . $post->id);
				} else {
//					$errors['mysql'] = mysql_error();
//					if (strpos($errors['mysql'], 'Duplicate entry') !== false) {
//						$errors['mysql'] = 'Пост с таким заголовком уже существует';
//					}
				}
			}
		}

		$this->render('post/add.twig', array('values' => $_POST, 'errors' => $errors));
	}

	/**
	 * Валидация
	 *
	 * @param $post
	 * @return array
	 */
	public function validate($post)
	{
		$errors = array();
		if (mb_strlen($_POST['title'], 'utf8') < 5) $errors['title'] = 'Слишком короткий заголовок';
		if (mb_strlen(strip_tags($_POST['body']), 'utf8') < 15) $errors['body'] = 'Слишком короткий пост (меньше 15 символов)';
		return $errors;
	}

	/**
	 * Редактирование поста
	 *
	 * @param $postId - ID-шник поста
	 */
	public function editAction($postId)
	{
		$errors = array();
		$values = array();

		// Пост
		$repository = $this->getRepository('Post');
		$post = $repository->findById($postId);

		if ($post) {
			$post = $post[0];
			$values['title'] = $post->title;
			$values['body'] = $post->body;

			// Отправлена форма && пользователь авторизован
			if ($this->isRequestMethod('POST') and isset($_SESSION['user'])) {
				$values['title'] = $_POST['title'];
				$values['body'] = $_POST['body'];

				// Валидация
				if (mb_strlen($values['title'], 'utf8') < 5) $errors['title'] = 'Слишком короткий заголовок';
				if (mb_strlen(strip_tags($values['body']), 'utf8') < 15) $errors['body'] = 'Слишком короткий пост (меньше 15 символов)';

				if (!count($errors)) {
					$post->setTitle(mysql_real_escape_string($values['title']));
					$post->setBody(mysql_real_escape_string($values['body']));

					// Сохранение пользователя в БД
					if ($post->update()) {
						return $this->redirect('/post/' . $post->id);
					} else {
						$errors['mysql'] = mysql_error();
					}
				}
			}
		}

		$this->render('post/edit.twig', array('values' => $values, 'errors' => $errors));
	}

	/**
	 * Удаление поста
	 *
	 * @param $postId
	 * @throws \Exception
	 */
	public function deleteAction($postId)
	{
		// Пост
		$repository = $this->getRepository('Post');
		$post = $repository->findById($postId);

		if ($post) {
			$post = $post[0];
			$post->delete();
			$this->redirect('/');
		} else {
			throw new \Exception('Пост не найден. Возможно вы случайно 2 раза кликнули по кнопке "удалить".');
		}
	}
}
