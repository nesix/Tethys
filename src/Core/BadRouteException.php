<?php

namespace Tethys\Core;

class BadRouteException extends Exception
{

    public function getTitle()
    {
        return 'Bad route';
    }

}