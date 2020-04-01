<?php

namespace Ola\RabbitMqAdminToolkitBundle;

use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use RabbitMq\ManagementApi\Client;

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
        $vhostConfiguration = $this->vhosts[$vhostName] ?? null;
        
        if (null === $vhostConfiguration) {
            throw new \Exception(sprintf('No vhost configuration found for vhost %s', $vhostConfiguration['name']));
        }
        
        $connectionUri = $this->connections[$vhostConfiguration['connection']] ?? null;

        if (null === $connectionUri) {
            throw new \Exception(sprintf('No connection found for vhost %s', $vhostConfiguration['name']));
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
