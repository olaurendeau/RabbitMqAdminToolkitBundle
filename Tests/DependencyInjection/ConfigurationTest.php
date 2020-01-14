<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\DependencyInjection;

use Ola\RabbitMqAdminToolkitBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function test_basicConfiguration(): void
    {
        $this->assertEquals(
            $this->getBasicExpectedConfiguration(),
            $this->processConfiguration($this->getBasicConfiguration())
        );
    }

    public function test_overridingConfiguration(): void
    {
        $expectedConfiguration = $this->getBasicExpectedConfiguration();
        $expectedConfiguration['delete_allowed'] = true;
        $expectedConfiguration['vhosts']['default']['exchanges']['exchange.c'] = [
            'name' => 'exchange.c',
            'durable' => true,
            'type' => 'topic'
        ];

        $expectedConfiguration['vhosts']['default']['queues']['queue.a']['durable'] = false;
        $expectedConfiguration['vhosts']['default']['queues']['queue.a']['bindings'] = [
            [
                'exchange' => 'exchange.c',
                'routing_key' => 'b.#',
            ],
        ];

        $configs = $this->getBasicConfiguration();
        $configs[0]['vhosts']['default']['queues']['queue.b'] = [
            'bindings' => [
                [
                    'exchange' => 'exchange.a',
                    'routing_key' => 'c.#',
                ],
            ]
        ];
        $configs[] = [
            'delete_allowed' => true,
            'vhosts' => [
                'default' => [
                    'exchanges' => [
                        'exchange.c' => NULL,
                    ],
                    'queues' => [
                        'queue.b' => false,
                        'queue.a' => [
                            'durable' => false,
                            'bindings' => [
                                [
                                    'exchange' => 'exchange.c',
                                    'routing_key' => 'b.#',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            $expectedConfiguration,
            $this->processConfiguration($configs)
        );
    }

    public function test_queueArgumentsNormalization(): void
    {
        $expectedConfiguration = $this->getBasicExpectedConfiguration();

        $expectedConfiguration['vhosts']['default']['queues']['queue.a']['arguments'] = [
            'x-ha-policy' => 'all'
        ];

        $configs = $this->getBasicConfiguration();
        $configs[0]['vhosts']['default']['queues']['queue.a']['arguments'] = [
            'x-ha-policy' => 'all'
        ];

        $this->assertEquals(
            $expectedConfiguration,
            $this->processConfiguration($configs)
        );
    }

    private function processConfiguration($configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

    private function getBasicExpectedConfiguration(): array
    {
        return [
            'default_vhost' => 'default',
            'delete_allowed' => false,
            'silent_failure' => false,
            'connections' => [
                'default' => 'http://user:password@localhost:15672',
            ],
            'vhosts' => [
                'default' => [
                    'connection' => 'default',
                    'permissions' => [
                        'user' => [
                            'configure' => '.*',
                            'read' => '.*',
                            'write' => '.*'
                        ],
                    ],
                    'exchanges' => [
                        'exchange.a' => [
                            'name' => 'exchange.a',
                            'durable' => true,
                            'type' => 'topic'
                        ],
                    ],
                    'queues' => [
                        'queue.a' => [
                            'name' => 'queue.a',
                            'durable' => true,
                            'modulus' => null,
                            'arguments' => [],
                            'bindings' => [
                                [
                                    'exchange' => 'exchange.a',
                                    'routing_key' => 'a.#',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getBasicConfiguration(): array
    {
        return [
            [
                'connections' => [
                    'default' => 'http://user:password@localhost:15672',
                ],
                'vhosts' => [
                    'default' => [
                        'permissions' => [
                            'user' => NULL,
                        ],
                        'exchanges' => [
                            'exchange.a' => NULL,
                        ],
                        'queues' => [
                            'queue.a' => [
                                'bindings' => [
                                    [
                                        'exchange' => 'exchange.a',
                                        'routing_key' => 'a.#',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }
}
