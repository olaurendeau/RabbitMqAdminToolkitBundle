<?php

namespace Ola\RabbitMqAdminToolkitBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
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

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'vhost_list'), array_keys($config['vhosts']));
        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'silent_failure'), $config['silent_failure']);
        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'connections'), $config['connections']);
        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'vhosts'), $config['vhosts']);
        $container->setParameter(sprintf(self::PARAMETER_TEMPLATE, 'delete_allowed'), $config['delete_allowed']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
