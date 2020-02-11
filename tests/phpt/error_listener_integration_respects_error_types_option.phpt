--TEST--
Test that the ErrorListenerIntegration integration captures only the errors allowed by the `error_types` options
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

$options = [
    'error_types' => E_ALL & ~E_USER_WARNING,
    'default_integrations' => false,
    'integrations' => [
        new ErrorListenerIntegration(),
    ],
];

StubTransportFactory::registerClientWithStubTransport($options);

trigger_error('Error thrown', E_USER_NOTICE);
trigger_error('Error thrown', E_USER_WARNING);
?>
--EXPECTF--
Event sent: User Notice: Error thrown

Notice: Error thrown in %s on line %d

Warning: Error thrown in %s on line %d
