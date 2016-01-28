<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Ola\RabbitMqAdminToolkitBundle\DependencyInjection\OlaRabbitMqAdminToolkitExtension;

class OlaRabbitMqAdminToolkitExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new OlaRabbitMqAdminToolkitExtension()
        );
    }

    public function test_load_()
    {
        $this->load(array(
            'delete_allowed' => true,
            'connections' => array (
                'default' => 'http://user:password@localhost:15672',
            ),
            'vhosts' => array(
                'test' => array (
                    'name' => '/test',
                    'connection' => 'default',
                    'permissions' => array (
                        'lafourchette' => NULL,
                    ),
                    'exchanges' => array (
                        'lf.exchange.a' => NULL,
                        'lf.exchange.b' => array (
                            'type' => 'direct',
                        ),
                        'lf.exchange.c' => NULL,
                    ),
                    'queues' => array (
                        'lf.queue.a' => array (
                            'bindings' => array (
                                array (
                                    'exchange' => 'lf.exchange.a',
                                    'routing_key' => 'a.#',
                                ),
                                array (
                                    'exchange' => 'lf.exchange.b',
                                    'routing_key' => 'b.#',
                                ),
                            ),
                        ),
                        'lf.queue.b' => array (
                            'bindings' => array (
                                array (
                                    'exchange' => 'lf.exchange.a',
                                    'routing_key' => 'a.#',
                                ),
                                array (
                                    'exchange' => 'lf.exchange.b',
                                    'routing_key' => 'b.#',
                                ),
                                array (
                                    'exchange' => 'lf.exchange.c',
                                    'routing_key' => 'c.#',
                                ),
                            ),
                        ),
                        'lf.queue.c' => array (
                            'bindings' => array (
                                array (
                                    'exchange' => 'lf.exchange.a',
                                    'routing_key' => 'a.#',
                                ),
                                array (
                                    'exchange' => 'lf.exchange.c',
                                    'routing_key' => 'c.#',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ));
        $this->assertContainerBuilderHasService('ola_rabbit_mq_admin_toolkit.connection.default');
        $this->assertContainerBuilderHasService('ola_rabbit_mq_admin_toolkit.configuration.test');
        $this->assertContainerBuilderHasParameter('ola_rabbit_mq_admin_toolkit.default_vhost');
    }
}
