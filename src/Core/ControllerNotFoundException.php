<?php

namespace Tethys\Core;

class ControllerNotFoundException extends Exception
{

    public function getTitle()
    {
        return 'Controller not found';
    }

}