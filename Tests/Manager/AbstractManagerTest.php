<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

use Http\Client\Exception\HttpException;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use RabbitMq\ManagementApi\Client;

abstract class AbstractManagerTest extends TestCase
{
    protected ObjectProphecy $configuration;
    protected ObjectProphecy $client;

    public function setUp(): void
    {
        $this->client = $this->prophesize(Client::class);
        $this->configuration = $this->prophesize(VhostConfiguration::class);

        $this->configuration->getClient()->willReturn($this->client->reveal());
        $this->configuration->getName()->willReturn('vhost');
    }

    protected function get404Exception(): ObjectProphecy
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(404);

        $exception = $this->prophesize(HttpException::class);
        $exception->getResponse()->willReturn($response->reveal());

        return $exception;
    }
}
