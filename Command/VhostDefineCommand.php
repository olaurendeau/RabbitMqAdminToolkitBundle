<?php

namespace Ola\RabbitMqAdminToolkitBundle\Command;

use Ola\RabbitMqAdminToolkitBundle\DependencyInjection\OlaRabbitMqAdminToolkitExtension;
use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            $this->comment($input, $output, sprintf(
                'Define rabbitmq <info>%s</info> vhost configuration',
                $this->getVhost($input)
            ));

            $vhostConfiguration = $this->getVhostConfiguration($input, $output);
            $vhostHandler = $this->getContainer()->get('ola_rabbit_mq_admin_toolkit.handler.vhost');
            $creation = !$vhostHandler->exists($vhostConfiguration);

            $vhostHandler->define($vhostConfiguration);

            $this->success($input, $output, sprintf(
                'Rabbitmq "%s" vhost configuration successfully %s !',
                $this->getVhost($input),
                $creation ? 'created' : 'updated'
            ));
        } catch (\Exception $e) {
            if (!$this->getContainer()->getParameter('ola_rabbit_mq_admin_toolkit.silent_failure')) {
                throw $e;
            }
        }
    }

    /**
     * Retrieve vhost's name to process
     *
     * @param InputInterface $input
     *
     * @return string
     */
    private function getVhost(InputInterface $input)
    {
        $vhost = $input->getArgument('vhost');
        if (empty($vhost)) {
            $vhost = $this->getContainer()->getParameter('ola_rabbit_mq_admin_toolkit.default_vhost');
        }

        return $vhost;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return VhostConfiguration
     *
     * @throws \InvalidArgumentException
     */
    private function getVhostConfiguration(InputInterface $input, OutputInterface $output)
    {
        $vhost = $this->getVhost($input);

        $serviceName = sprintf(
            OlaRabbitMqAdminToolkitExtension::VHOST_MANAGER_SERVICE_TEMPLATE,
            $vhost
        );

        if (!$this->getContainer()->has($serviceName)) {
            throw new \InvalidArgumentException(sprintf(
                'No configuration service found for vhost : "%s"',
                $vhost
            ));
        }

        return $this->getContainer()->get($serviceName);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     */
    private function comment(InputInterface $input, OutputInterface $output, $message)
    {
        $io = $this->getIO($input, $output);

        if (null !== $io && method_exists($io, 'comment')) {
            $io->comment($message);
        } else {
            $output->writeln(sprintf('<comment>%s</comment>', $message));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     */
    private function success(InputInterface $input, OutputInterface $output, $message)
    {
        $io = $this->getIO($input, $output);

        if (null !== $io) {
            $io->success($message);
        } else {
            $output->writeln(sprintf('<info>%s</info>', $message));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|\Symfony\Component\Console\Style\SymfonyStyle
     */
    private function getIO(InputInterface $input, OutputInterface $output)
    {
        if (class_exists('Symfony\Component\Console\Style\SymfonyStyle')) {
            return new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);
        }

        return null;
    }
}
