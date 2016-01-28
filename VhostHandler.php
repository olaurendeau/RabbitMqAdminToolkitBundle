<?php

namespace Ola\RabbitMqAdminToolkitBundle;

use Ola\RabbitMqAdminToolkitBundle\Manager\ExchangeManager;
use Ola\RabbitMqAdminToolkitBundle\Manager\PermissionManager;
use Ola\RabbitMqAdminToolkitBundle\Manager\QueueManager;
use Ola\RabbitMqAdminToolkitBundle\Manager\VhostManager;

class VhostHandler
{
    /**
     * @var VhostManager
     */
    private $vhostManager;

    /**
     * @var PermissionManager
     */
    private $permissionManager;

    /**
     * @var ExchangeManager
     */
    private $exchangeManager;

    /**
     * @var QueueManager
     */
    private $queueManager;

    public function __construct(
        VhostManager $vhostManager,
        PermissionManager $permissionManager,
        ExchangeManager $exchangeManager,
        QueueManager $queueManager
    ) {
        $this->permissionManager = $permissionManager;
        $this->vhostManager = $vhostManager;
        $this->exchangeManager = $exchangeManager;
        $this->queueManager = $queueManager;
    }

    /**
     * @param VhostConfiguration $configuration
     */
    public function define(VhostConfiguration $configuration)
    {
        $this->vhostManager->define($configuration);
        $this->permissionManager->define($configuration);
        $this->exchangeManager->define($configuration);
        $this->queueManager->define($configuration);
    }

    /**
     * @param VhostConfiguration $configuration
     *
     * @return bool
     */
    public function exists(VhostConfiguration $configuration)
    {
        return $this->vhostManager->exists($configuration);
    }
}
