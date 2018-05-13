<?php

/*
 * This file is part of Raven.
 *
 * (c) Sentry Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Raven;

/**
 * This class implements a simple error handler that catches all configured
 * error types and logs them using a certain Raven client. Registering more
 * than once this error handler is not supported and will lead to nasty problems.
 * The code is based on the Symfony Debug component.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
class ErrorHandler extends AbstractErrorHandler
{
    /**
     * Registers this error handler by associating its instance with the given
     * Raven client.
     *
     * @param Client $client             The Raven client
     * @param int    $reservedMemorySize The amount of memory to reserve for the fatal error handler
     *
     * @return ErrorHandler
     */
    public static function register(Client $client, $reservedMemorySize = 10240)
    {
        return new self($client, $reservedMemorySize);
    }

    /**
     * {@inheritdoc}
     */
    protected function doHandleException($exception)
    {
        $this->client->captureException($exception);
    }
}
