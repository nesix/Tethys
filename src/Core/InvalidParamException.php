<?php

namespace Tethys\Core;

class InvalidParamException extends \BadMethodCallException implements ExceptionInterface
{

    public function getTitle()
    {
        return 'Invalid Parameter';
    }
    
}