<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests;

use Ola\RabbitMqAdminToolkitBundle\Manager\ExchangeManager;
use Ola\RabbitMqAdminToolkitBundle\Manager\PermissionManager;
use Ola\RabbitMqAdminToolkitBundle\Manager\QueueManager;
use Ola\RabbitMqAdminToolkitBundle\Manager\VhostManager;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use Ola\RabbitMqAdminToolkitBundle\VhostHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class VhostHandlerTest extends TestCase
{
    private ObjectProphecy $vhostManager;
    private ObjectProphecy $permissionManager;
    private ObjectProphecy $exchangeManager;
    private ObjectProphecy $queueManager;
    private ObjectProphecy $configuration;

    private VhostHandler $vhostHandler;

    public function setUp(): void
    {
        $this->vhostManager = $this->prophesize(VhostManager::class);
        $this->permissionManager = $this->prophesize(PermissionManager::class);
        $this->exchangeManager = $this->prophesize(ExchangeManager::class);
        $this->queueManager = $this->prophesize(QueueManager::class);
        $this->configuration = $this->prophesize(VhostConfiguration::class);

        $this->vhostHandler = new VhostHandler(
            $this->vhostManager->reveal(),
            $this->permissionManager->reveal(),
            $this->exchangeManager->reveal(),
            $this->queueManager->reveal()
        );
    }

    public function test_exists()
    {
        $this->vhostManager->exists($this->configuration->reveal())->willReturn(true);

        $this->assertTrue($this->vhostHandler->exists($this->configuration->reveal()));
    }

    public function test_define()
    {
        $this->vhostManager->define($this->configuration)->shouldBeCalled();
        $this->permissionManager->define($this->configuration)->shouldBeCalled();
        $this->exchangeManager->define($this->configuration)->shouldBeCalled();
        $this->queueManager->define($this->configuration)->shouldBeCalled();

        $this->vhostHandler->define($this->configuration->reveal());
    }
}
