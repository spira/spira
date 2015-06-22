<?php namespace App\Exceptions;

/**
 * Exceptions fix added care of
 * https://laracasts.com/discuss/channels/lumen/lumen-debug-mode-not-showing-stack-trace
 *
 * @todo Remove this file when the issue at https://github.com/laravel/framework/issues/8744 is fixed.
 *
 */

use ErrorException;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;

class HandleExceptionsFix {


    protected $app;

    protected static $fatalError = null;

    public function bootstrap(Application $app)
    {
        $this->app = $app;

        error_reporting(-1);

        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

        ini_set('display_errors', 'Off');
    }

    public function handleError($level, $message, $file = '', $line = 0, $context = array())
    {

        // **** Add this, loads fatal error
        if ($level & (1 << 24)) {
            self::$fatalError = array(
                'message' => $message,
                'type' => $level,
                'file' => $file,
                'line' => $line
            );
        }

        if (error_reporting() & $level)
        {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    public function handleException($e)
    {
        $this->getExceptionHandler()->report($e);

        if ($this->app->runningInConsole())
        {
            $this->renderForConsole($e);
        }
        else
        {
            $this->renderHttpResponse($e);
        }
    }

    protected function renderForConsole($e)
    {
        $this->getExceptionHandler()->renderForConsole(new ConsoleOutput, $e);
    }

    protected function renderHttpResponse($e)
    {
        $this->getExceptionHandler()->render($this->app['request'], $e)->send();
    }

    // Updated this to check for the fatal error
    public function handleShutdown()
    {
        $error = error_get_last();

        if(self::$fatalError){
            $error = self::$fatalError;
        }

        if ( ! is_null($error) && $this->isFatal($error['type']))
        {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }


    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }


    protected function isFatal($type)
    {
        // *** Add type 16777217 that HVVM returns for fatal
        return in_array($type, [16777217, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }


    protected function getExceptionHandler()
    {
        return new \App\Exceptions\Handler();   // <-- call our app's handler
    }

}