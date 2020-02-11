--TEST--
Test catching fatal errors
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

StubTransportFactory::registerClientWithStubTransport();

class TestClass implements \Serializable
{
}
?>
--EXPECTF--
Fatal error: Class Sentry\Tests\TestClass contains 2 abstract methods and must therefore be declared abstract or implement the remaining methods (Serializable::serialize, Serializable::unserialize) in %s on line %d
Event sent: Error: Class Sentry\Tests\TestClass contains 2 abstract methods and must therefore be declared abstract or implement the remaining methods (Serializable::serialize, Serializable::unserialize)
