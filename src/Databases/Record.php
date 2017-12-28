<?php

namespace Tethys\Databases;

use Tethys\Core\Exception;
use Tethys\Core\Model;

/**
 * @property int $id
 * @property array $data
 * @property array $modifiedData
 */
abstract class Record extends Model implements \IteratorAggregate, \ArrayAccess
{

    const EVENT_BEFORE_SAVE = 'record_before_save';
    const EVENT_BEFORE_UPDATE = 'record_before_update';
    const EVENT_BEFORE_INSERT = 'record_before_insert';
    const EVENT_BEFORE_DELETE = 'record_before_delete';

    const EVENT_AFTER_SAVE = 'record_after_save';
    const EVENT_AFTER_UPDATE = 'record_after_update';
    const EVENT_AFTER_INSERT = 'record_after_insert';
    const EVENT_AFTER_DELETE = 'record_after_delete';

    const EVENT_CHANGE_FIELD_VALUE = 'change_field_value';

    /** @var array */
    protected $rowData;

    /** @var string[] */
    protected $modifiedField = [];

    /** @var int */
    private $_initial_id;

    /**
     * Record constructor.
     * @param $row
     */
    public function __construct($row)
    {
        $this->rowData = $row;
        $this->_initial_id = $row['id'] ?? null;
        parent::__construct([]);
    }

    /**
     * @return string
     */
    abstract public static function getTable();

    /**
     * @return array
     */
    public static function getAdditionalQueryFields()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->rowData ?: [];
    }

    /**
     * @return array
     */
    public function getModifiedData()
    {
        $ret = [];
        foreach ($this->modifiedField as $field) {
            $ret[$field] = $this->rowData[$field];
        }
        return $ret;
    }

    public function clearModifiedFields()
    {
        $this->modifiedField = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_initial_id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId($id)
    {
        $this->rowData['id'] = $this->_initial_id = $id;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function has($field)
    {
        return isset(static::getFields()[$field]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->rowData[$name] ?? null;
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        if ($this->has($name)) {
            $currentValue = $this->rowData[$name] ?? null;
            if ($currentValue !== $value) {
                $this->rowData[$name] = $value;
                if (!in_array($name, $this->modifiedField)) $this->modifiedField[] = $name;
                $this->trigger(self::EVENT_CHANGE_FIELD_VALUE, null,
                $name,
                    null === $currentValue ? 'null' : $currentValue,
                    null === $value ? 'null' : $value
                );
            }
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     *
     */
    public function save()
    {
        $this->trigger(self::EVENT_BEFORE_SAVE);
        static::getConnection(true)->saveObject($this);
        $this->trigger(self::EVENT_AFTER_SAVE);
    }

    /**
     *
     */
    public function delete()
    {
        $this->trigger(self::EVENT_BEFORE_DELETE);
        static::getConnection(true)->deleteObject($this);
        $this->trigger(self::EVENT_AFTER_DELETE);
    }

    /**
     * @return array
     */
    public static function getFields()
    {
        return [
            'id' => [ Model::FIELD_ID ]
        ];
    }

    /**
     * @param int $id
     * @return static
     */
    public static function getByID($id)
    {
        return $id ? static::find([ 'id' => $id ])->fetchOne() : null;
    }

    /**
     * @param array $filterParams
     * @param string $orderBy
     * @return RecordFilter
     * @throws Exception
     */
    public static function find(array $filterParams = [], $orderBy = '')
    {
        return static::getConnection()->findObject(static::className(), $filterParams, $orderBy);
    }

    /**
     * @param bool $modify собираемся ли менять данные
     * @return RecordQueriesInterface
     */
    public static function getConnection($modify = false)
    {
        return \Tethys::db();
    }

    /**
     * Проверяет, существует ли указанное поле
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__set($offset, null);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->rowData);
    }

}