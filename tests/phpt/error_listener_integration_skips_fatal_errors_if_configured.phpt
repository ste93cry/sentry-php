--TEST--
Test that the ErrorListenerIntegration integration ignores the fatal errors if configured to do so
--FILE--
<?php

declare(strict_types=1);

namespace Sentry\Tests;

use Sentry\Integration\ErrorListenerIntegration;
use Sentry\Tests\Transport\StubTransportFactory;

$vendor = __DIR__;

while (!file_exists($vendor . '/vendor')) {
    $vendor = dirname($vendor);
}

require $vendor . '/vendor/autoload.php';

StubTransportFactory::registerClientWithStubTransport([
    'default_integrations' => false,
    'integrations' => [
        new ErrorListenerIntegration(null, false),
    ],
]);

class FooClass implements \Serializable
{
}
?>
--EXPECTF--
Fatal error: Class Sentry\Tests\FooClass contains 2 abstract methods and must therefore be declared abstract or implement the remaining methods (Serializable::serialize, Serializable::unserialize) in %s on line %d
