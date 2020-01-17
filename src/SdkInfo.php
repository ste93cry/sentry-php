<?php

declare(strict_types=1);

namespace Sentry;

/**
 * This class represents the SDK interface and stores all the information about
 * the SDK and the installed integrations and packages at the moment of capturing
 * an event.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
final class SdkInfo
{
    /**
     * @var string The name of the SDK
     */
    private $name;

    /**
     * @var string The semantic version of the SDK
     */
    private $version;

    /**
     * @var string[] The list of integrations with the platform or a framework
     *               that were explicitly activated by the user
     *
     * @psalm-var list<class-string<\Sentry\Integration\IntegrationInterface>>
     */
    private $integrations;

    /**
     * Class constructor.
     *
     * @param string   $name         The name of the SDK
     * @param string   $version      The semantic version of the SDK
     * @param string[] $integrations The list of integrations with the platform
     *                               or a framework that were explicitly activated
     *                               by the user
     *
     * @psalm-param list<class-string<\Sentry\Integration\IntegrationInterface>> $integrations
     */
    public function __construct(string $name, string $version, array $integrations = [])
    {
        if ('' === trim($name) || '' === trim($version)) {
            throw new \InvalidArgumentException('Neither the $name argument nor the $version argument can be blank.');
        }

        $this->name = $name;
        $this->version = $version;
        $this->integrations = $integrations;
    }

    /**
     * Gets the name of the SDK.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the semantic version of the SDK.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Gets list of integrations with the platform or a framework that was
     * explicitly activated by the user.
     *
     * @return string[]
     *
     * @psalm-return list<class-string<\Sentry\Integration\IntegrationInterface>>
     */
    public function getIntegrations(): array
    {
        return $this->integrations;
    }
}
