<?php

namespace Tethys\Web;

class HttpForbiddenException extends HttpException
{

    public function getTitle()
    {
        return 'Forbidden';
    }

}