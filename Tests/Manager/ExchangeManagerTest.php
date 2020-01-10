<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\ExchangeManager;
use Prophecy\Prophecy\ObjectProphecy;
use RabbitMq\ManagementApi\Api\Exchange;

class ExchangeManagerTest extends AbstractManagerTest
{
    private ObjectProphecy $exchanges;
    private ExchangeManager $exchangeManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->exchanges = $this->prophesize(Exchange::class);
        $this->client->exchanges()->willReturn($this->exchanges->reveal());
        $this->configuration->isDeleteAllowed()->willReturn(false);

        $this->exchangeManager = new ExchangeManager();
    }

    public function test_define_create(): void
    {
        $this->configuration->getConfiguration('exchanges')->willReturn([
            'foo' => ['name' => 'foo'],
            'bar' => ['name' => 'doe']
        ]);

        $exception = $this->get404Exception();
        $this->exchanges->get('vhost', 'foo')->willThrow($exception->reveal());
        $this->exchanges->get('vhost', 'doe')->willThrow($exception->reveal());

        $this->exchanges->create('vhost', 'foo', [])->shouldBeCalled();
        $this->exchanges->create('vhost', 'doe', [])->shouldBeCalled();

        $this->exchangeManager->define($this->configuration->reveal());
    }

    public function test_define_exist(): void
    {
        $this->configuration->getConfiguration('exchanges')->willReturn([
            'foo' => ['name' => 'foo'],
            'bar' => ['name' => 'doe']
        ]);

        $this->exchanges->get('vhost', 'foo')->willReturn([]);
        $this->exchanges->get('vhost', 'doe')->willReturn([]);

        $this->exchanges->create('vhost', 'foo', [])->shouldNotBeCalled();
        $this->exchanges->create('vhost', 'doe', [])->shouldNotBeCalled();

        $this->exchangeManager->define($this->configuration->reveal());
    }

    public function test_define_update(): void
    {
        $this->configuration->getConfiguration('exchanges')->willReturn([
            'foo' => ['name' => 'foo', 'durable' => true],
            'bar' => ['name' => 'doe', 'durable' => true],
            'moo' => ['name' => 'moo', 'durable' => true, 'arguments' => ['x-bar' => 12]]
        ]);

        $this->configuration->isDeleteAllowed()->willReturn(true);

        $this->exchanges->get('vhost', 'foo')->willReturn(['durable' => true]);
        $this->exchanges->get('vhost', 'doe')->willReturn(['durable' => false]);
        $this->exchanges->get('vhost', 'moo')->willReturn(['durable' => true, 'arguments' => ['x-foo' => 19]]);

        $this->exchanges->create('vhost', 'foo', ['durable' => true])->shouldNotBeCalled();
        $this->exchanges->delete('vhost', 'doe')->shouldBeCalled();
        $this->exchanges->create('vhost', 'doe', ['durable' => true])->shouldBeCalled();
        $this->exchanges->delete('vhost', 'moo')->shouldBeCalled();
        $this->exchanges->create('vhost', 'moo', ['durable' => true, 'arguments' => ['x-bar' => 12]])->shouldBeCalled();

        $this->exchangeManager->define($this->configuration->reveal());
    }
}
