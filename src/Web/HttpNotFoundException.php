<?php

namespace Tethys\Web;

class HttpNotFoundException extends HttpException
{

    public function getTitle()
    {
        return 'Not found';
    }

}