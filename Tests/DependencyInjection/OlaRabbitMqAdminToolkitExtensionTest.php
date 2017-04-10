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

    public function test_load_successfull()
    {
        $this->load(array(
            'delete_allowed' => true,
            'connections' => array(
                'default' => 'http://user:password@localhost',
            ),
            'vhosts' => array(
                'test' => array(
                    'name' => '/test',
                    'connection' => 'default',
                    'permissions' => array(
                        'lafourchette' => NULL,
                    ),
                    'exchanges' => array(
                        'exchange.a' => NULL,
                        'exchange.b' => array(
                            'type' => 'direct',
                        ),
                        'exchange.c' => NULL,
                    ),
                    'queues' => array(
                        'queue.a.sharded' => array(
                            'name' => 'queue.a.{modulus}',
                            'modulus' => 10,
                            'bindings' => array(
                                array(
                                    'exchange' => 'exchange.a',
                                    'routing_key' => 'a.{modulus}.#',
                                ),
                                array(
                                    'exchange' => 'exchange.b',
                                    'routing_key' => 'b.#',
                                ),
                            ),
                        ),
                        'queue.b.{modulus}' => array(
                            'bindings' => array(
                                array(
                                    'exchange' => 'exchange.a',
                                    'routing_key' => 'a.#',
                                ),
                                array(
                                    'exchange' => 'exchange.b',
                                    'routing_key' => 'b.{modulus}.#',
                                ),
                                array(
                                    'exchange' => 'exchange.c',
                                    'routing_key' => 'c.#',
                                ),
                            ),
                        ),
                        'queue.c' => array(
                            'bindings' => array(
                                array(
                                    'exchange' => 'exchange.a',
                                    'routing_key' => 'a.#',
                                ),
                                array(
                                    'exchange' => 'exchange.c',
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
        $this->assertContainerBuilderHasParameter('ola_rabbit_mq_admin_toolkit.vhost_list');
    }

    public function dataProvider_load_failBecauseModulusIsImproperlyDefined()
    {
        return array(
            // Should have {modulus} in at least one routing_key
            array(
                array(
                    'queue.a.{modulus}' => array(
                        'modulus' => 10,
                        'bindings' => array(
                            array(
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.#',
                            ),
                            array(
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.#',
                            ),
                        ),
                    ),
                )
            ),
            // Should have {modulus} in at least one routing_key
            array(
                array(
                    'queue.a.sharded' => array(
                        'name' => 'queue.a.{modulus}',
                        'modulus' => 10,
                        'bindings' => array(
                            array(
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.#',
                            ),
                            array(
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.#',
                            ),
                        ),
                    ),
                )
            ),
            // Should have {modulus} in queue name
            array(
                array(
                    'queue.a.sharded' => array(
                        'modulus' => 10,
                        'bindings' => array(
                            array(
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.{modulus}',
                            ),
                            array(
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.#',
                            ),
                        ),
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider dataProvider_load_failBecauseModulusIsImproperlyDefined
     */
    public function test_load_failBecauseModulusIsImproperlyDefined($queues)
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');

        $this->load(array(
            'delete_allowed' => true,
            'connections' => array(
                'default' => 'http://user:password@localhost:15672',
            ),
            'vhosts' => array(
                'test' => array(
                    'name' => '/test',
                    'connection' => 'default',
                    'permissions' => array(
                        'lafourchette' => NULL,
                    ),
                    'exchanges' => array(
                        'exchange.a' => NULL,
                        'exchange.b' => array(
                            'type' => 'direct',
                        ),
                    ),
                    'queues' => $queues
                ),
            ),
        ));
    }
}
