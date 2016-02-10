<?php

namespace Ola\RabbitMqAdminToolkitBundle\Command;

use Ola\RabbitMqAdminToolkitBundle\DependencyInjection\OlaRabbitMqAdminToolkitExtension;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VhostDefineCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('rabbitmq:vhost:define')
            ->setDescription('Create or update a vhost')
            ->addArgument('vhost', InputArgument::OPTIONAL, 'Which vhost should be configured ?')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $vhostConfiguration = $this->getVhostConfiguration($input, $output);
            $vhostHandler = $this->getContainer()->get('ola_rabbit_mq_admin_toolkit.handler.vhost');
            $creation = !$vhostHandler->exists($vhostConfiguration);
            $output->write(sprintf('%s vhost configuration...', $creation ? 'Creating' : 'Updating'));

            $vhostHandler->define($vhostConfiguration);

            $output->writeln(sprintf(' %s !', $creation ? 'created' : 'updated'));
        } catch (\Exception $e) {
            if (!$this->getContainer()->getParameter('ola_rabbit_mq_admin_toolkit.silent_failure')) {
                throw $e;
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function getVhostConfiguration(InputInterface $input, OutputInterface $output)
    {
        $vhost = $input->getArgument('vhost');
        if (empty($vhost)) {
            $vhost = $this->getContainer()->getParameter('ola_rabbit_mq_admin_toolkit.default_vhost');
        }

        $serviceName = sprintf(
            OlaRabbitMqAdminToolkitExtension::VHOST_MANAGER_SERVICE_TEMPLATE,
            $vhost
        );

        $output->write(sprintf('Looking for service [<info>%s</info>]...', $serviceName));

        if (!$this->getContainer()->has($serviceName)) {
            throw new \InvalidArgumentException(sprintf(
                'No configuration service found for vhost : "%s"',
                $vhost
            ));
        }
        $output->writeln(' service found !');

        $this->getContainer()->get($serviceName);
    }
}
