--TEST--

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

$errorHandler = ErrorHandler::registerOnceFatalErrorHandler();
$errorHandler->addFatalErrorHandlerListener(static function (): void {
    echo 'Fatal error listener called' . PHP_EOL;
});

$errorHandler = ErrorHandler::registerOnceExceptionHandler();
$errorHandler->addExceptionHandlerListener(static function (): void {
    echo 'Exception listener called' . PHP_EOL;
});

$previousExceptionHandler = set_exception_handler(static function (\Throwable $exception) use (&$previousExceptionHandler): void {
    echo 'Custom exception handler called' . PHP_EOL;

    $previousExceptionHandler($exception);

    throw new \Exception('foo bar baz');
});

throw new \Exception('foo bar');
?>
--EXPECTF--
Custom exception handler called
Exception listener called
Fatal error: Uncaught Exception: foo bar in %s:%d
Stack trace:
%a

Fatal error: Uncaught Exception: foo bar baz in %s:%d
Stack trace:
%a
Fatal error listener called
