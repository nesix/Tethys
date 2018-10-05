<?php

namespace Tethys\Databases;

class TransactionErrorException extends QueryErrorException
{

    public function getTitle()
    {
        return 'Transaction query error';
    }

}