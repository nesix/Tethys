<?php

namespace Tethys\Web;

use Tethys\Core\BaseObject;
use Tethys\Core\InvalidCallException;

class CookieCollection extends BaseObject implements \IteratorAggregate, \ArrayAccess, \Countable
{

    public $readOnly = false;

    /** @var Cookie[] */
    private $_cookies;

    /**
     * @param Cookie $cookie
     */
    public function add(Cookie $cookie)
    {
        if ($this->readOnly) {
            throw new InvalidCallException('The cookie collection is read only.');
        }
        $this->_cookies[$cookie->name] = $cookie;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->_cookies[$name]) && $this->_cookies[$name]->value !== ''
            && ($this->_cookies[$name]->expire === null || $this->_cookies[$name]->expire >= time());
    }

    /**
     * @param string $name
     * @return Cookie
     */
    public function get($name)
    {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
    }

    public function getValue($name, $defaultValue = null)
    {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name]->value : $defaultValue;
    }

    /**
     * @param Cookie|string $cookie
     * @param bool $removeFromBrowser
     */
    public function remove($cookie, $removeFromBrowser = true)
    {
        if ($this->readOnly) {
            throw new InvalidCallException('The cookie collection is read only.');
        }
        if ($cookie instanceof Cookie) {
            $cookie->expire = 1;
            $cookie->value = '';
        } else {
            $cookie = new Cookie([
                'name' => $cookie,
                'expire' => 1,
            ]);
        }
        if ($removeFromBrowser) {
            $this->_cookies[$cookie->name] = $cookie;
        } else {
            unset($this->_cookies[$cookie->name]);
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_cookies);
    }

    /**
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->_cookies);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     * @return Cookie
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param string $offset
     * @param mixed $cookie
     */
    public function offsetSet($offset, $cookie)
    {
        $this->add($cookie);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @param Cookie[] $cookies
     * @param array $params
     * @return static
     */
    public static function create($cookies, $params = [])
    {
        $collection = static::make($params);
        $collection->_cookies = $cookies;
        return $collection;
    }

}