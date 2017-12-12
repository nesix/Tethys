<?php

namespace Tethys\Core;

use Traversable;

/**
 * Коллекция
 */
class Collection extends Component implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /** @var array */
    protected $_items;

    /**
     * @param mixed $offset
     * @return bool
     */
    public function has($offset)
    {
        return isset($this->_items[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function get($offset)
    {
        return $this->has($offset) ? $this->_items[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function set($offset, $value)
    {
        if (null === $this->_items) $this->_items = [];
        $this->_items[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function delete($offset)
    {
        if ($this->has($offset)) unset($this->_items[$offset]);
    }

    /**
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_items);
    }

    /**
     * @return Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_items);
    }

}