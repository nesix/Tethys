<?php

namespace Tethys\Core;

class InvalidConfigException extends Exception
{

    public function getTitle()
    {
        return 'Invalid config';
    }

}