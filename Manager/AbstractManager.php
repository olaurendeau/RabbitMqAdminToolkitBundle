<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;

abstract class AbstractManager
{
    /**
     * @param ClientErrorResponseException $e
     */
    protected function handleNotFoundException(ClientErrorResponseException $e)
    {
        if ($e->getResponse()->getStatusCode() !== 404) {
            throw $e;
        }
    }

    /**
     * @param $configuration
     * @param $remoteConfiguration
     *
     * @return bool
     */
    protected function isUpToDate($configuration, $remoteConfiguration)
    {
        $diff = array_diff_assoc($configuration, $remoteConfiguration);

        return empty($diff);
    }
}
