<?php

namespace Sentry\Tests\Transport;

use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\Options;
use Sentry\SentrySdk;
use Sentry\Transport\TransportFactoryInterface;
use Sentry\Transport\TransportInterface;

class StubTransportFactory implements TransportFactoryInterface
{
    public function create(Options $options): TransportInterface
    {
        return new class implements TransportInterface {
            public function send(Event $event): ?string
            {
                echo 'Event sent: ' . $event->getExceptions()[0]['value'] . PHP_EOL;

                return null;
            }
        };
    }

    public static function registerClientWithStubTransport(): void
    {
        $client = (new ClientBuilder(new Options(['dsn' => 'http://public@example.com/'])))
            ->setTransportFactory(new self())
            ->getClient();

        SentrySdk::getCurrentHub()->bindClient($client);
    }
}
