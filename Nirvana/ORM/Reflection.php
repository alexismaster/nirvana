<?php
/**
 * Парсинг комментариев
 * 
 * @category   Nirvana
 * @package    ORM
 * @author     Alexey Jukov <alexismaster@yandex.ru>
 */

namespace Nirvana\ORM;

use Nirvana\MVC as MVC;


abstract class Reflection
{
	/**
	 * Возвращает параметры для колонок таблицы основвываясь на модели
	 *
	 * @return array
	 */
	protected function getColumnsByModel($class_name)
	{
		$result = array();
		$rc = new \ReflectionClass($class_name);

		// Комментарии свойств
		foreach ($rc->getProperties() as $property) {
			// $rp = new \ReflectionProperty($property->class, $property->name);
			// $options = $this->columnOptions($rp->getDocComment());
			$options = $this->columnOptions($this->getPropertyComment($property));
			if (!isset($options['Column'])) continue;
			$result[$property->name] = $options; // Парсинг комментариев над свойствами
		}

		return $result;
	}

	/**
	 * Парсит комментарий к свойству.
	 * 
	 * Этот код дублируется в Table
	 * 
	 * @param $comment - Комментарий
	 * @return array
	 */
	protected function columnOptions($comment)
	{
		$result = array();
		preg_match_all('/@ORM\\\(.*)/', $comment, $fragments);

		// Фрагменты комментария к свойству класаа (т.е. строки вида @ORM\Column(type="string", length=100))
		foreach ($fragments[1] as $commentFragment) {
			$options = preg_replace_callback('/^([a-z]+)(\\((.*)\\))?/i', function ($matches) {
				if (isset($matches[3])) {
					preg_match_all('/([a-z]+)="?([a-z0-9_]+ ?[a-z0-9_]*)/i', $matches[3], $matches2);
					$res = array_combine($matches2[1], $matches2[2]);
				} else {
					$res = array();
				}
				return json_encode(array('type' => $matches[1], 'parameters' => $res));
			}, $commentFragment);

			$options = json_decode($options, true);
			$result[$options['type']] = $options['parameters'];
		}

		return $result;
	}

	/**
	 * Возвращает комментарий к свойству
	 * 
	 * $property
	 */
	protected function getPropertyComment($property)
	{
		$rp = new \ReflectionProperty($property->class, $property->name);
		return $rp->getDocComment();
	}
}
