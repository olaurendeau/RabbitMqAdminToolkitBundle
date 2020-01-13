<?php

namespace Ola\RabbitMqAdminToolkitBundle\DependencyInjection;

use Ola\RabbitMqAdminToolkitBundle\ClientFactory;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use RabbitMq\ManagementApi\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OlaRabbitMqAdminToolkitExtension extends Extension
{
    const PARAMETER_TEMPLATE = 'ola_rabbit_mq_admin_toolkit.%s';
    const CONNECTION_SERVICE_TEMPLATE = 'ola_rabbit_mq_admin_toolkit.connection.%s';
    const VHOST_MANAGER_SERVICE_TEMPLATE = 'ola_rabbit_mq_admin_toolkit.configuration.%s';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'vhost_list'), array_keys($config['vhosts']));
        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'silent_failure'), $config['silent_failure']);

        $this->loadConnections($config['connections'], $container);
        $this->loadVhostManagers($config['vhosts'], $container, $config['delete_allowed']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * @param array $connections
     * @param ContainerBuilder $container
     */
    private function loadConnections(array $connections, ContainerBuilder $container): void
    {
        foreach ($connections as $name => $uri) {
            $parsedUri = parse_url($uri);

            $definition = new Definition(Client::class, [
                $parsedUri['scheme'],
                $parsedUri['host'],
                $parsedUri['user'],
                $parsedUri['pass'],
                $parsedUri['port'] ?? 80
            ]);

            $definition->setFactory([ClientFactory::class, 'getClient']); // necessary to have the exception handling
            $definition->setPublic(true);
            $container->setDefinition(sprintf(self::CONNECTION_SERVICE_TEMPLATE, $name), $definition);
        }
    }

    /**
     * @param array $vhosts
     * @param ContainerBuilder $container
     * @param bool $deleteAllowed
     */
    private function loadVhostManagers(array $vhosts, ContainerBuilder $container, bool $deleteAllowed): void
    {
        foreach ($vhosts as $name => $vhost) {
            $definition = new Definition(VhostConfiguration::class, [
                new Reference(sprintf(self::CONNECTION_SERVICE_TEMPLATE, $vhost['connection'])),
                !empty($vhost['name']) ? $vhost['name'] : $name,
                $vhost,
                $deleteAllowed
            ]);
            $definition->setPublic(true);
            $container->setDefinition(sprintf(self::VHOST_MANAGER_SERVICE_TEMPLATE, $name), $definition);
        }
    }
}
