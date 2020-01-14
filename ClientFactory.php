<?php

namespace Ola\RabbitMqAdminToolkitBundle;

use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use RabbitMq\ManagementApi\Client;

class ClientFactory
{
    /**
     * @param string $scheme
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param int $port
     *
     * @return Client
     */
    public function getClient(string $scheme, string $host, string $user, string $pass, int $port = 80): Client
    {
        return new Client(
            new PluginClient(HttpClientDiscovery::find(), [
                new ErrorPlugin()
            ]),
            sprintf(
                '%s://%s:%d',
                $scheme,
                $host,
                $port
            ),
            $user,
            $pass
        );
    }
}
