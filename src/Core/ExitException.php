<?php

namespace Tethys\Core;

use Throwable;

class ExitException extends Exception
{

    public $exitStatus;

    public function __construct($exitStatus = 0, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->exitStatus = $exitStatus;
        parent::__construct($message, $code, $previous);
    }

    public function getTitle()
    {
        return 'Exit Exception';
    }

}