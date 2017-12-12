<?php

namespace Tethys\Console;

use Tethys\Core\Exception;

class ErrorHandler extends \Tethys\Core\ErrorHandler
{

    /**
     * @param Exception $exception
     */
    protected function renderException($exception)
    {
        echo PHP_EOL;
        echo Colors::paint($exception->getTitle(), Colors::UNDERLINE + Colors::LIGHT_RED).PHP_EOL;

        foreach (preg_split('/(~.*?~)/', $exception->getMessage(), 0, PREG_SPLIT_DELIM_CAPTURE) as $string) {

            if (preg_match('/^~(.+)~$/', $string, $matches)) {
                echo Colors::paint($matches[1], Colors::LIGHT_RED);
            } else {
                echo Colors::paint($string, Colors::RED);
            }

        }

        echo PHP_EOL.PHP_EOL;

    }
}