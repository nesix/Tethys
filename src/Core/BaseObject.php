<?php

namespace Tethys\Core;


/**
 * Class BaseObject
 * @package Tethys\Core
 *
 * Базовый объект
 *
 */
class BaseObject
{

    /**
     * BaseObject constructor.
     * @param array $row
     */
    public function __construct(array $row = [])
    {
        $this->obtain($row);
        $this->init();
    }

    /**
     * заполнение свойств объекта
     *
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
     * Инициализация объекта
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Возвращает имя класса вызвавшего функцию
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Создает объект класса вызвавшего функцию
     *
     * @param array $row
     * @return static
     */
    public static function make(array $row = [])
    {
        return new static($row);
    }

}