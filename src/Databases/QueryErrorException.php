<?php

namespace Tethys\Databases;

class QueryErrorException extends DatabaseErrorException
{

    public function getTitle()
    {
        return 'Query error';
    }

}