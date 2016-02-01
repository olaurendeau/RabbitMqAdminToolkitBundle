<?php

namespace Ola\RabbitMqAdminToolkitBundle\DependencyInjection;

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

        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'default_vhost'), $config['default_vhost']);

        $this->loadConnections($config['connections'], $container);
        $this->loadVhostManagers($config['vhosts'], $container, $config['delete_allowed']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param array $connections
     * @param ContainerBuilder $container
     */
    private function loadConnections(array $connections, ContainerBuilder $container)
    {
        foreach ($connections as $name => $uri) {

            $parsedUri = parse_url($uri);

            $definition = new Definition('RabbitMq\ManagementApi\Client', array(
                null,
                sprintf("%s://%s:%s", $parsedUri['scheme'], $parsedUri['host'], $parsedUri['port']),
                $parsedUri['user'],
                $parsedUri['pass']
            ));

            $container->setDefinition(sprintf(self::CONNECTION_SERVICE_TEMPLATE, $name), $definition);
        }
    }

    /**
     * @param array $vhosts
     * @param ContainerBuilder $container
     * @param $deleteAllowed
     */
    private function loadVhostManagers(array $vhosts, ContainerBuilder $container, $deleteAllowed)
    {
        foreach ($vhosts as $name => $vhost) {
            $definition = new Definition('Ola\RabbitMqAdminToolkitBundle\VhostConfiguration', array(
                new Reference(sprintf(self::CONNECTION_SERVICE_TEMPLATE, $vhost['connection'])),
                !empty($vhost['name']) ? $vhost['name'] : $name,
                $vhost,
                $deleteAllowed
            ));
            $container->setDefinition(sprintf(self::VHOST_MANAGER_SERVICE_TEMPLATE, $name), $definition);
        }
    }
}
