<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class VhostManager extends AbstractManager
{
    /**
     * @param VhostConfiguration $configuration
     *
     * @return bool
     */
    public function exists(VhostConfiguration $configuration)
    {
        try {
            $configuration->getClient()->vhosts()->get($configuration->getName());
        } catch (ClientErrorResponseException $e) {
            $this->handleNotFoundException($e);

            return false;
        }

        return true;
    }

    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration)
    {
        if (!$this->exists($configuration)) {
            $configuration->getClient()->vhosts()->create($configuration->getName());
        }
    }
}
