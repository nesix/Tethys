<?php

namespace Tethys\Web;

use Tethys\Core\Exception;
use Throwable;

class HttpException extends Exception
{

    public static $messages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
    ];

    public function __construct($message = '', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpHeader()
    {
        $message = self::$messages[$this->getCode()] ?? '';
        if ($message) {
            /** @var Request $request */
            $request = \Tethys::request();
            return $request->protocol.' '.$this->getCode().' '.$message;
        }
        return '';
    }

    public function getTitle()
    {
        return 'Http error';
    }

}