<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\Command;

use Ola\RabbitMqAdminToolkitBundle\Command\VhostDefineCommand;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use Ola\RabbitMqAdminToolkitBundle\VhostHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VhostDefineCommandTest extends TestCase
{
    private ObjectProphecy $container;
    private ObjectProphecy $configuration;
    private ObjectProphecy $handler;

    private Application $application;
    private VhostDefineCommand $command;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $this->configuration = $this->prophesize(VhostConfiguration::class);
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->handler = $this->prophesize(VhostHandler::class);

        $this->defineCommand(false);
    }

    public function test_execute_withoutDefaultVhost(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(false);

        $this->commandTester->execute(['command' => 'rabbitmq:define:vhost']);
    }

    public function test_execute_withoutDefaultVhostButSilentFailure(): void
    {
        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(false);
        $this->defineCommand(true);

        $this->assertEquals(0, $this->commandTester->execute(['command' => 'rabbitmq:define:vhost']));
    }

    public function test_execute_creationWithDefaultVhost(): void
    {
        $this->handler->exists($this->configuration)->willReturn(false);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.foo')
            ->willReturn($this->configuration->reveal());

        $this->commandTester->execute(['command' => 'rabbitmq:define:vhost']);

        $this->assertStringContainsString('created', $this->commandTester->getDisplay());
    }

    public function test_execute_creationWithSpecificVhost(): void
    {
        $this->handler->exists($this->configuration)->willReturn(false);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.bar')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.bar')
            ->willReturn($this->configuration->reveal());

        $this->commandTester->execute(['vhost' => 'bar']);

        $this->assertStringContainsString('created', $this->commandTester->getDisplay());
    }

    public function test_execute_updateWithDefaultVhost(): void
    {
        $this->handler->exists($this->configuration)->willReturn(true);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.foo')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.foo')
            ->willReturn($this->configuration->reveal());

        $this->commandTester->execute(['command' => 'rabbitmq:define:vhost']);

        $this->assertStringContainsString('updated', $this->commandTester->getDisplay());
    }

    public function test_execute_updateWithSpecificVhost(): void
    {
        $this->handler->exists($this->configuration)->willReturn(true);
        $this->handler->define($this->configuration)->shouldBeCalled();

        $this->container->has('ola_rabbit_mq_admin_toolkit.configuration.bar')->willReturn(true);
        $this->container->get('ola_rabbit_mq_admin_toolkit.configuration.bar')
            ->willReturn($this->configuration->reveal());

        $this->commandTester->execute(['vhost' => 'bar']);

        $this->assertStringContainsString('updated', $this->commandTester->getDisplay());
    }

    private function defineCommand(bool $silentFailure): void
    {
        $this->application = new Application();
        $this->command = new VhostDefineCommand(
            $this->container->reveal(),
            ['foo'],
            $this->handler->reveal(),
            $silentFailure
        );
        $this->command->setApplication($this->application);

        $this->commandTester = new CommandTester($this->command);
    }
}
