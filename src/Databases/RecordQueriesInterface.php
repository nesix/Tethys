<?php

namespace Tethys\Databases;

interface RecordQueriesInterface
{

    /**
     * @param string|Record $class
     * @param array $filterParams
     * @param string|null $orderBy
     * @return RecordFilter
     */
    public function findObject($class, array $filterParams = [], $orderBy = null);

    /**
     * @param Record $record
     */
    public function saveObject(Record $record);

    /**
     * @param Record $record
     */
    public function deleteObject(Record $record);

}