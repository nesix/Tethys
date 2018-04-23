<?php

namespace Tethys\Utils;


/**
 * Class Json
 * @package Tethys\Utils
 *
 * Обертки для работы с Json
 *
 */
class Json
{

    /**
     * @param array|object $data
     * @param int $options
     * @return string
     */
    public static function encode($data, $options = JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT)
    {
        return json_encode($data, $options);
    }

    /**
     * @param string $json
     * @param bool $assoc
     * @return mixed
     */
    public static function decode($json, $assoc = true)
    {
        return json_decode($json, $assoc);
    }

    /**
     * @param \stdClass|object $stdClassObject
     * @return mixed
     */
    public static function asArray($stdClassObject)
    {
        return json_decode(json_encode($stdClassObject), true);
    }

}