<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Manager;

abstract class AbstractManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;
    protected $client;

    public function setUp()
    {
        $this->client = $this->prophesize('RabbitMq\ManagementApi\Client');

        $this->configuration = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\VhostConfiguration');
        $this->configuration->getClient()->willReturn($this->client->reveal());
        $this->configuration->getName()->willReturn('vhost');

    }

    protected function get404Exception()
    {
        $response = $this->prophesize('Guzzle\Http\Message\Response');
        $response->getStatusCode()->willReturn(404);

        $exception = $this->prophesize('Guzzle\Http\Exception\ClientErrorResponseException');
        $exception->getResponse()->willReturn($response->reveal());

        return $exception;
    }
}
