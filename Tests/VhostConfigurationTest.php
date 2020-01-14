<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests;

use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use RabbitMq\ManagementApi\Client;

class VhostConfigurationTest extends TestCase
{
    private ObjectProphecy $client;
    private VhostConfiguration $configuration;

    public function setUp(): void
    {
        $this->createConfiguration();
    }

    public function test_getClient()
    {
        $this->assertSame($this->client->reveal(), $this->configuration->getClient());
    }

    public function test_getName()
    {
        $this->assertSame('foo', $this->configuration->getName());
    }

    public function test_getConfiguration_all()
    {
        $this->assertSame(['foo' => 'bar'], $this->configuration->getConfiguration());
    }

    public function test_getConfiguration_singleKey()
    {
        $this->assertSame('bar', $this->configuration->getConfiguration('foo'));
    }

    public function test_isDeleteAllowed_global()
    {
        $this->assertSame(false, $this->configuration->isDeleteAllowed());
    }

    public function test_isDeleteAllowed_vhostOverride()
    {
        $this->createConfiguration(['delete_allowed' => true]);
        $this->assertSame(true, $this->configuration->isDeleteAllowed());
    }

    private function createConfiguration($configuration = ['foo' => 'bar'])
    {
        $this->client = $this->prophesize(Client::class);

        $this->configuration = new VhostConfiguration($this->client->reveal(), 'foo', $configuration, false);
    }
}
