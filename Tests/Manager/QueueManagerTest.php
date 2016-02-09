<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\QueueManager;

class QueueManagerTest extends AbstractManagerTest
{
    private $bindingManager;
    private $queues;
    private $queueManager;

    public function setUp()
    {
        parent::setUp();

        $this->queues = $this->prophesize('RabbitMq\ManagementApi\Api\Queue');
        $this->client->queues()->willReturn($this->queues->reveal());
        $this->configuration->isDeleteAllowed()->willReturn(false);
        $this->bindingManager = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\Manager\BindingManager');

        $this->queueManager = new QueueManager($this->bindingManager->reveal());
    }

    public function test_define_create()
    {
        $this->configuration->getConfiguration('queues')->willReturn(array(
            'foo' => array('bindings' => array('foo-bind'), 'modulus' => null),
            'bar' => array('name' => 'doe', 'bindings' => array('doe-bind'), 'modulus' => null)
        ));

        $exception = $this->get404Exception();
        $this->queues->get('vhost', 'foo')->willThrow($exception->reveal());
        $this->queues->get('vhost', 'doe')->willThrow($exception->reveal());

        $this->queues->create('vhost', 'foo', array())->shouldBeCalled();
        $this->queues->create('vhost', 'doe', array())->shouldBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', array('foo-bind'));
        $this->bindingManager->define($this->configuration, 'doe', array('doe-bind'));

        $this->queueManager->define($this->configuration->reveal());
    }

    public function test_define_createSharded()
    {
        $this->configuration->getConfiguration('queues')->willReturn(array(
            'foo' => array('bindings' => array('foo-bind'), 'modulus' => null),
            'bar' => array('name' => 'doe.{modulus}', 'bindings' => array(array('routing_key' => 'routing.{modulus}')), 'modulus' => 2)
        ));

        $exception = $this->get404Exception();
        $this->queues->get('vhost', 'foo')->willThrow($exception->reveal());
        $this->queues->get('vhost', 'doe.0')->willThrow($exception->reveal());
        $this->queues->get('vhost', 'doe.1')->willThrow($exception->reveal());

        $this->queues->create('vhost', 'foo', array())->shouldBeCalled();
        $this->queues->create('vhost', 'doe.0', array())->shouldBeCalled();
        $this->queues->create('vhost', 'doe.1', array())->shouldBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', array('foo-bind'));
        $this->bindingManager->define($this->configuration, 'doe.0', array('routing_key' => 'routing.0'));
        $this->bindingManager->define($this->configuration, 'doe.1', array('routing_key' => 'routing.1'));

        $this->queueManager->define($this->configuration->reveal());
    }

    public function test_define_exist()
    {
        $this->configuration->getConfiguration('queues')->willReturn(array(
            'foo' => array('bindings' => array('foo-bind'), 'modulus' => null),
            'bar' => array('name' => 'doe', 'bindings' => array('doe-bind'), 'modulus' => null)
        ));

        $this->queues->get('vhost', 'foo')->willReturn(array());
        $this->queues->get('vhost', 'doe')->willReturn(array());

        $this->queues->create('vhost', 'foo', array())->shouldNotBeCalled();
        $this->queues->create('vhost', 'doe', array())->shouldNotBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', array('foo-bind'));
        $this->bindingManager->define($this->configuration, 'doe', array('doe-bind'));

        $this->queueManager->define($this->configuration->reveal());
    }

    public function test_define_update()
    {
        $this->configuration->getConfiguration('queues')->willReturn(array(
            'foo' => array('durable' => true, 'bindings' => array('foo-bind'), 'modulus' => null),
            'bar' => array('name' => 'doe', 'durable' => true, 'bindings' => array('doe-bind'), 'modulus' => null)
        ));

        $this->configuration->isDeleteAllowed()->willReturn(true);

        $this->queues->get('vhost', 'foo')->willReturn(array('durable' => true));
        $this->queues->get('vhost', 'doe')->willReturn(array('durable' => false));

        $this->queues->create('vhost', 'foo', array('durable' => true))->shouldNotBeCalled();
        $this->queues->delete('vhost', 'doe')->shouldBeCalled();
        $this->queues->create('vhost', 'doe', array('durable' => true))->shouldBeCalled();

        $this->bindingManager->define($this->configuration, 'foo', array('foo-bind'));
        $this->bindingManager->define($this->configuration, 'doe', array('doe-bind'));

        $this->queueManager->define($this->configuration->reveal());
    }
}
