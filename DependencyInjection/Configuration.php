<?php

namespace Ola\RabbitMqAdminToolkitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ola_rabbit_mq_admin_toolkit');


        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_vhost')->defaultValue('default')->end()
                ->scalarNode('delete_allowed')->defaultFalse()->end()
                ->arrayNode('connections')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('vhosts')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('connection')->defaultValue('default')->end()
                            ->scalarNode('delete_allowed')->end()
                            ->arrayNode('permissions')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('configure')->defaultValue('.*')->end()
                                        ->scalarNode('read')->defaultValue('.*')->end()
                                        ->scalarNode('write')->defaultValue('.*')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('exchanges')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->scalarNode('type')->defaultValue('topic')->end()
                                        ->scalarNode('durable')->defaultTrue()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('queues')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->scalarNode('durable')->defaultTrue()->end()
                                        ->arrayNode('bindings')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('exchange')->end()
                                                    ->scalarNode('routing_key')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
