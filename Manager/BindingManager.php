<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class BindingManager extends AbstractManager
{
    /**
     * @param VhostConfiguration $configuration
     * @param string $queue
     * @param array $bindings
     */
    public function define(VhostConfiguration $configuration, $queue, array $bindings)
    {
        foreach ($bindings as $binding) {

            try {
                $configuration->getClient()->bindings()->get(
                    $configuration->getName(),
                    $binding['exchange'],
                    $queue,
                    $binding['routing_key']
                );
            } catch (ClientErrorResponseException $e) {
                $this->handleNotFoundException($e);

                $configuration->getClient()->bindings()->create(
                    $configuration->getName(),
                    $binding['exchange'],
                    $queue,
                    $binding['routing_key']
                );
            }
        }
    }
}
