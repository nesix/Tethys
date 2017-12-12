<?php

namespace Tethys\Core;

/**
 * Property exception
 */
class PropertyException extends Exception
{
    public function getTitle()
    {
        return 'Unknown property';
    }
}
