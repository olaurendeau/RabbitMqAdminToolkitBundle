<?php

namespace Ola\RabbitMqAdminToolkitBundle\DependencyInjection;

use Ola\RabbitMqAdminToolkitBundle\Manager\QueueManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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
                            ->append($this->getPermissionsConfiguration())
                            ->append($this->getExchangesConfiguration())
                            ->append($this->getQueuesConfiguration())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    private function getPermissionsConfiguration()
    {
        $node = new ArrayNodeDefinition('permissions');

        return $node
            ->prototype('array')
                ->children()
                    ->scalarNode('configure')->defaultValue('.*')->end()
                    ->scalarNode('read')->defaultValue('.*')->end()
                    ->scalarNode('write')->defaultValue('.*')->end()
                ->end()
            ->end();
    }

    /**
     * @return NodeDefinition
     */
    private function getExchangesConfiguration()
    {
        $node = new ArrayNodeDefinition('exchanges');

        return $node
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('type')->defaultValue('topic')->end()
                    ->scalarNode('durable')->defaultTrue()->end()
                ->end()
            ->end();
    }

    /**
     * @return NodeDefinition
     */
    private function getQueuesConfiguration()
    {
        $modulusValidation = function($v) {

            $hasModulus = function($string) {
                return strpos($string, QueueManager::MODULUS_PLACEHOLDER) !== false;
            };

            foreach ($v as $name => $queue) {
                if (isset($queue['modulus']) && is_int($queue['modulus'])) {
                    if (!$hasModulus($name) || (!$hasModulus($name) && isset($queue['name']) && !$hasModulus($queue['name']))) {
                        return true;
                    }

                    $bindingsHaveModulus = false;
                    foreach ($queue['bindings'] as $binding) {
                        $bindingsHaveModulus = $hasModulus($binding['routing_key']);
                    }

                    if (!$bindingsHaveModulus) {
                        return true;
                    }
                }
            }
        };

        $node = new ArrayNodeDefinition('queues');

        return $node
            ->validate()
                ->ifTrue($modulusValidation)
                ->thenInvalid('If queue.modulus is defined queue.name & at least one associated routing_key should contain a {modulus} part')
            ->end()
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('durable')->defaultTrue()->end()
                    ->scalarNode('modulus')->defaultNull()->end()
                    ->arrayNode('bindings')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('exchange')->end()
                                ->scalarNode('routing_key')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
