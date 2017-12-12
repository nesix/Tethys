<?php

namespace Tethys\Core;

class UnknownComponentClassException extends Exception
{

    public function getTitle()
    {
        return 'Unknoen component class';
    }

}