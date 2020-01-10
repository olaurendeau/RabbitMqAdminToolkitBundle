<?php

namespace Ola\RabbitMqAdminToolkitBundle\Manager;

use Http\Client\Exception\HttpException as ClientException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class PermissionManager extends AbstractManager
{
    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration): void
    {
        foreach ($configuration->getConfiguration('permissions') as $user => $permission) {
            try {
                $remotePermission = $configuration->getClient()->permissions()->get($configuration->getName(), $user);
            } catch (ClientException $e) {
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
