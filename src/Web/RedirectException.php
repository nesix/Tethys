<?php

namespace Tethys\Web;

use Tethys\Core\Exception;

class RedirectException extends Exception
{

    public $url;
    public $permanent;

    public function __construct($url = '', $permanent = false, $message = null, $code = 302, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->url = $url;
        $this->permanent = $permanent;
    }

}