<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests;

use Ola\RabbitMqAdminToolkitBundle\ClientFactory;
use Ola\RabbitMqAdminToolkitBundle\Exception\ConfigurationNotFoundException;
use Ola\RabbitMqAdminToolkitBundle\Exception\ConnectionNotFoundException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use Ola\RabbitMqAdminToolkitBundle\VhostConfigurationFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class VhostConfigurationFactoryTest extends TestCase
{
    private ObjectProphecy $clientFactory;

    private VhostConfigurationFactory $vhostConfigurationFactory;

    public function setUp(): void
    {
        $this->clientFactory = $this->prophesize(ClientFactory::class);
        $this->vhostConfigurationFactory = new VhostConfigurationFactory(
            $this->clientFactory->reveal(),
            true,
            [
                'default' => 'https://login:pwd@rabbitmq.local:443'
            ],
            [
                'default' => [
                    'connection' => 'default'
                ],
                'with_connection_not_found' => [
                    'connection' => 'not_found'
                ]
            ]
        );
    }

    public function testWithVhostNotFound()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $this->clientFactory->getClient(Argument::cetera())->shouldNotBeCalled();

        $this->vhostConfigurationFactory->getVhostConfiguration('not_found');
    }

    public function testWithConnectionNotFound()
    {
        $this->expectException(ConnectionNotFoundException::class);
        $this->clientFactory->getClient(Argument::cetera())->shouldNotBeCalled();

        $this->vhostConfigurationFactory->getVhostConfiguration('with_connection_not_found');
    }

    public function testWithWorkingScenario()
    {
        $this
            ->clientFactory
            ->getClient(
                'https',
                'rabbitmq.local',
                'login',
                'pwd',
                443
            )->shouldBeCalledTimes(1);

        $result = $this->vhostConfigurationFactory->getVhostConfiguration('default');

        $this->assertInstanceOf(VhostConfiguration::class, $result);
    }
}
