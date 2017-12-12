<?php

namespace Tethys\Core;

abstract class ErrorHandler extends Component
{

    /**
     * @var \Exception
     */
    public $exception;

    /**
     * @var int the size of the reserved memory. A portion of memory is pre-allocated so that
     * when an out-of-memory issue occurs, the error handler is able to handle the error with
     * the help of this reserved memory. If you set this value to be 0, no memory will be reserved.
     * Defaults to 256KB.
     */
    public $memoryReserveSize = 262144;

    /**
     * @var string Used to reserve memory for fatal error handler.
     */
    private $_memoryReserve;

    /**
     * @var \Exception from HHVM error that stores backtrace
     */
    private $_hhvmException;

    /**
     *
     */
    public function register()
    {

        ini_set('display_errors', false);

        set_exception_handler([$this, 'handleException']);

        if (defined('HHVM_VERSION')) {
            set_error_handler([$this, 'handleHhvmError']);
        } else {
            set_error_handler([$this, 'handleError']);
        }
        if ($this->memoryReserveSize > 0) {
            $this->_memoryReserve = str_repeat('x', $this->memoryReserveSize);
        }

        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError']);

    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * @param \Exception $exception
     */
    public function handleException($exception)
    {

        if ($exception instanceof ExitException) {
            return;
        }

        $this->exception = $exception;

        $this->unregister();

        if (PHP_SAPI !== 'cli') {
            http_response_code(500);
        }

        try {
//            $this->logException($exception);
//            if ($this->discardExistingOutput) {
//                $this->clearOutput();
//            }
            $this->renderException($exception);
//            if (!YII_ENV_TEST) {
//                \Yii::getLogger()->flush(true);
//                if (defined('HHVM_VERSION')) {
//                    flush();
//                }
//                exit(1);
//            }
        } catch (\Exception $e) {
            // an other exception could be thrown while displaying the exception
            $this->handleFallbackExceptionMessage($e, $exception);
        } catch (\Throwable $e) {
            // additional check for \Throwable introduced in PHP 7
            $this->handleFallbackExceptionMessage($e, $exception);
        }

        $this->exception = null;

    }

    protected function handleFallbackExceptionMessage($exception, $previousException) {
//        $msg = "An Error occurred while handling another error:\n";
//        $msg .= (string) $exception;
//        $msg .= "\nPrevious exception:\n";
//        $msg .= (string) $previousException;
//        if (0) { // YII_DEBUG) {
//            if (PHP_SAPI === 'cli') {
//                echo $msg . "\n";
//            } else {
//                echo '<pre>' . htmlspecialchars($msg, ENT_QUOTES, Yii::$app->charset) . '</pre>';
//            }
//        } else {
//            echo 'An internal server error occurred.';
//        }
//        $msg .= "\n\$_SERVER = " . VarDumper::export($_SERVER);
//        error_log($msg);
//        if (defined('HHVM_VERSION')) {
//            flush();
//        }
//        exit(1);
    }

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @param $context
     * @param $backtrace
     * @return bool
     */
    public function handleHhvmError($code, $message, $file, $line, $context, $backtrace)
    {
        if ($this->handleError($code, $message, $file, $line)) {
            return true;
        }
        if (E_ERROR & $code) {
            $exception = new ErrorException($message, $code, $code, $file, $line);
            $ref = new \ReflectionProperty('\Exception', 'trace');
            $ref->setAccessible(true);
            $ref->setValue($exception, $backtrace);
            $this->_hhvmException = $exception;
        }
        return false;
    }

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     */
    public function handleError($code, $message, $file, $line)
    {
        echo 'Error: '.$code.' '.$message.' '.$file.' '.$line;
    }

    /**
     *
     */
    public function handleFatalError()
    {
        \Tethys::log()->prr(error_get_last());
    }

    abstract protected function renderException($exception);

}