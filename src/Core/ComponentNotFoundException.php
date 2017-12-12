<?php

namespace Tethys\Core;

class ComponentNotFoundException extends Exception
{

    public function getTitle()
    {
        return 'Component not found';
    }

}