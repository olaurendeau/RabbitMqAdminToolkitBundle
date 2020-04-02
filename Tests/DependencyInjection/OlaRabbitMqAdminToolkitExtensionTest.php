<?php

namespace Ola\RabbitMqAdminToolkitBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Ola\RabbitMqAdminToolkitBundle\DependencyInjection\OlaRabbitMqAdminToolkitExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class OlaRabbitMqAdminToolkitExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new OlaRabbitMqAdminToolkitExtension()
        ];
    }

    public function test_load_successfull()
    {
        $connections = [
            'default' => 'http://user:password@localhost',
        ];
        $vhosts = [
            'test' => [
                'name' => '/test',
                'connection' => 'default',
                'permissions' => [
                    'lafourchette' => NULL,
                ],
                'exchanges' => [
                    'exchange.a' => NULL,
                    'exchange.b' => [
                        'type' => 'direct',
                    ],
                    'exchange.c' => NULL,
                ],
                'queues' => [
                    'queue.a.sharded' => [
                        'name' => 'queue.a.{modulus}',
                        'modulus' => 10,
                        'bindings' => [
                            [
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.{modulus}.#',
                            ],
                            [
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.#',
                            ],
                        ],
                    ],
                    'queue.b.{modulus}' => [
                        'bindings' => [
                            [
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.#',
                            ],
                            [
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.{modulus}.#',
                            ],
                            [
                                'exchange' => 'exchange.c',
                                'routing_key' => 'c.#',
                            ],
                        ],
                    ],
                    'queue.c' => [
                        'bindings' => [
                            [
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.#',
                            ],
                            [
                                'exchange' => 'exchange.c',
                                'routing_key' => 'c.#',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->load([
            'delete_allowed' => true,
            'connections' => $connections,
            'vhosts' => $vhosts,
        ]);

        // todo assert new parameters are created
        $this->assertContainerBuilderHasParameter('ola_rabbit_mq_admin_toolkit.vhost_list');
        $this->assertContainerBuilderHasParameter('ola_rabbit_mq_admin_toolkit.vhosts');
        $this->assertContainerBuilderHasParameter('ola_rabbit_mq_admin_toolkit.connections');
        $this->assertContainerBuilderHasParameter('ola_rabbit_mq_admin_toolkit.delete_allowed');
    }

    public function dataProvider_load_failBecauseModulusIsImproperlyDefined(): array
    {
        return [
            // Should have {modulus} in at least one routing_key
            [
                [
                    'queue.a.{modulus}' => [
                        'modulus' => 10,
                        'bindings' => [
                            [
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.#',
                            ],
                            [
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.#',
                            ],
                        ],
                    ],
                ]
            ],
            // Should have {modulus} in at least one routing_key
            [
                [
                    'queue.a.sharded' => [
                        'name' => 'queue.a.{modulus}',
                        'modulus' => 10,
                        'bindings' => [
                            [
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.#',
                            ],
                            [
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.#',
                            ],
                        ],
                    ],
                ]
            ],
            // Should have {modulus} in queue name
            [
                [
                    'queue.a.sharded' => [
                        'modulus' => 10,
                        'bindings' => [
                            [
                                'exchange' => 'exchange.a',
                                'routing_key' => 'a.{modulus}',
                            ],
                            [
                                'exchange' => 'exchange.b',
                                'routing_key' => 'b.#',
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_load_failBecauseModulusIsImproperlyDefined
     */
    public function test_load_failBecauseModulusIsImproperlyDefined(array $queues): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load([
            'delete_allowed' => true,
            'connections' => [
                'default' => 'http://user:password@localhost:15672',
            ],
            'vhosts' => [
                'test' => [
                    'name' => '/test',
                    'connection' => 'default',
                    'permissions' => [
                        'lafourchette' => NULL,
                    ],
                    'exchanges' => [
                        'exchange.a' => NULL,
                        'exchange.b' => [
                            'type' => 'direct',
                        ],
                    ],
                    'queues' => $queues
                ],
            ],
        ]);
    }
}
