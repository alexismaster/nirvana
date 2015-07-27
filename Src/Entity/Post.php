<?php
/**
 * Пост
 */

namespace Src\Entity;


class Post extends \Nirvana\ORM\Entity
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


