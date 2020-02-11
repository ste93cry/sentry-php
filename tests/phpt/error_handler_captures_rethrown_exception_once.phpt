--TEST--
Test that a thrown exception is captured only once if rethrown from a previous exception handler
--FILE--
<?php

declare(strict_types=1);

namespace Sentry\Tests;

use Sentry\Tests\Transport\StubTransportFactory;

$vendor = __DIR__;

while (!file_exists($vendor . '/vendor')) {
    $vendor = dirname($vendor);
}

require $vendor . '/vendor/autoload.php';

set_exception_handler(static function (\Exception $exception): void {
    echo 'Custom exception handler called' . PHP_EOL;

    throw $exception;
});

StubTransportFactory::registerClientWithStubTransport();

throw new \Exception('foo bar');
?>
--EXPECTF--
Event sent: foo bar
Custom exception handler called

Fatal error: Uncaught Exception: foo bar in %s:%d
Stack trace:
#0%a
  thrown in %s on line %d
