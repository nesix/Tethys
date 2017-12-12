<?php

namespace Tethys\Console\Controllers;

use Tethys\Console\Colors;
use Tethys\Core\Controller;

class HelpController extends Controller
{

    public function actionIndex()
    {
        return Colors::paint('Help page', Colors::GREEN).PHP_EOL;
    }

}