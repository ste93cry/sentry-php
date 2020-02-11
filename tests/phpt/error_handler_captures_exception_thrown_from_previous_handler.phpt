--TEST--
Test catching exceptions
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

set_exception_handler(static function (): void {
    echo 'Custom exception handler called' . PHP_EOL;

    throw new \Exception('foo bar baz');
});

StubTransportFactory::registerClientWithStubTransport();

throw new \Exception('foo bar');
?>
--EXPECTF--
Event sent: foo bar
Custom exception handler called
Event sent: foo bar baz

Fatal error: Uncaught Exception: foo bar baz in %s:%d
Stack trace:
%a
