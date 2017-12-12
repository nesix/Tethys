<?php

namespace Tethys\Console;

use Tethys\Core\Exception;

class BadRouteRuleException extends Exception
{

    public function getTitle()
    {
        return 'Bad route rule';
    }

}