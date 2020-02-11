--TEST--
Test that the FatalErrorListenerIntegration integration captures only the errors allowed by the `error_types` option
--FILE--
<?php

declare(strict_types=1);

namespace Sentry\Tests;

use Sentry\Integration\FatalErrorListenerIntegration;
use Sentry\Tests\Transport\StubTransportFactory;

$vendor = __DIR__;

while (!file_exists($vendor . '/vendor')) {
    $vendor = dirname($vendor);
}

require $vendor . '/vendor/autoload.php';

$client = StubTransportFactory::registerClientWithStubTransport([
    'default_integrations' => false,
    'integrations' => [
        new FatalErrorListenerIntegration(),
    ],
]);


class FooClass implements \Serializable
{
}

$client->getOptions()->setErrorTypes(E_ALL & ~E_ERROR);

class BarClass implements \Serializable
{
}
?>
--EXPECTF--
Fatal error: Class Sentry\Tests\FooClass contains 2 abstract methods and must therefore be declared abstract or implement the remaining methods (Serializable::serialize, Serializable::unserialize) in %s on line %d
Event sent: Error: Class Sentry\Tests\FooClass contains 2 abstract methods and must therefore be declared abstract or implement the remaining methods (Serializable::serialize, Serializable::unserialize)
