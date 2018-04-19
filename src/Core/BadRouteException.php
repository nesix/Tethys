<?php

namespace Tethys\Core;


/**
 * Class BadRouteException
 * @package Tethys\Core
 *
 * Ошибка маршрутизации
 *
 */
class BadRouteException extends Exception
{

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Bad route';
    }

}