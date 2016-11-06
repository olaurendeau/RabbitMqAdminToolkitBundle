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
                $bindings = $configuration->getClient()->bindings()->get(
                    $configuration->getName(),
                    $binding['exchange'],
                    $queue,
                    isset($binding['routing_key']) ? $binding['routing_key'] : null
                );

                if (0 === count($bindings)) {
                    $this->createBinding($configuration, $queue, $binding);
                }

            } catch (ClientErrorResponseException $e) {
                $this->handleNotFoundException($e);

                $this->createBinding($configuration, $queue, $binding);
            }
        }
    }

    /**
     * @param VhostConfiguration $configuration
     * @param $queue
     * @param array $binding
     */
    private function createBinding(VhostConfiguration $configuration, $queue, array $binding)
    {
        $configuration->getClient()->bindings()->create(
            $configuration->getName(),
            $binding['exchange'],
            $queue,
            isset($binding['routing_key']) ? $binding['routing_key'] : null
        );
    }
}
