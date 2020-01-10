<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Http\Client\Exception\HttpException as ClientException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class VhostManager extends AbstractManager
{
    /**
     * @param VhostConfiguration $configuration
     *
     * @return bool
     */
    public function exists(VhostConfiguration $configuration): bool
    {
        try {
            $configuration->getClient()->vhosts()->get($configuration->getName());
        } catch (ClientException $e) {
            $this->handleNotFoundException($e);

            return false;
        }

        return true;
    }

    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration): void
    {
        if (!$this->exists($configuration)) {
            $configuration->getClient()->vhosts()->create($configuration->getName());
        }
    }
}
