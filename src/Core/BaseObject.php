<?php

namespace Tethys\Core;

/**
 * Basic object
 */
class BaseObject
{

    /**
     * @param array $row
     */
    public function __construct(array $row = [])
    {
        $this->obtain($row);
        $this->init();
    }

    /**
     * @param array $row
     * @return $this
     */
    public function obtain(array $row)
    {
        if (null !== $row && is_array($row)) {
            foreach ($row as $field=>$value) {
                $this->$field = $value;
            }
        }
        return $this;
    }

    /**
     * Init object
     */
    public function init()
    {
    }

    /**
     * Get current class name
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * @param array $row
     * @return static
     */
    public static function make(array $row = [])
    {
        return new static($row);
    }
}