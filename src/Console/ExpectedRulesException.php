<?php

namespace Tethys\Console;

use Tethys\Core\Exception;

class ExpectedRulesException extends Exception
{

    /**
     * @return string
     */
    public function getTitle() {
        return 'Expected rules';
    }

}