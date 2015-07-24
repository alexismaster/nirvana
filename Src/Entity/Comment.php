<?php
/**
 * Сущность "Комментарий"
 *
 * @category   App
 * @package    Entity
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace SRC\Entity;


class Comment extends \Nirvana\ORM\Entity
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	public $id;

	/**
	 * @ORM\Column(type="text")
	 */
	public $comment;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $parent_id;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $user_id;

	/**
	 * @ORM\Column(type="integer")
	 */
	public $topic_id;
}
