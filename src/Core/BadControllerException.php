<?php

namespace Tethys\Core;

class BadControllerException extends Exception
{
    public function getTitle()
    {
        return 'Bad controller';
    }
}