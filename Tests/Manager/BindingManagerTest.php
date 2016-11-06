<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\BindingManager;

class BindingManagerTest extends AbstractManagerTest
{
    private $bindings;

    private $bindingManager;

    public function setUp()
    {
        parent::setUp();

        $this->bindings = $this->prophesize('RabbitMq\ManagementApi\Api\Binding');
        $this->client->bindings()->willReturn($this->bindings->reveal());

        $this->bindingManager = new BindingManager();
    }

    public function test_define_create()
    {
        $bindings = array(
            array('exchange' => 'bar')
        );

        $this->bindings->get('vhost', 'bar', 'foo', null)->willReturn(array());

        $this->bindings->create('vhost', 'bar', 'foo', null)->shouldBeCalled();

        $this->bindingManager->define($this->configuration->reveal(), 'foo', $bindings);
    }

    public function test_define_create_with_not_found_exception()
    {
        $bindings = array(
            array('exchange' => 'bar', 'routing_key' => 'foo.#')
        );

        $exception = $this->get404Exception();
        $this->bindings->get('vhost', 'bar', 'foo', 'foo.#')->willThrow($exception->reveal());

        $this->bindings->create('vhost', 'bar', 'foo', 'foo.#')->shouldBeCalled();

        $this->bindingManager->define($this->configuration->reveal(), 'foo', $bindings);
    }

    public function test_define_bindingExists()
    {
        $bindings = array(
            array('exchange' => 'bar', 'routing_key' => 'foo.#')
        );

        $this->bindings->get('vhost', 'bar', 'foo', 'foo.#')->willReturn(array('binding'));

        $this->bindings->create('vhost', 'bar', 'foo', 'foo.#')->shouldNotBeCalled();

        $this->bindingManager->define($this->configuration->reveal(), 'foo', $bindings);
    }
}
