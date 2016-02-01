<?php

namespace Ola\RabbitMqAdminToolkitBundle\Command;

use Ola\RabbitMqAdminToolkitBundle\DependencyInjection\OlaRabbitMqAdminToolkitExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VhostDefineCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('rabbitmq:vhost:define')
            ->setDescription('Create or update a vhost')
            ->addArgument('vhost', InputArgument::OPTIONAL, 'Which vhost should be configured ?')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $vhost = $input->getArgument('vhost');
            if (empty($vhost)) {
                $vhost = $this->container->getParameter('ola_rabbit_mq_admin_toolkit.default_vhost');
            }

            $serviceName = sprintf(
                OlaRabbitMqAdminToolkitExtension::VHOST_MANAGER_SERVICE_TEMPLATE,
                $vhost
            );

            $output->write(sprintf('Looking for service [<info>%s</info>]...', $serviceName));

            if (!$this->container->has($serviceName)) {
                throw new \InvalidArgumentException(sprintf(
                    'No service found for vhost : "%s"',
                    $vhost
                ));
            }
            $output->writeln(' service found !');

            $vhostConfiguration = $this->container->get($serviceName);
            $vhostHandler = $this->container->get('ola_rabbit_mq_admin_toolkit.handler.vhost');
            $creation = !$vhostHandler->exists($vhostConfiguration);
            $output->write(sprintf('%s vhost configuration...', $creation ? 'Creating' : 'Updating'));

            $vhostHandler->define($vhostConfiguration);

            $output->writeln(sprintf(' %s !', $creation ? 'created' : 'updated'));
        } catch (\Exception $e) {
            if (!$this->container->getParameter('ola_rabbit_mq_admin_toolkit.silent_failure')) {
                throw $e;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
