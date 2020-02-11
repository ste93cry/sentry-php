--TEST--
Test catching out of memory fatal error
--FILE--
<?php

declare(strict_types=1);

namespace Sentry\Tests;

use Sentry\Tests\Transport\StubTransportFactory;

ini_set('memory_limit', '20M');

$vendor = __DIR__;

while (!file_exists($vendor . '/vendor')) {
    $vendor = dirname($vendor);
}

require $vendor . '/vendor/autoload.php';

StubTransportFactory::registerClientWithStubTransport();

$foo = str_repeat('x', 1024 * 1024 * 30);
?>
--EXPECTF--
Fatal error: Allowed memory size of %d bytes exhausted (tried to allocate %d bytes) in %s on line %d
Event sent: Error: Allowed memory size of %d bytes exhausted (tried to allocate %d bytes)
