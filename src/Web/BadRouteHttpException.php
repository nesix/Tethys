<?php
namespace Tethys\Web;

class BadRouteHttpException extends HttpException
{

    public function getTitle()
    {
        return 'Bad route';
    }

}