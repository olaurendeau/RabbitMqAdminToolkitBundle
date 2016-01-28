<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class QueueManager extends AbstractManager
{
    /**
     * @var BindingManager
     */
    private $bindingManager;

    /**
     * @param BindingManager $bindingManager
     */
    public function __construct(BindingManager $bindingManager)
    {
        $this->bindingManager = $bindingManager;
    }

    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration)
    {
        foreach ($configuration->getConfiguration('queues') as $name => $queue) {

            $name = isset($queue['name']) ? $queue['name'] : $name;
            unset($queue['name']);

            $bindings = $queue['bindings'];
            unset($queue['bindings']);

            try {
                $remoteQueue = $configuration->getClient()->queues()->get($configuration->getName(), $name);
            } catch (ClientErrorResponseException $e) {
                $this->handleNotFoundException($e);

                $configuration->getClient()->queues()->create($configuration->getName(), $name, $queue);
            }

            if ($configuration->isDeleteAllowed() && isset($remoteQueue) && !$this->isUpToDate($queue, $remoteQueue)) {
                $configuration->getClient()->queues()->delete($configuration->getName(), $name);
                $configuration->getClient()->queues()->create($configuration->getName(), $name, $queue);
            }

            $this->bindingManager->define($configuration, $name, $bindings);
        }
    }
}
