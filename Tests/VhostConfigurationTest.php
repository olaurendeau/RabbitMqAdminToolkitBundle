<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests;

use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;

class VhostConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $configuration;

    public function setUp()
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
        $this->assertSame(array('foo' => 'bar'), $this->configuration->getConfiguration());
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
        $this->createConfiguration(array('delete_allowed' => true));
        $this->assertSame(true, $this->configuration->isDeleteAllowed());
    }

    private function createConfiguration($configuration = array('foo' => 'bar'))
    {
        $this->client = $this->prophesize('RabbitMq\ManagementApi\Client');

        $this->configuration = new VhostConfiguration($this->client->reveal(), 'foo', $configuration, false);
    }
}
