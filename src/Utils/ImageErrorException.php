<?php

namespace Tethys\Utils;

use Tethys\Core\Exception;

class ImageErrorException extends Exception
{

    public function getTitle()
    {
        return 'Image error';
    }

}