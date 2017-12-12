<?php

namespace Tethys\Databases;

use Tethys\Core\Exception;

class DatabaseErrorException extends Exception
{

    public function getTitle()
    {
        return 'Database error';
    }

}