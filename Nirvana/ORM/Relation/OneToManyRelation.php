<?php
/**
 * Связь вида "один ко многим".
 */

namespace Nirvana\ORM\Relation;

use Nirvana\ORM as ORM;


class OneToManyRelation extends Relation implements RelationInterface
{
    /**
     * Тип связанных сущностей
     *
     * @var
     */
    private $targetEntity;

    /**
     * Поле в котором связанные сущности хранят ID (вероятно его стоит сделать стандартным)
     *
     * @var
     */
    private $mappedBy;

    /**
     * Имя таблицы связанных сущностей
     *
     * @var
     */
    private $table;

    /**
     * Подключаемые таблицы
     *
     * @var
     */
    private $joinedTables;

    /**
     * @var string
     */
    private $comment;

    /**
     * @param $rp \ReflectionProperty
     */
    public function __construct($rp)
    {
        $this->comment = $rp->getDocComment();
        $this->_prepareComment($this->comment);
    }

    /**
     * getItems
     *
     * @param $id - Ид объекта для которого выбираются связи
     * @return array|void
     */
    public function getItems($id)
    {
        $repository = new ORM\Repository($this->targetEntity);
        $parameters = array($this->mappedBy => $id);

        // Нет подключаемых таблиц
        if (!strpos($this->comment, 'JoinTable')) {
            return $repository->findBy($parameters);
        }
        else {
            $_JOIN_SQL = array(); // JOIN-ы
            $_JOIN_COL = array(); // Колонки присоединяемых таблиц

            foreach ($this->joinedTables as $t) {
                $_JOIN_SQL[] = 'LEFT JOIN '.$t['name'].' ON '.$t['name'].'.id = '.$this->table.'.'.$t['name'].'_id';
                $_JOIN_COL   = array_merge($_JOIN_COL, $this->_prepareJoinedColumns($t['columns'], $t['name']));
            }

            return $repository->findBySql('
				SELECT
				  '.$this->table.'.*, '.implode(',', $_JOIN_COL).'
				FROM
				  '.$this->table.' '.implode(' ', $_JOIN_SQL).'
				WHERE
				  '.$this->mappedBy.' = :'.$this->mappedBy, $parameters);
        }
    }

    /**
     * Парсит комментарий свойства
     * @param $comment
     * @return array
     */
    private function _prepareComment($comment)
    {
        $result  = array();
        $comment = explode("\r\n", $comment);

        foreach($comment as $line) {

            // Подключаемые JOIN-ами таблицы
            if (strpos($line, '@ORM\JoinTable(')) {
                $line = str_replace(' ', '', $line);
                $line = str_replace('*', '', $line);
                $line = str_replace('"', '', $line);
                $line = str_replace("\t", '', $line);
                $line = str_replace('name=', '', $line);
                $line = str_replace('@ORM\JoinTable(', '', $line);
                $line = str_replace(')', '', $line);

                $result[] = array_combine(array('name', 'columns'), explode(',columns=', $line));
            }
            // OneToMany
            else if (strpos($line, '@ORM\OneToMany(')) {
                $line = str_replace(' ', '', $line);

                $i1 = strpos($line, 'targetEntity="');  // 14
                $i2 = strpos($line, '",mappedBy="');    // 12
                $i3 = strpos($line, '")');

                $this->targetEntity = substr($line, $i1 + 14, $i2 - $i1 - 14);
                $this->mappedBy     = substr($line, $i2 + 12, $i3 - $i2 - 12);
                $this->table        = strtolower($this->targetEntity);
            }
        }

        $this->joinedTables = $result;
    }

    /**
     * _prepareJoinedColumns
     *
     * @param $columns
     * @param $table
     * @return array
     */
    private function _prepareJoinedColumns($columns, $table)
    {
        $columns = explode(',', $columns);

        foreach ($columns as $i => $column) {
            $columns[$i] = $table.'.'.$column;
        }

        return $columns;
    }
}