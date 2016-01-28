<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests;

use Ola\RabbitMqAdminToolkitBundle\VhostHandler;

class VhostHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $vhostManager;
    private $permissionManager;
    private $exchangeManager;
    private $queueManager;

    private $configuration;

    private $vhostHandler;

    public function setUp()
    {
        $this->vhostManager = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\Manager\VhostManager');
        $this->permissionManager = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\Manager\PermissionManager');
        $this->exchangeManager = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\Manager\ExchangeManager');
        $this->queueManager = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\Manager\QueueManager');

        $this->configuration = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\VhostConfiguration');

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
