<?php

namespace Ola\RabbitMqAdminToolkitBundle\DependencyInjection;

use \Closure as Closure;
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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ola_rabbit_mq_admin_toolkit');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_vhost')->defaultValue('default')->end()
                ->scalarNode('delete_allowed')->defaultFalse()->end()
                ->scalarNode('silent_failure')->defaultFalse()->end()
                ->arrayNode('connections')
                    ->useAttributeAsKey('identifier')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('vhosts')
                    ->useAttributeAsKey('identifier')
                    ->canBeUnset(true)
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
    private function getPermissionsConfiguration(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('permissions');

        return $node
            ->useAttributeAsKey('identifier')
            ->canBeUnset(true)
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
    private function getExchangesConfiguration(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('exchanges');

        $this->appendNameNormalization($node);

        return $node
            ->useAttributeAsKey('identifier')
            ->canBeUnset(true)
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
    private function getQueuesConfiguration(): NodeDefinition
    {
        $modulusValidation = $this->getModulusValidation();

        $node = new ArrayNodeDefinition('queues');

        $this->appendNameNormalization($node);

        return $node
            ->useAttributeAsKey('identifier')
            ->validate()
                ->ifTrue($modulusValidation)
                ->thenInvalid('If queue.modulus is defined queue.name & at least one associated routing_key should contain a {modulus} part')
            ->end()
            ->prototype('array')
                ->canBeUnset(true)
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('durable')->defaultTrue()->end()
                    ->scalarNode('modulus')->defaultNull()->end()
                    ->append($this->getQueueArgumentsConfiguration())
                    ->arrayNode('bindings')
                        ->performNoDeepMerging()
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

    /**
     * Get modulus validation closure
     *
     * @return Closure
     */
    private function getModulusValidation(): Closure
    {
        return function($queues) {
            $hasModulus = function($string) {
                return strpos($string, QueueManager::MODULUS_PLACEHOLDER) !== false;
            };

            foreach ($queues as $name => $queue) {
                if (isset($queue['modulus']) && is_int($queue['modulus'])) {

                    if (!$hasModulus($queue['name'])) {
                        return true;
                    }

                    $bindingsHaveModulus = false;
                    foreach ($queue['bindings'] as $binding) {
                        if ($hasModulus($binding['routing_key'])) {
                            $bindingsHaveModulus = true;
                        }
                    }

                    if (!$bindingsHaveModulus) {
                        return true;
                    }
                }
            }
        };
    }

    /**
     * @param NodeDefinition $node
     *
     * @return NodeDefinition
     */
    private function appendNameNormalization(NodeDefinition $node): NodeDefinition
    {
        return $node
            ->beforeNormalization()
                ->ifTrue(function($array) {
                    foreach ($array as $item) {
                        if (false !== $item && !isset($item['name'])) {
                            return true;
                        }
                    }
                })
                ->then(function($array) {
                    foreach ($array as $name => $item) {
                        if (false !== $item && !isset($item['name'])) {
                            $array[$name]['name'] = $name;
                        }
                    }

                    return $array;
                })
            ->end();
    }

    /**
     * @return NodeDefinition
     */
    private function getQueueArgumentsConfiguration(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('arguments');

        return $node
            // We have to un-normalize arguments keys, as this is not configurable in SF 2.1
            ->useAttributeAsKey('identifier')
            ->beforeNormalization()
                ->ifTrue(function($arguments) {
                    foreach ($arguments as $k => $v) {
                        //Un-normalize keys
                        if (false !== strpos($k, '_')) {
                            return true;
                        }
                    }
                })
                ->then(function($arguments) {
                    foreach ($arguments as $k => $v) {
                        if (false !== strpos($k, '_')) {
                            $arguments[str_replace('_', '-', $k)] = $v;
                            unset($arguments[$k]);
                        }
                    }

                    return $arguments;
                })
            ->end()
            ->prototype('scalar')->end();
    }
}
