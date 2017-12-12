<?php

namespace Tethys\Core;

class ModelFieldErrorException extends ModelErrorException
{

    public function getTitle()
    {
        return 'Model field error';
    }

}