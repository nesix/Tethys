<?php

namespace Tethys\Core;

class UnknownClassException extends Exception
{

    public function getTitle()
    {
        return 'Unknown class';
    }

}
