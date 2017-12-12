<?php

namespace Tethys\Core;

class ModelErrorException extends Exception
{

    public function getTitle()
    {
        return 'Model error';
    }

}