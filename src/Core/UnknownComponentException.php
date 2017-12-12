<?php

namespace Tethys\Core;

class UnknownComponentException extends Exception
{

    public function getTitle()
    {
        return 'Unknown component';
    }

}