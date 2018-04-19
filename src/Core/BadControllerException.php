<?php

namespace Tethys\Core;


/**
 * Class BadControllerException
 * @package Tethys\Core
 *
 * Ошибка контроллера
 *
 */
class BadControllerException extends Exception
{

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Bad controller';
    }

}