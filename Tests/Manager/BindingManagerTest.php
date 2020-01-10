<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\BindingManager;
use Prophecy\Prophecy\ObjectProphecy;
use RabbitMq\ManagementApi\Api\Binding;

class BindingManagerTest extends AbstractManagerTest
{
    private ObjectProphecy $bindings;
    private BindingManager $bindingManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->bindings = $this->prophesize(Binding::class);
        $this->client->bindings()->willReturn($this->bindings->reveal());

        $this->bindingManager = new BindingManager();
    }

    public function test_define_create(): void
    {
        $bindings = [
            ['exchange' => 'bar']
        ];

        $this->bindings->get('vhost', 'bar', 'foo', null)->willReturn([]);

        $this->bindings->create('vhost', 'bar', 'foo', null)->shouldBeCalled();

        $this->bindingManager->define($this->configuration->reveal(), 'foo', $bindings);
    }

    public function test_define_create_with_not_found_exception(): void
    {
        $bindings = [
            ['exchange' => 'bar', 'routing_key' => 'foo.#']
        ];

        $exception = $this->get404Exception();
        $this->bindings->get('vhost', 'bar', 'foo', 'foo.#')->willThrow($exception->reveal());

        $this->bindings->create('vhost', 'bar', 'foo', 'foo.#')->shouldBeCalled();

        $this->bindingManager->define($this->configuration->reveal(), 'foo', $bindings);
    }

    public function test_define_bindingExists(): void
    {
        $bindings = [
            ['exchange' => 'bar', 'routing_key' => 'foo.#']
        ];

        $this->bindings->get('vhost', 'bar', 'foo', 'foo.#')->willReturn(['binding']);

        $this->bindings->create('vhost', 'bar', 'foo', 'foo.#')->shouldNotBeCalled();

        $this->bindingManager->define($this->configuration->reveal(), 'foo', $bindings);
    }
}
