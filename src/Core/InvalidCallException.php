<?php

namespace Tethys\Core;

class InvalidCallException extends \BadMethodCallException implements ExceptionInterface
{

    public function getTitle()
    {
        return 'Invalid Call';
    }

}
