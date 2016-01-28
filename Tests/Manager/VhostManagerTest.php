<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\VhostManager;

class VhostManagerTest extends AbstractManagerTest
{
    private $vhosts;

    private $vhostManager;

    public function setUp()
    {
        parent::setUp();

        $this->vhosts = $this->prophesize('RabbitMq\ManagementApi\Api\Vhost');
        $this->client->vhosts()->willReturn($this->vhosts->reveal());

        $this->vhostManager = new VhostManager();
    }

    public function test_exists_doNotExists()
    {
        $exception = $this->get404Exception();
        $this->vhosts->get('vhost')->willThrow($exception->reveal());

        $this->assertFalse($this->vhostManager->exists($this->configuration->reveal()));
    }

    public function test_exists()
    {
        $this->vhosts->get('vhost')->willReturn(array('vhost'));

        $this->assertTrue($this->vhostManager->exists($this->configuration->reveal()));
    }

    public function test_define_create()
    {
        $exception = $this->get404Exception();
        $this->vhosts->get('vhost')->willThrow($exception->reveal());

        $this->vhosts->create('vhost')->shouldBeCalled();

        $this->vhostManager->define($this->configuration->reveal());
    }

    public function test_define_vhostExists()
    {
        $this->vhosts->get('vhost')->willReturn(array());

        $this->vhosts->create('vhost')->shouldNotBeCalled();

        $this->vhostManager->define($this->configuration->reveal());
    }
}
