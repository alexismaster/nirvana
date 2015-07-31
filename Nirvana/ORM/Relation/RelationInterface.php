<?php
/**
 * Все связи должны реализоввывать метод возвращающий набор
 * соответствующих связи сущностей
 */

namespace Nirvana\ORM\Relation;


interface RelationInterface
{
    public function getItems($id);
}