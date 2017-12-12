<?php

class Tethys
{

    /**
     * @var \Tethys\Core\Application
     */
    public static $app;

    /**
     * @return \Tethys\Core\Component|\Tethys\Databases\Connection
     */
    public static function db() {
        return self::$app->get('db');
    }

    /**
     * @return \Tethys\Core\Component|\Tethys\Core\Logging
     */
    public static function log() {
        return self::$app->get('log');
    }

    /**
     * @return \Tethys\Core\Component|\Tethys\Console\Request|\Tethys\Web\Request
     */
    public static function request() {
        return self::$app->get('request');
    }

    /**
     * @return \Tethys\Core\Component|\Tethys\Console\Response|\Tethys\Web\Response
     */
    public static function response() {
        return self::$app->get('response');
    }

    /**
     * @return \Tethys\Core\Component|\Tethys\Core\View
     */
    public static function view() {
        return self::$app->get('view');
    }

    /**
     * @return \Tethys\Core\Component|\Tethys\Core\Security
     */
    public static function security() {
        return self::$app->get('security');
    }

}