<?php

namespace Tethys\Utils;

use Tethys\Core\BaseObject;

class Html extends BaseObject
{

    private static $_emt;

    /**
     * @param string $text
     * @param array $params
     * @return string
     */
    public static function typography(string $text, array $params = [])
    {
        if (null === self::$_emt) {
            require_once __DIR__.'/Libs/EMT.php';
            self::$_emt = new \EMTypograph();
        }
        self::$_emt->setup(array_merge(static::getTypographySettings(), $params));
        self::$_emt->set_text($text);
        return self::$_emt->apply();
    }

    public static function getTypographySettings()
    {
        return [
            'Text.paragraphs'=>'off',
            'OptAlign.oa_oquote'=>'off',
            'Etc.split_number_to_triads' => 'off',
            'Text.breakline' => 'off',
            'Text.auto_links' => 'off',
        ];
    }

}