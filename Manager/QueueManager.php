<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class QueueManager extends AbstractManager
{
    const MODULUS_PLACEHOLDER = '{modulus}';

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

            $modulus = $queue['modulus'];
            unset($queue['modulus']);

            if (null !== $modulus) {
                for ($i = 0; $i < $modulus; $i++) {
                    $this->createQueue($configuration, $this->getModulusName($name, $i), $queue);

                    $modulusBindings = $bindings;
                    foreach ($modulusBindings as $key => $binding) {
                        $modulusBindings[$key]['routing_key'] = $this->getModulusName($binding['routing_key'], $i);
                    }

                    $this->bindingManager->define(
                        $configuration,
                        $this->getModulusName($name, $i),
                        $modulusBindings
                    );
                }
            } else {
                $this->createQueue($configuration, $name, $queue);

                $this->bindingManager->define($configuration, $name, $bindings);
            }
        }
    }

    /**
     * @param VhostConfiguration $configuration
     * @param $name
     * @param $queue
     */
    private function createQueue(VhostConfiguration $configuration, $name, $queue)
    {
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
    }

    /**
     * @param $name
     * @param $modulus
     *
     * @return string
     */
    private function getModulusName($name, $modulus)
    {
        return str_replace(self::MODULUS_PLACEHOLDER, $modulus, $name);
    }
}
