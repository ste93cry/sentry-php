--TEST--
Test that the error handler ignores silenced errors by default, but it reports them with the appropriate option enabled.
--FILE--
<?php

declare(strict_types=1);

namespace Sentry\Tests;

use Sentry\Tests\Transport\StubTransportFactory;

$vendor = __DIR__;

while (!file_exists($vendor . '/vendor')) {
    $vendor = \dirname($vendor);
}

require $vendor . '/vendor/autoload.php';

$client = StubTransportFactory::registerClientWithStubTransport(['capture_silenced_errors' => true]);

echo 'Triggering silenced error' . PHP_EOL;

@$a++;

$client->getOptions()->setCaptureSilencedErrors(false);

echo 'Triggering silenced error' . PHP_EOL;

@$b++;
?>
--EXPECT--
Triggering silenced error
Event sent: Notice: Undefined variable: a
Triggering silenced error
