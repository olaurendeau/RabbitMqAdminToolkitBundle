<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Http\Client\Exception\HttpException as ClientException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class QueueManager extends AbstractManager
{
    const MODULUS_PLACEHOLDER = '{modulus}';

    /**
     * @var BindingManager
     */
    private BindingManager $bindingManager;

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
    public function define(VhostConfiguration $configuration): void
    {
        foreach ($configuration->getConfiguration('queues') as $queue) {

            $name = $queue['name'];
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
     * @param string $name
     * @param array $queue
     */
    private function createQueue(VhostConfiguration $configuration, string $name, array $queue): void
    {
        try {
            $remoteQueue = $configuration->getClient()->queues()->get($configuration->getName(), $name);
        } catch (ClientException $e) {
            $this->handleNotFoundException($e);

            $configuration->getClient()->queues()->create($configuration->getName(), $name, $queue);
        }

        if ($configuration->isDeleteAllowed() && isset($remoteQueue) && !$this->isUpToDate($queue, $remoteQueue)) {
            $configuration->getClient()->queues()->delete($configuration->getName(), $name);
            $configuration->getClient()->queues()->create($configuration->getName(), $name, $queue);
        }
    }

    /**
     * @param string $name
     * @param string $modulus
     *
     * @return string
     */
    private function getModulusName(string $name, string $modulus): string
    {
        return str_replace(self::MODULUS_PLACEHOLDER, $modulus, $name);
    }
}
