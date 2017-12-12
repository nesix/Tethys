<?php

namespace Tethys\Utils;

use Predis\Client;
use Tethys\Core\Component;

/**
 *
 * @property Client $server
 *
 */
class Redis extends Component implements \ArrayAccess
{

    public $connection;

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->getServer()->get($key);
    }


    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $expire
     * @return $this;
     */
    public function set($key, $value, $expire = null)
    {
        $this->getServer()->set($key, $value);
        if (null !== $expire) $this->getServer()->expire($key, $expire);
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function clear($key)
    {
        $this->getServer()->del(is_array($key)?$key:[$key]);
        return $this;
    }

    /**
     * @param string $pattern
     * @return string[]
     */
    public function keys($pattern)
    {
        return $this->getServer()->keys($pattern);
    }

    /**
     * @param string $key
     * @param string $field
     * @return string
     */
    public function getHash($key, $field)
    {
        return $this->getServer()->hget($key, $field);
    }

    /**
     * @param string $key
     * @return array
     */
    public function getHashAll($key)
    {
        return $this->getServer()->hgetall($key);
    }

    /**
     * @param string $key
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function setHash($key, $field, $value)
    {
        $this->getServer()->hset($key, $field, $value);
        return $this;
    }


    /**
     * @param string $key
     * @param string[] $values
     * @return $this
     */
    public function setHashAll($key, $values)
    {
        foreach ($values as $field=>$value) $this->getServer()->hset($key, $field, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param string|string[] $fields
     * @return $this
     */
    public function clearHash($key, $fields)
    {
        $this->getServer()->hdel($key, is_array($fields) ? $fields : [ $fields ]);
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function incr($key)
    {
        return $this->getServer()->incr($key);
    }

    public function transaction($key, callable $function)
    {
//        $pub = $this->getServer()->pubSubLoop();
//        $pub->subscribe("channel");
//        $pub->
//        $this->getServer()->watch($key);
//        call_user_func($function, $this);
//        $this->getServer()->exec();
    }

    private $_redis;

    /**
     * @return Client;
     */
    public function getServer()
    {
        if (null === $this->_redis) {
            $this->_redis = new Client($this->connection);
        }
        return $this->_redis;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getServer()->exists($offset);
    }

    /**
     * @param mixed $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param string $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->clear($offset);
    }
}