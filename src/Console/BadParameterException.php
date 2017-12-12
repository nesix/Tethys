<?php

namespace Tethys\Console;

use Tethys\Core\Exception;

class BadParameterException extends Exception
{

    public function getTitle() {
        return 'Bad parameter';
    }

}