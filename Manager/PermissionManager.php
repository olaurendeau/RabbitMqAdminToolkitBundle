<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class PermissionManager extends AbstractManager
{
    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration)
    {
        foreach ($configuration->getConfiguration('permissions') as $user => $permission) {
            try {
                $remotePermission = $configuration->getClient()->permissions()->get($configuration->getName(), $user);
            } catch (ClientErrorResponseException $e) {
                $this->handleNotFoundException($e);

                $configuration->getClient()->permissions()->create($configuration->getName(), $user, $permission);
            }

            if ($configuration->isDeleteAllowed() && isset($remotePermission) && !$this->isUpToDate($permission, $remotePermission)) {
                $configuration->getClient()->permissions()->delete($configuration->getName(), $user);
                $configuration->getClient()->permissions()->create($configuration->getName(), $user, $permission);
            }
        }
    }
}
