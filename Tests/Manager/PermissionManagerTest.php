<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Ola\RabbitMqAdminToolkitBundle\Manager\PermissionManager;
use Prophecy\Prophecy\ObjectProphecy;
use RabbitMq\ManagementApi\Api\Permission;

class PermissionManagerTest extends AbstractManagerTest
{
    private ObjectProphecy $permissions;
    private PermissionManager $permissionManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->permissions = $this->prophesize(Permission::class);
        $this->client->permissions()->willReturn($this->permissions->reveal());
        $this->configuration->isDeleteAllowed()->willReturn(false);

        $this->permissionManager = new PermissionManager();
    }

    public function test_define_create(): void
    {
        $this->configuration->getConfiguration('permissions')->willReturn([
            'foo' => [],
        ]);

        $exception = $this->get404Exception();
        $this->permissions->get('vhost', 'foo')->willThrow($exception->reveal());

        $this->permissions->create('vhost', 'foo', [])->shouldBeCalled();

        $this->permissionManager->define($this->configuration->reveal());
    }

    public function test_define_exist(): void
    {
        $this->configuration->getConfiguration('permissions')->willReturn([
            'foo' => [],
        ]);

        $this->permissions->get('vhost', 'foo')->willReturn([]);

        $this->permissions->create('vhost', 'foo', [])->shouldNotBeCalled();

        $this->permissionManager->define($this->configuration->reveal());
    }

    public function test_define_update(): void
    {
        $this->configuration->getConfiguration('permissions')->willReturn([
            'foo' => ['durable' => true],
            'bar' => ['durable' => true]
        ]);

        $this->configuration->isDeleteAllowed()->willReturn(true);

        $this->permissions->get('vhost', 'foo')->willReturn(['durable' => true]);
        $this->permissions->get('vhost', 'bar')->willReturn(['durable' => false]);

        $this->permissions->create('vhost', 'foo', ['durable' => true])->shouldNotBeCalled();
        $this->permissions->delete('vhost', 'bar')->shouldBeCalled();
        $this->permissions->create('vhost', 'bar', ['durable' => true])->shouldBeCalled();

        $this->permissionManager->define($this->configuration->reveal());
    }
}
