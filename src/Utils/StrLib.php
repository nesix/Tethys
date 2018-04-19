<?php

namespace Tethys\Utils;

/**
 * Class StrLib
 * @package Tethys\Utils
 *
 * Строковые функции
 *
 */
class StrLib
{

    /**
     * @param string|int $time
     * @return false|string
     */
    public static function gmtDate($time)
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return $time ? gmdate('D, d M Y H:i:s \G\M\T', $time) : '';
    }


    public static function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }

    public static function byteSubstr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length === null ? mb_strlen($string, '8bit') : $length, '8bit');
    }

    public static function base64UrlEncode($input)
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    public static function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

}