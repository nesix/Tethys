<?php

namespace Tethys\Web;


/**
 * Class HttpNotModifiedException
 * @package Tethys\Web
 */
class HttpNotModifiedException extends HttpException
{

    public function getTitle()
    {
        return 'Http not modified';
    }

}