<?php

namespace Tethys\Databases;

class QueryErrorException extends DatabaseErrorException
{

    public function getName()
    {
        return 'Query error';
    }

}