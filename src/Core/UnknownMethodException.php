<?php

namespace Tethys\Core;

class UnknownMethodException extends \BadMethodCallException implements ExceptionInterface
{

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Unknown Method';
    }

}
