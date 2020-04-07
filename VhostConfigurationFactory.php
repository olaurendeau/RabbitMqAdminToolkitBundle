<?php

namespace Ola\RabbitMqAdminToolkitBundle;

use Ola\RabbitMqAdminToolkitBundle\Exception\ConfigurationNotFoundException;
use Ola\RabbitMqAdminToolkitBundle\Exception\ConnectionNotFoundException;

/**
 * This factory will be used by the VhostDefineCommand to create all the VhostConfiguration services.
 * Those services used to be created as container definitions at bundle's injection but have been removed so that
 * the bundle remains as discrete as possible when non used, and doesn't add unnecessary processing during the
 * dependency injection phase, as its purpose is to be used as a one-shot to define the vhosts
 */
class VhostConfigurationFactory
{
    private ClientFactory $clientFactory;

    private bool $deleteAllowed;

    private array $connections;

    private array $vhosts;

    public function __construct(ClientFactory $clientFactory, bool $deleteAllowed, array $connections, array $vhosts)
    {
        $this->clientFactory = $clientFactory;
        $this->deleteAllowed = $deleteAllowed;
        $this->connections = $connections;
        $this->vhosts = $vhosts;
    }

    public function getVhostConfiguration(string $vhostName): VhostConfiguration
    {
        return 'poo';
        
        $vhostConfiguration = $this->vhosts[$vhostName] ?? null;

        if (null === $vhostConfiguration) {
            throw new ConfigurationNotFoundException(sprintf('No vhost configuration found for vhost %s', $vhostName));
        }

        $connectionUri = $this->connections[$vhostConfiguration['connection']] ?? null;

        if (null === $connectionUri) {
            throw new ConnectionNotFoundException(sprintf('No connection found for vhost %s', $vhostName));
        }

        $parsedUri = parse_url($connectionUri);

        return new VhostConfiguration(
            $this->clientFactory->getClient(
                $parsedUri['scheme'],
                $parsedUri['host'],
                $parsedUri['user'],
                $parsedUri['pass'],
                $parsedUri['port'] ?? 80
            ),
            $vhostConfiguration['name'] ?? $vhostName,
            $vhostConfiguration,
            $this->deleteAllowed
        );
    }
}
