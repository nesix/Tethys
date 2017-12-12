<?php

namespace Tethys\Console;

use Tethys\Core\BaseObject;

class Colors extends BaseObject
{

    const LIGHT         = 0x0100;

    const UNDERLINE     = 0x0200;

    const BLACK         = 0x0001;
    const DARK_GRAY     = 0x0101;
    const GRAY          = 0x0002;
    const LIGHT_GRAY    = 0x0102;
    const WHITE         = 0x0003;
    const EXTRA_WHITE   = 0x0103;
    const BROWN         = 0x0004;
    const YELLOW        = 0x0104;
    const CYAN          = 0x0008;
    const LIGHT_CYAN    = 0x0108;
    const RED           = 0x0010;
    const LIGHT_RED     = 0x0110;
    const GREEN         = 0x0020;
    const LIGHT_GREEN   = 0x0120;
    const BLUE          = 0x0040;
    const LIGHT_BLUE    = 0x0140;
    const PURPLE        = 0x0080;
    const LIGHT_PURPLE  = 0x0180;

    const BG_BLACK      = 0x1000;
    const BG_RED        = 0x2000;
    const BG_GREEN      = 0x3000;
    const BG_YELLOW     = 0x4000;
    const BG_BLUE       = 0x5000;
    const BG_PURPLE     = 0x6000;
    const BG_CYAN       = 0x7000;
    const BG_GRAY       = 0x8000;

    private static $colors = [

        self::BLACK         => '30',
        self::DARK_GRAY     => '1;30',
        self::RED           => '31',
        self::LIGHT_RED     => '1;31',
        self::GREEN         => '32',
        self::LIGHT_GREEN   => '1;32',
        self::BROWN         => '33',
        self::YELLOW        => '1;33',
        self::BLUE          => '34',
        self::LIGHT_BLUE    => '1;34',
        self::PURPLE        => '35',
        self::LIGHT_PURPLE  => '1;35',
        self::CYAN          => '36',
        self::LIGHT_CYAN    => '1;36',
        self::GRAY          => '37',
        self::LIGHT_GRAY    => '1;37',
        self::WHITE         => '38',
        self::EXTRA_WHITE   => '1;38',

        self::BG_BLACK      => '40',
        self::BG_RED        => '41',
        self::BG_GREEN      => '42',
        self::BG_YELLOW     => '43',
        self::BG_BLUE       => '44',
        self::BG_PURPLE     => '45',
        self::BG_CYAN       => '46',
        self::BG_GRAY       => '47',

    ];

    /**
     * Returns colored string
     *
     * @param string $string
     * @param int $color
     * @return string
     */
    public static function paint($string, $color = 0x0000)
    {
        $bgColor = self::$colors[0xf000 & $color] ?? '';
        $fgColor = self::$colors[0x01ff & $color] ?? '';

        $colored_string = '';

        if ($color & self::UNDERLINE) $colored_string .= "\e[4m";

        if ($fgColor) $colored_string .= "\e[" . $fgColor . "m";
        if ($bgColor) $colored_string .= "\e[" . $bgColor . "m";

        return $colored_string
            ? ($colored_string . $string . "\e[0m")
            : $string;
    }

}