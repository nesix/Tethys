<?php

namespace Tethys\Databases\Mysql;

use Tethys\Core\Model;
use Tethys\Databases\Record;

class Connection extends \Tethys\Databases\Connection
{

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $port;

    /**
     * @var string
     */
    public $charset;

    /**
     * @return string
     */
    public function getConnectionString() {

        $params = [
            'host' => $this->host,
            'dbname' => $this->db,
        ];
        if ($this->port) $params['port'] = $this->port;
        if ($this->charset) $params['charset'] = $this->charset;

        $string = [];
        foreach ($params as $f=>$v) $string[] = $f.'='.$v;

        return 'mysql:'.implode(';', $string);
    }

    /**
     * @return string|RecordFilter
     */
    public function getFilterClass()
    {
        return 'Tethys\Databases\Mysql\RecordFilter';
    }

    /**
     * @param Record $record
     * @throws \Exception
     */
    public function saveObject(Record $record)
    {
        /** @var Record $className */
        $className = $record::className();

        $fields = $className::getFields();
        $data = [];
        foreach ($record->getModifiedData() as $field=>$value) {

            if (!isset($fields[$field])) throw new \Exception;

            if (null === $value) {
                $data[$field] = 'NULL';
                continue;
            }

            $fieldType = array_shift($fields[$field]);

            switch ($fieldType) {

                case(Model::FIELD_INT):
                    $data[$field] = (int)$value;
                    break;

                case(Model::FIELD_FLOAT):
                    $data[$field] = (float)$value;
                    break;

                case(Model::FIELD_TIME):
                    $data[$field] = $this->escape(date('H:i:s', strtotime($value)));
                    break;

                case(Model::FIELD_DATE):
                    $data[$field] = $this->escape(date('Y-m-d', strtotime($value)));
                    break;

                case(Model::FIELD_DATETIME):
                    $data[$field] = $this->escape(date('Y-m-d H:i:s', strtotime($value)));
                    break;

                case(Model::FIELD_BOOL):
                    $data[$field] = $value ? 1 : 0;
                    break;

                case(Model::FIELD_IP):
                    $data[$field] = 'INET_ATON('.$this->escape($value).')';
                    break;

                case(Model::FIELD_GUID):
                    $data[$field] = $value;
                    $data[$field] = 'UNHEX(\''.preg_replace('/([^\da-f]+)/iu', '', $value).'\')';
                    break;

                default:
                    $data[$field] = $this->escape($value);
                    break;

            }
        }

        if ($data) {
            if ($record->getId()) {

                $updates = [];
                foreach ($data as $field=>$value) {
                    $updates[] = "`{$field}`={$value}";
//                    $sql .= "\n  `{$field}`={$value}";
                }
                if ($updates) {

                    $sql = 'UPDATE `'.$record::getTable()."` SET\n"
                        .'  '.implode(",\n  ", $updates)
                        ."\nWHERE `id`=".$record->getId().' LIMIT 1;';

//                    \Tethys::log()->writeToFile('sql', $sql);

                    try {

                        $record->trigger(Record::EVENT_BEFORE_UPDATE);

                        $this->query($sql);
                        $record->clearModifiedFields();

                        $record->trigger(Record::EVENT_AFTER_UPDATE);

                    } catch (\Exception $e) {

                    }

                }

            } else {

                $sql = 'INSERT INTO `'.$record::getTable().'` (`'
                    .implode('`, `', array_keys($data))."`)\nVALUES(".implode(', ', $data).");";

//                \Tethys::log()->writeToFile('sql', $sql);

                try {

                    $record->trigger(Record::EVENT_BEFORE_INSERT);

                    $new_id = $this->insert($sql);
                    $record->setId($new_id);
                    $record->clearModifiedFields();

                    $record->trigger(Record::EVENT_AFTER_INSERT);

                } catch (\Exception $e) {

                }


            }

        }
    }

    /**
     * @param Record $record
     */
    public function deleteObject(Record $record)
    {
        try {
            $id = $record->getId();
            if ($id) {
                $sql = 'DELETE FROM '.$record::getTable().' WHERE id='.$this->escape($id).' LIMIT 1;';
                $this->dbh()->query($sql);
            }
        } catch (\Throwable $t) {
        }
    }

}