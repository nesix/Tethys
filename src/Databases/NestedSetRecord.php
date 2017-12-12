<?php

namespace Tethys\Databases;

/**
 * @property int $parent_id
 * @property int $ns_level
 * @property int $ns_left
 * @property int $ns_right
 * @property NestedSetRecord $parent
 * @property NestedSetRecord[] $path
 * @property int $level
 * @property int $left
 * @property int $right
 */
abstract class NestedSetRecord extends Record
{

    /** @var static */
    private $_parent;

    /**
     * @return static
     */
    public function getParent()
    {
        if (null === $this->_parent) $this->_parent = static::getByID($this->parent_id);
        return $this->_parent;
    }

    /** @var static[] */
    private $_path;

    /**
     * Путь к объекту
     * @return Record[]|static[]
     */
    public function getPath()
    {
        if (null === $this->_path) {
            $this->_path = static::find([
                'ns_left' => [ '<', $this->ns_left ],
                'ns_right' => [ '>', $this->ns_right ],
            ], 'ns_left')->fetch();
        }
        return $this->_path;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return (int)$this->ns_level;
    }

    /**
     * @return int
     */
    public function getLeft()
    {
        return (int)$this->ns_left;
    }

    /**
     * @return int
     */
    public function getRight()
    {
        return (int)$this->ns_right;
    }

    /**
     * @return array
     */
    public static function getFields()
    {
        return array_merge(parent::getFields(), [
            'parent_id' => [ self::FIELD_INT ],
            'ns_level' => [ self::FIELD_INT ],
            'ns_left' => [ self::FIELD_INT ],
            'ns_right' => [ self::FIELD_INT ],
        ]);
    }

}