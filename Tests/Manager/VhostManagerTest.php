<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Http\Client\Exception\HttpException;
use Ola\RabbitMqAdminToolkitBundle\Manager\VhostManager;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use RabbitMq\ManagementApi\Api\Vhost;

class VhostManagerTest extends AbstractManagerTest
{
    private ObjectProphecy $vhosts;
    private VhostManager $vhostManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->vhosts = $this->prophesize(Vhost::class);
        $this->client->vhosts()->willReturn($this->vhosts->reveal());

        $this->vhostManager = new VhostManager();
    }

    public function test_exists_doNotExists(): void
    {
        $exception = $this->get404Exception();
        $this->vhosts->get('vhost')->willThrow($exception->reveal());

        $this->assertFalse($this->vhostManager->exists($this->configuration->reveal()));
    }

    public function test_exists(): void
    {
        $this->vhosts->get('vhost')->willReturn(['vhost']);

        $this->assertTrue($this->vhostManager->exists($this->configuration->reveal()));
    }


    public function test_define_willFail(): void
    {
        $this->expectException(HttpException::class);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(405);

        $exception = $this->prophesize(HttpException::class);
        $exception->getResponse()->willReturn($response->reveal());

        $this->vhosts->get('vhost')->willThrow($exception->reveal());
        $this->vhosts->create('vhost')->shouldNotBeCalled();

        $this->vhostManager->define($this->configuration->reveal());
    }

    public function test_define_create(): void
    {
        $exception = $this->get404Exception();
        $this->vhosts->get('vhost')->willThrow($exception->reveal());
        $this->vhosts->create('vhost')->shouldBeCalled();

        $this->vhostManager->define($this->configuration->reveal());
    }

    public function test_define_vhostExists(): void
    {
        $this->vhosts->get('vhost')->willReturn([]);
        $this->vhosts->create('vhost')->shouldNotBeCalled();

        $this->vhostManager->define($this->configuration->reveal());
    }
}
