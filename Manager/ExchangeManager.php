<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Http\Client\Exception\HttpException as ClientException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class ExchangeManager extends AbstractManager
{
    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration): void
    {
        foreach ($configuration->getConfiguration('exchanges') as $exchange) {
            $name = $exchange['name'];
            unset($exchange['name']);

            try {
                $remoteExchange = $configuration->getClient()->exchanges()->get($configuration->getName(), $name);
            } catch (ClientException $e) {
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
