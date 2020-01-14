<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\BindingManager;
use Ola\RabbitMqAdminToolkitBundle\Manager\QueueManager;
use Prophecy\Prophecy\ObjectProphecy;
use RabbitMq\ManagementApi\Api\Queue;

class QueueManagerTest extends AbstractManagerTest
{
    private ObjectProphecy $bindingManager;
    private ObjectProphecy $queues;
    private QueueManager $queueManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->queues = $this->prophesize(Queue::class);
        $this->bindingManager = $this->prophesize(BindingManager::class);
        $this->client->queues()->willReturn($this->queues->reveal());
        $this->configuration->isDeleteAllowed()->willReturn(false);

        $this->queueManager = new QueueManager($this->bindingManager->reveal());
    }

    public function test_define_create(): void
    {
        $this->configuration->getConfiguration('queues')->willReturn([
            'foo' => ['name' => 'foo', 'bindings' => ['foo-bind'], 'modulus' => null],
            'bar' => ['name' => 'doe', 'bindings' => ['doe-bind'], 'modulus' => null]
        ]);

        $exception = $this->get404Exception();
        $this->queues->get('vhost', 'foo')->willThrow($exception->reveal());
        $this->queues->get('vhost', 'doe')->willThrow($exception->reveal());

        $this->queues->create('vhost', 'foo', [])->shouldBeCalled();
        $this->queues->create('vhost', 'doe', [])->shouldBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', ['foo-bind']);
        $this->bindingManager->define($this->configuration, 'doe', ['doe-bind']);

        $this->queueManager->define($this->configuration->reveal());
    }

    public function test_define_createSharded(): void
    {
        $this->configuration->getConfiguration('queues')->willReturn([
            'foo' => ['name' => 'foo', 'bindings' => ['foo-bind'], 'modulus' => null],
            'bar' => ['name' => 'doe.{modulus}', 'bindings' => [['routing_key' => 'routing.{modulus}']], 'modulus' => 2]
        ]);

        $exception = $this->get404Exception();
        $this->queues->get('vhost', 'foo')->willThrow($exception->reveal());
        $this->queues->get('vhost', 'doe.0')->willThrow($exception->reveal());
        $this->queues->get('vhost', 'doe.1')->willThrow($exception->reveal());

        $this->queues->create('vhost', 'foo', [])->shouldBeCalled();
        $this->queues->create('vhost', 'doe.0', [])->shouldBeCalled();
        $this->queues->create('vhost', 'doe.1', [])->shouldBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', ['foo-bind']);
        $this->bindingManager->define($this->configuration, 'doe.0', [['routing_key' => 'routing.0']]);
        $this->bindingManager->define($this->configuration, 'doe.1', [['routing_key' => 'routing.1']]);

        $this->queueManager->define($this->configuration->reveal());
    }

    public function test_define_exist(): void
    {
        $this->configuration->getConfiguration('queues')->willReturn([
            'foo' => ['name' => 'foo', 'bindings' => ['foo-bind'], 'modulus' => null],
            'bar' => ['name' => 'doe', 'bindings' => ['doe-bind'], 'modulus' => null]
        ]);

        $this->queues->get('vhost', 'foo')->willReturn([]);
        $this->queues->get('vhost', 'doe')->willReturn([]);

        $this->queues->create('vhost', 'foo', [])->shouldNotBeCalled();
        $this->queues->create('vhost', 'doe', [])->shouldNotBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', ['foo-bind']);
        $this->bindingManager->define($this->configuration, 'doe', ['doe-bind']);

        $this->queueManager->define($this->configuration->reveal());
    }

    public function test_define_update(): void
    {
        $this->configuration->getConfiguration('queues')->willReturn([
            'foo' => ['name' => 'foo', 'durable' => true, 'bindings' => ['foo-bind'], 'modulus' => null],
            'bar' => ['name' => 'doe', 'durable' => true, 'bindings' => ['doe-bind'], 'modulus' => null]
        ]);

        $this->configuration->isDeleteAllowed()->willReturn(true);

        $this->queues->get('vhost', 'foo')->willReturn(['durable' => true]);
        $this->queues->get('vhost', 'doe')->willReturn(['durable' => false]);

        $this->queues->create('vhost', 'foo', ['durable' => true])->shouldNotBeCalled();
        $this->queues->delete('vhost', 'doe')->shouldBeCalled();
        $this->queues->create('vhost', 'doe', ['durable' => true])->shouldBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', ['foo-bind']);
        $this->bindingManager->define($this->configuration, 'doe', ['doe-bind']);

        $this->queueManager->define($this->configuration->reveal());
    }
}
