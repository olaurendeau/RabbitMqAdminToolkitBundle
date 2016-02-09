<?php

namespace Ola\RabbitMqAdminToolkitBundle\DependencyInjection;

use Ola\RabbitMqAdminToolkitBundle\Manager\QueueManager;
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

        $modulusValidation = function($v) {
            foreach ($v as $name => $queue) {
                if (isset($queue['modulus']) && is_int($queue['modulus'])) {
                    if (strpos($name, QueueManager::MODULUS_PLACEHOLDER) === false) {
                        if (isset($queue['name'])) {
                            if (strpos($queue['name'], QueueManager::MODULUS_PLACEHOLDER) === false) {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    }

                    $hasModulus = false;
                    foreach ($queue['bindings'] as $binding) {
                        if (strpos($binding['routing_key'], QueueManager::MODULUS_PLACEHOLDER) !== false) {
                            $hasModulus = true;
                        }
                    }

                    if (!$hasModulus) {
                        return true;
                    }
                }
            }
        };

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_vhost')->defaultValue('default')->end()
                ->scalarNode('delete_allowed')->defaultFalse()->end()
                ->scalarNode('silent_failure')->defaultFalse()->end()
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
                                ->validate()
                                    ->ifTrue($modulusValidation)
                                    ->thenInvalid('If queue.modulus is defined queue.name & at least one associated routing_key should contain a {modulus} part')
                                ->end()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->scalarNode('durable')->defaultTrue()->end()
                                        ->scalarNode('modulus')
                                            ->defaultNull()
                                        ->end()
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
