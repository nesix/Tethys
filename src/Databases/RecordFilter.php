<?php

namespace Tethys\Databases;

use Tethys\Core\BaseObject;

abstract class RecordFilter extends BaseObject
{

    /**
     * @var Connection
     */
    public $connection;

    /**
     * @var Record|string
     */
    public $objectClass;

    /**
     * @var array
     */
    public $filterParams;

    /**
     * @var string
     */
    public $orderBy;

    /**
     * @param int $limit
     * @param int $offset
     * @param string $keyField
     * @return Record[]
     */
    abstract public function fetch($limit = null, $offset = null, $keyField = null);

    /**
     * @return int
     */
    abstract public function count();

    /**
     * @return Record
     */
    public function fetchOne()
    {
        $result = $this->fetch(1);
        return $result ? array_shift($result) : null;
    }

    /**
     * @param array $row
     * @return Record
     */
    public function entity(array $row)
    {
        return $this->objectClass::make($row);
    }

}