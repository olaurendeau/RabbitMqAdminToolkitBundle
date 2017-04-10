<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Command;

use Ola\RabbitMqAdminToolkitBundle\Command\VhostDefineCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class VhostDefineCommandTest extends \PHPUnit_Framework_TestCase
{
    private $application;
    private $container;
    private $command;
    private $commandTester;

    private $configuration;
    private $handler;

    public function setUp()
    {
        $this->configuration = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\VhostConfiguration');

        $this->handler = $this->prophesize('Ola\RabbitMqAdminToolkitBundle\VhostHandler');

        $this->application = new Application();

        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->get('ola_rabbit_mq_admin_toolkit.handler.vhost')->willReturn($this->handler->reveal());
        $this->container->getParameter('ola_rabbit_mq_admin_toolkit.silent_failure')->willReturn(false);
        $this->container->getParameter('ola_rabbit_mq_admin_toolkit.vhost_list')->willReturn(array('foo'));

        $this->command = new VhostDefineCommand();
        $this->command->setApplication($this->application);
        $this->command->setContainer($this->container->reveal());

        $this->commandTester = new CommandTester($this->command);
    }

    public function test_execute_withoutDefaultVhost()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(false);

        $this->commandTester->execute(array('command' => 'rabbitmq:define:vhost'));
    }

    public function test_execute_withoutDefaultVhostButSilentFailure()
    {
        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(false);
        $this->container->getParameter('ola_rabbit_mq_admin_toolkit.silent_failure')->willReturn(true);

        $this->commandTester->execute(array('command' => 'rabbitmq:define:vhost'));
    }

    public function test_execute_creationWithDefaultVhost()
    {
        $this->handler->exists($this->configuration)->willReturn(false);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn($this->configuration->reveal());

        $this->commandTester->execute(array('command' => 'rabbitmq:define:vhost'));

        $this->assertContains('created', $this->commandTester->getDisplay());
    }

    public function test_execute_creationWithSpecificVhost()
    {
        $this->handler->exists($this->configuration)->willReturn(false);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.bar')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.bar')->willReturn($this->configuration->reveal());

        $this->commandTester->execute(array('vhost' => 'bar'));

        $this->assertContains('created', $this->commandTester->getDisplay());
    }

    public function test_execute_updateWithDefaultVhost()
    {
        $this->handler->exists($this->configuration)->willReturn(true);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn($this->configuration->reveal());

        $this->commandTester->execute(array('command' => 'rabbitmq:define:vhost'));

        $this->assertContains('updated', $this->commandTester->getDisplay());
    }

    public function test_execute_updateWithSpecificVhost()
    {
        $this->handler->exists($this->configuration)->willReturn(true);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.bar')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.bar')->willReturn($this->configuration->reveal());

        $this->commandTester->execute(array('vhost' => 'bar'));

        $this->assertContains('updated', $this->commandTester->getDisplay());
    }
}
