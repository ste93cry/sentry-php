--TEST--
Test catching exceptions
--FILE--
<?php

declare(strict_types=1);

namespace Sentry\Tests;

use Sentry\ErrorHandler;

$vendor = __DIR__;

while (!file_exists($vendor . '/vendor')) {
    $vendor = dirname($vendor);
}

require $vendor . '/vendor/autoload.php';

ErrorHandler::addExceptionListener(static function (\Throwable $error): void {
    echo 'Sentry exception listener called: ' . $error->getMessage() . PHP_EOL;
});

ErrorHandler::addErrorListener(static function (\Throwable $error): void {
    echo 'Sentry error listener called: ' . $error->getMessage() . PHP_EOL;
});

ErrorHandler::addFatalErrorListener(static function (\Throwable $error): void {
    echo 'Sentry fatal error listener called: ' . $error->getMessage() . PHP_EOL;
});

$otherHandler = new class() {
    /** @var callable */
    public $previousHandler;

    public function handle(\Throwable $throwable): void
    {
        echo 'Custom exception handler called' . PHP_EOL;
        $exceptionFromInside = null;

        if ($this->previousHandler) {
            try {
                echo 'Calling Sentry exception handler...' . PHP_EOL;
                ($this->previousHandler)($throwable);
            } catch (\Throwable $exceptionFromInside) {
                echo 'Sentry handler rethrowed' . PHP_EOL;
            }
        }

        echo 'Throwing a new exception from the custom exception handler' . PHP_EOL;
        throw new \RuntimeException('Secondary exception thrown');

        // this will never be reached, but it's how it should be handled
        if ($exceptionFromInside) {
            throw $exceptionFromInside;
        }
    }
};

$otherHandler->previousHandler = set_exception_handler([$otherHandler, 'handle']);

throw new \Exception('foo bar');
?>
--EXPECTF--
Sentry error listener called: User Deprecated%s
Custom exception handler called
Calling Sentry exception handler...
Sentry exception listener called: foo bar
Sentry handler rethrowed
Throwing a new exception from the custom exception handler
Sentry fatal error listener called: Secondary exception thrown

Fatal error: Uncaught RuntimeException: Secondary exception thrown in Standard input code:%d
Stack trace:
#0 [internal function]: class@anonymous->handle(Object(Exception))
#1 {main}
  thrown in Standard input code on line %d
