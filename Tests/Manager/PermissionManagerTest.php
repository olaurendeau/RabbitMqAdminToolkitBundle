<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\PermissionManager;

class PermissionManagerTest extends AbstractManagerTest
{
    private $permissions;
    private $permissionManager;

    public function setUp()
    {
        parent::setUp();

        $this->permissions = $this->prophesize('RabbitMq\ManagementApi\Api\Permission');
        $this->client->permissions()->willReturn($this->permissions->reveal());
        $this->configuration->isDeleteAllowed()->willReturn(false);

        $this->permissionManager = new PermissionManager();
    }

    public function test_define_create()
    {
        $this->configuration->getConfiguration('permissions')->willReturn(array(
            'foo' => array(),
        ));

        $exception = $this->get404Exception();
        $this->permissions->get('vhost', 'foo')->willThrow($exception->reveal());

        $this->permissions->create('vhost', 'foo', array())->shouldBeCalled();

        $this->permissionManager->define($this->configuration->reveal());
    }

    public function test_define_exist()
    {
        $this->configuration->getConfiguration('permissions')->willReturn(array(
            'foo' => array(),
        ));

        $this->permissions->get('vhost', 'foo')->willReturn(array());

        $this->permissions->create('vhost', 'foo', array())->shouldNotBeCalled();

        $this->permissionManager->define($this->configuration->reveal());
    }

    public function test_define_update()
    {
        $this->configuration->getConfiguration('permissions')->willReturn(array(
            'foo' => array('durable' => true),
            'bar' => array('durable' => true)
        ));

        $this->configuration->isDeleteAllowed()->willReturn(true);

        $this->permissions->get('vhost', 'foo')->willReturn(array('durable' => true));
        $this->permissions->get('vhost', 'bar')->willReturn(array('durable' => false));

        $this->permissions->create('vhost', 'foo', array('durable' => true))->shouldNotBeCalled();
        $this->permissions->delete('vhost', 'bar')->shouldBeCalled();
        $this->permissions->create('vhost', 'bar', array('durable' => true))->shouldBeCalled();

        $this->permissionManager->define($this->configuration->reveal());
    }
}
