<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\DependencyInjection;

use Ola\RabbitMqAdminToolkitBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function test_basicConfiguration()
    {
        $this->assertEquals(
            $this->getBasicExpectedConfiguration(),
            $this->processConfiguration($this->getBasicConfiguration())
        );
    }

    public function test_overridingConfiguration()
    {
        $expectedConfiguration = $this->getBasicExpectedConfiguration();
        $expectedConfiguration['delete_allowed'] = true;
        $expectedConfiguration['vhosts']['default']['exchanges']['exchange.c'] = array(
            'name' => 'exchange.c',
            'durable' => true,
            'type' => 'topic'
        );

        $expectedConfiguration['vhosts']['default']['queues']['queue.a']['durable'] = false;
        $expectedConfiguration['vhosts']['default']['queues']['queue.a']['bindings'] = array(
            array(
                'exchange' => 'exchange.c',
                'routing_key' => 'b.#',
            ),
        );

        $configs = $this->getBasicConfiguration();
        $configs[0]['vhosts']['default']['queues']['queue.b'] = array(
            'bindings' => array(
                array(
                    'exchange' => 'exchange.a',
                    'routing_key' => 'c.#',
                ),
            )
        );
        $configs[] = array(
            'delete_allowed' => true,
            'vhosts' => array(
                'default' => array(
                    'exchanges' => array(
                        'exchange.c' => NULL,
                    ),
                    'queues' => array(
                        'queue.b' => false,
                        'queue.a' => array(
                            'durable' => false,
                            'bindings' => array(
                                array(
                                    'exchange' => 'exchange.c',
                                    'routing_key' => 'b.#',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals(
            $expectedConfiguration,
            $this->processConfiguration($configs)
        );
    }

    public function test_queueArgumentsNormalization()
    {
        $expectedConfiguration = $this->getBasicExpectedConfiguration();

        $expectedConfiguration['vhosts']['default']['queues']['queue.a']['arguments'] = array(
            'x-ha-policy' => 'all'
        );

        $configs = $this->getBasicConfiguration();
        $configs[0]['vhosts']['default']['queues']['queue.a']['arguments'] = array(
            'x-ha-policy' => 'all'
        );

        $this->assertEquals(
            $expectedConfiguration,
            $this->processConfiguration($configs)
        );
    }

    private function processConfiguration($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

    private function getBasicExpectedConfiguration()
    {
        return array(
            'default_vhost' => 'default',
            'delete_allowed' => false,
            'silent_failure' => false,
            'connections' => array(
                'default' => 'http://user:password@localhost:15672',
            ),
            'vhosts' => array(
                'default' => array(
                    'connection' => 'default',
                    'permissions' => array(
                        'user' => array(
                            'configure' => '.*',
                            'read' => '.*',
                            'write' => '.*'
                        ),
                    ),
                    'exchanges' => array(
                        'exchange.a' => array(
                            'name' => 'exchange.a',
                            'durable' => true,
                            'type' => 'topic'
                        ),
                    ),
                    'queues' => array(
                        'queue.a' => array(
                            'name' => 'queue.a',
                            'durable' => true,
                            'modulus' => null,
                            'arguments' => array(),
                            'bindings' => array(
                                array(
                                    'exchange' => 'exchange.a',
                                    'routing_key' => 'a.#',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    private function getBasicConfiguration()
    {
        return array(
            array(
                'connections' => array(
                    'default' => 'http://user:password@localhost:15672',
                ),
                'vhosts' => array(
                    'default' => array(
                        'permissions' => array(
                            'user' => NULL,
                        ),
                        'exchanges' => array(
                            'exchange.a' => NULL,
                        ),
                        'queues' => array(
                            'queue.a' => array(
                                'bindings' => array(
                                    array(
                                        'exchange' => 'exchange.a',
                                        'routing_key' => 'a.#',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )
        );
    }
}
