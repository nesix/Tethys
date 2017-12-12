<?php

namespace Tethys\Core;

/**
 * Basic component
 */
class Component extends BaseObject
{

    private $_events = [];

    /**
     * @param string $name
     * @return mixed
     * @throws PropertyException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        if (method_exists($this, 'set' . $name)) {
            throw new PropertyException('Getting write-only property: ' . $this::className() . '::' . $name);
        }

        throw new PropertyException('Getting unknown property: ' . $this::className() . '::' . $name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws PropertyException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;

        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }

        if (method_exists($this, 'get' . $name)) {
            throw new PropertyException('Setting read-only property: ' . $this::className() . '::' . $name);
        }

        throw new PropertyException('Setting unknown property: ' . $this::className() . '::' . $name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        return method_exists($this, $getter) && null !== $this->$getter();
    }

    /**
     * @param string $name
     * @throws PropertyException
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
            return;
        }
        throw new PropertyException('Unsetting an unknown or read-only property: ' . $this::className() . '::' . $name);
    }

    /**
     * Add event listener
     * @param string $name
     * @param callable $handler
     * @param bool $append
     * @return $this
     */
    public function on($name, $handler, $append = true)
    {
        if ($append || empty($this->_events[$name])) {
            $this->_events[$name][] = $handler;
        } else {
            array_unshift($this->_events[$name], $handler);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param callable|null $handler
     * @return $this
     */
    public function off($name, $handler = null)
    {
        if (!empty($this->_events[$name])) {

            if (null === $handler) {

                unset($this->_events[$name]);

            } else {

                $removed = false;

                foreach ($this->_events[$name] as $i => $eventHandler) {
                    if ($eventHandler === $handler) {
                        unset($this->_events[$name][$i]);
                        $removed = true;
                    }
                }

                if ($removed) {
                    $this->_events[$name] = array_values($this->_events[$name]);
                }

            }

        }
        return $this;
    }

    /**
     * @param string $name
     * @param Event|null $event
     * @param array $data
     */
    public function trigger($name, Event $event = null, ...$data)
    {
        if (!empty($this->_events[$name])) {
            if (null === $event) $event = Event::make();
            if (null === $event->sender) $event->sender = $this;
            $event->name = $name;
            $event->prevented = false;
            if (empty($data)) $data = [];
            array_unshift($data, $event);
            foreach ($this->_events[$name] as $handler) {
                call_user_func_array($handler, $data);
                if ($event->prevented) return;
            }
        }
    }

}