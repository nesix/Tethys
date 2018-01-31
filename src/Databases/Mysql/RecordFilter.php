<?php

namespace Tethys\Databases\Mysql;

use Tethys\Core\Model;
use Tethys\Databases\FilterErrorException;
use Tethys\Databases\Record;

class RecordFilter extends \Tethys\Databases\RecordFilter
{
    /**
     * @param int $limit
     * @param int $offset
     * @param string $keyField
     * @return Record[]
     * @throws FilterErrorException
     */
    public function fetch($limit = null, $offset = null, $keyField = null)
    {

        /** @var Record $class */
        $class = $this->objectClass;

        /** @var Connection $db */
        $db = $this->connection;

        /**
         * Формируем список запрашиваемых полей
         */
        $selectFields = [];
        foreach ($class::getFields() as $field=>$params) {
            $type = array_shift($params);
            switch ($type) {
                case(Model::FIELD_IP):
                    $selectFields[] = 'INET_NTOA(`t`.`'.$field.'`) `'.$field.'`';
                    break;
                case(Model::FIELD_GUID):
                    $selectFields[] = 'IF(`'.$field.'` IS NULL,NULL,LOWER(CONCAT(SUBSTR(HEX(`'.$field.'`),1,8),\'-\',SUBSTR(HEX(`'.$field.'`),9,4),\'-\''.
                        ',SUBSTR(HEX(`'.$field.'`),13,4),\'-\',SUBSTR(HEX(`'.$field.'`),17,4),\'-\',SUBSTR(HEX(`'.$field.'`),21,12)))) `'.$field.'`';
                    break;
                default:
                    $selectFields[] = '`t`.`'.$field.'`';
                    break;
            }
        }

        /**
         * Возможно, есть что добавить
         */
        foreach ($class::getAdditionalQueryFields() as $alias=>$query) {
            $selectFields[] = $query.' '.$alias;
        }

        /**
         * SELECT ... FROM ...
         */
        $sql = 'SELECT '.implode(', ', $selectFields).
            "\nFROM ".$class::getTable().' `t`';

        /**
         * WHERE ...
         */
        $where = $this->getWhere();

        if ($where) {
            $sql .= "\nWHERE (".implode(') AND (', $where).')';
        }

        /**
         * ORDER BY...
         */
        if ($this->orderBy) {
            $sql .= "\nORDER BY ".$this->orderBy;
        }

        /**
         * LIMIT
         */
        if ($limit) {
            $sql .= "\nLIMIT ";
            if ($offset) $sql .= $offset.', ';
            $sql .= $limit;
        }

        $sql .= ';';

        $result = [];
        foreach ($db->fetchAll($sql, $keyField) as $i=>$row) {
            $result[$i] = $this->entity($row);
        }

        return $result;
    }

    /**
     * @return int
     */
    public function count()
    {

        /** @var Record $class */
        $class = $this->objectClass;

        /** @var Connection $db */
        $db = $this->connection;

        /**
         * SELECT ... FROM ...
         */
        $sql = "SELECT COUNT(*)\nFROM ".$class::getTable();

        /**
         * WHERE ...
         */
        $where = $this->getWhere();

        if ($where) {
            $sql .= "\nWHERE (".implode(') AND (', $where).')';
        }

        return $db->fetchOne($sql) ?: 0;

    }

    protected function getWhere()
    {
        /** @var Record $class */
        $class = $this->objectClass;

        /** @var Connection $db */
        $db = $this->connection;

        $where = [];
        $fields = $class::getFields();
        foreach ($this->filterParams as $fieldName => $filterParams) {

            if (empty($fields[$fieldName])) {
                // такого поля нет в списке
                throw new FilterErrorException('Filter param ~'.$fieldName.'~ not exists in ~'.$class.'~ fields');
            }

            $field = $fields[$fieldName];
            $fieldType = array_shift($field);

            if (is_array($filterParams)) {

                $keyword = mb_strtolower($filterParams[0] ?? '');

                if (in_array($keyword, [ '>=', '<=', '>', '<', '!=', '<=>', '<>' ])) {

                    // Сравнение
                    if (!isset($filterParams[1])) throw new FilterErrorException('Expected filter param value for field ~'.$fieldName.'~');

                    $where[] = '`'.$fieldName.'`'.$keyword
                        .$db->escape(0 === $filterParams[1] ? '0' : ($filterParams[1] ?: ''));

                } elseif ('between' === $keyword) {

                    // BETWEEN

                    if (empty($filterParams[1]) || empty($filterParams[2])) {
                        throw new FilterErrorException('Expected filter param values for field ~'.$fieldName.'~');
                    }
                    $where[] = $fieldName.' BETWEEN '.$db->escape($filterParams[1] ?: '')
                        .' AMD '.$db->escape($filterParams[2] ?: '');

                } elseif ('like' === $keyword) {

                    // LIKE
                    if (!isset($filterParams[1])) throw new FilterErrorException('Expected filter param value for field ~'.$fieldName.'~');

                    $where[] = $fieldName.' LIKE '.$db->escape($filterParams[1] ?: '');

                } elseif (in_array($keyword, [ 'as_is', 'as-is', 'as is' ])) {

                    // "как есть"

                    if (empty($filterParams[1])) throw new FilterErrorException('Expected filter param value for field ~'.$fieldName.'~');
                    $where[] = $filterParams[1];

                } else {

                    // массив значений
                    $values = [];
                    foreach ($filterParams as $value) $values[] = $this->_make_filter_value($value, $fieldType);
                    $where[] = $fieldName.' IN ('.implode(',', $values).')';

                }

            } elseif (is_scalar($filterParams)) {

                if (in_array(mb_strtolower(trim($filterParams)), [ 'is_null', 'is-null', 'is null' ])) {

                    // нужен NULL
                    $where[] = '`'.$fieldName.'` IS NULL';

                } elseif (in_array(mb_strtolower(trim($filterParams)), [ 'is_not_null', 'is-not-null', 'is not null' ])) {

                    // нужен NOT NULL
                    $where[] = '`'.$fieldName.'` IS NOT NULL';

                } elseif (0 === $filterParams) {

                    // нужен '0'
                    $where[] = '`'.$fieldName.'`=\'0\'';

                } elseif ('' === $filterParams) {

                    // нужна пустая строка
                    $where[] = '`'.$fieldName.'`=\'\'';

                } else {

                    // просто какое-то значение
                    $where[] = '`'.$fieldName.'`='.$this->_make_filter_value($filterParams, $fieldType);

                }
            } elseif (null === $filterParams) {

                // нужен NULL
                $where[] = '`'.$fieldName.'` IS NULL';

            } else {
                throw new FilterErrorException('Bad filter param for field ~'.$fieldName.'~');
            }
        }
        return $where;
    }

    /**
     * @param string $value
     * @param string $fieldType
     * @return string
     */
    private function _make_filter_value($value, $fieldType)
    {
        switch ($fieldType) {
            case (Model::FIELD_GUID):
                return 'UNHEX('.$this->connection->escape( preg_replace('/[^\da-f]+/i', '', $value) ).')';
            case (Model::FIELD_IP):
                return 'INET_ATON('.$this->connection->escape($value).')';
            default:
                return $this->connection->escape($value);
        }
    }

}