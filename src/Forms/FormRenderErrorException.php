<?php

namespace Tethys\Forms;

use Tethys\Core\Exception;

class FormRenderErrorException extends Exception
{

    public function getName()
    {
        return 'Render form error';
    }

}