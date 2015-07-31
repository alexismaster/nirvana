<?php
/**
 * Пост
 */

namespace Src\Entity;

use \Nirvana\ORM as ORM;


class Post extends ORM\Entity
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="string", length=100, unique=true)
	 */
	public $title;

	/**
	 * @ORM\Column(type="text")
	 */
	public $body;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $user_id;

	/**
	 * getComments()
	 *
	 * При вызове этого метода произойд]т следующее:
	 * -
	 * - К таблице комментариев будут добавлены поля из таблицы пользователей
	 *
	 * @ORM\OneToMany(targetEntity="Comment", mappedBy="topic_id")
	 * @ORM\JoinTable(name="user", columns="username,email")
	 */
	protected $comments;

	/**
	 * afterUpdate
	 */
	public function afterUpdate()
	{
		//....
	}

	/**
	 * Удаление комментариев к посту
	 */
	public function beforeDelete()
	{
		$repository = $this->getRepository('Comment');
		$repository->deleteByTopicId($this->id);
	}
}


