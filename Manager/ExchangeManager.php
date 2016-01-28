<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class ExchangeManager extends AbstractManager
{
    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration)
    {
        foreach ($configuration->getConfiguration('exchanges') as $name => $exchange) {

            $name = isset($exchange['name']) ? $exchange['name'] : $name;
            unset($exchange['name']);

            try {
                $remoteExchange = $configuration->getClient()->exchanges()->get($configuration->getName(), $name);
            } catch (ClientErrorResponseException $e) {
                $this->handleNotFoundException($e);

                $configuration->getClient()->exchanges()->create($configuration->getName(), $name, $exchange);
            }

            if ($configuration->isDeleteAllowed() && isset($remoteExchange) && !$this->isUpToDate($exchange, $remoteExchange)) {
                $configuration->getClient()->exchanges()->delete($configuration->getName(), $name);
                $configuration->getClient()->exchanges()->create($configuration->getName(), $name, $exchange);
            }
        }
    }
}
