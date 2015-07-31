<?php
/**
 * Фабрика объектов связей
 */

namespace Nirvana\ORM\Relation;


class RelationFactory
{
    /**
     * Фабричный метод
     *
     * @param $rp \ReflectionProperty - Комментарий к свойству
     * @return Relation
     */
    public static function factory($rp)
    {
        $comment = $rp->getDocComment();

        if (strpos($comment, 'OneToMany'))  return new OneToManyRelation($rp);
        if (strpos($comment, 'ManyToOne'))  return new ManyToOneRelation($rp);
        if (strpos($comment, 'OneToOne'))   return new OneToOneRelation($rp);
        if (strpos($comment, 'ManyToMany')) return new ManyToManyRelation($rp);
    }
}