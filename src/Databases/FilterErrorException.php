<?php

namespace Tethys\Databases;

class FilterErrorException extends DatabaseErrorException
{

    public function getTitle()
    {
        return 'Filter error';
    }

}