<?php

namespace Tethys\Databases;

class InsertQueryErrorException extends QueryErrorException
{

    public function getTitle()
    {
        return 'Insert query error';
    }

}