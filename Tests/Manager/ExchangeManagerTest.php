<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\ExchangeManager;

class ExchangeManagerTest extends AbstractManagerTest
{
    private $exchanges;
    private $exchangeManager;

    public function setUp()
    {
        parent::setUp();

        $this->exchanges = $this->prophesize('RabbitMq\ManagementApi\Api\Exchange');
        $this->client->exchanges()->willReturn($this->exchanges->reveal());
        $this->configuration->isDeleteAllowed()->willReturn(false);

        $this->exchangeManager = new ExchangeManager();
    }

    public function test_define_create()
    {
        $this->configuration->getConfiguration('exchanges')->willReturn(array(
            'foo' => array(),
            'bar' => array('name' => 'doe')
        ));

        $exception = $this->get404Exception();
        $this->exchanges->get('vhost', 'foo')->willThrow($exception->reveal());
        $this->exchanges->get('vhost', 'doe')->willThrow($exception->reveal());

        $this->exchanges->create('vhost', 'foo', array())->shouldBeCalled();
        $this->exchanges->create('vhost', 'doe', array())->shouldBeCalled();

        $this->exchangeManager->define($this->configuration->reveal());
    }

    public function test_define_exist()
    {
        $this->configuration->getConfiguration('exchanges')->willReturn(array(
            'foo' => array(),
            'bar' => array('name' => 'doe')
        ));

        $this->exchanges->get('vhost', 'foo')->willReturn(array());
        $this->exchanges->get('vhost', 'doe')->willReturn(array());

        $this->exchanges->create('vhost', 'foo', array())->shouldNotBeCalled();
        $this->exchanges->create('vhost', 'doe', array())->shouldNotBeCalled();

        $this->exchangeManager->define($this->configuration->reveal());
    }

    public function test_define_update()
    {
        $this->configuration->getConfiguration('exchanges')->willReturn(array(
            'foo' => array('durable' => true),
            'bar' => array('name' => 'doe', 'durable' => true)
        ));

        $this->configuration->isDeleteAllowed()->willReturn(true);

        $this->exchanges->get('vhost', 'foo')->willReturn(array('durable' => true));
        $this->exchanges->get('vhost', 'doe')->willReturn(array('durable' => false));

        $this->exchanges->create('vhost', 'foo', array('durable' => true))->shouldNotBeCalled();
        $this->exchanges->delete('vhost', 'doe')->shouldBeCalled();
        $this->exchanges->create('vhost', 'doe', array('durable' => true))->shouldBeCalled();

        $this->exchangeManager->define($this->configuration->reveal());
    }
}
