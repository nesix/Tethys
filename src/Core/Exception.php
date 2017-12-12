<?php

namespace Tethys\Core;

/**
 * Basic exception
 */
class Exception extends \Exception implements ExceptionInterface
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Exception';
    }
}
