<?php

namespace Ola\RabbitMqAdminToolkitBundle\Command;

use Ola\RabbitMqAdminToolkitBundle\VhostConfiguration;
use Ola\RabbitMqAdminToolkitBundle\VhostConfigurationFactory;
use Ola\RabbitMqAdminToolkitBundle\VhostHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class VhostDefineCommand extends Command
{
    protected static $defaultName = 'rabbitmq:vhost:define';

    private VhostConfigurationFactory $vhostConfigurationFactory;

    private array $vhostList;

    private VhostHandler $vhostHandler;

    private bool $silentFailure;

    public function __construct(
        VhostConfigurationFactory $vhostConfigurationFactory,
        array $vhostList,
        VhostHandler $vhostHandler,
        bool $silentFailure
    ) {
        parent::__construct();

        $this->vhostConfigurationFactory = $vhostConfigurationFactory;
        $this->vhostList = $vhostList;
        $this->vhostHandler = $vhostHandler;
        $this->silentFailure = $silentFailure;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Create or update a vhost')
            ->addArgument('vhost', InputArgument::OPTIONAL, 'Which vhost should be configured ?')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vhostList = $this->getVhostList($input);

        foreach ($vhostList as $vhost) {
            try {
                $this->comment($input, $output, sprintf(
                    'Define rabbitmq <info>%s</info> vhost configuration',
                    $vhost
                ));

                $vhostConfiguration = $this->getVhostConfiguration($vhost);
                $this->vhostHandler->define($vhostConfiguration);

                $this->success($input, $output, sprintf(
                    'Rabbitmq "%s" vhost configuration successfully %s !',
                    $vhost,
                    !$this->vhostHandler->exists($vhostConfiguration) ? 'created' : 'updated'
                ));
            } catch (\Exception $e) {
                if (!$this->silentFailure) {
                    throw $e;
                }
            }
        }

        return 0;
    }

    /**
     * Return Vhosts to process
     *
     * @param InputInterface $input
     *
     * @return array
     */
    private function getVhostList(InputInterface $input): array
    {
        $inputVhost = $input->getArgument('vhost');

        return empty($inputVhost) ? $this->vhostList : [$inputVhost];
    }

    /**
     * @param string $vhost
     *
     * @return VhostConfiguration
     *
     * @throws \InvalidArgumentException
     */
    private function getVhostConfiguration(string $vhost): VhostConfiguration
    {
        return $this->vhostConfigurationFactory->getVhostConfiguration($vhost);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $message
     */
    private function comment(InputInterface $input, OutputInterface $output, string $message): void
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
    private function success(InputInterface $input, OutputInterface $output, string $message): void
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
     *
     * @return null|\Symfony\Component\Console\Style\SymfonyStyle
     */
    private function getIO(InputInterface $input, OutputInterface $output): ?SymfonyStyle
    {
        if (class_exists(SymfonyStyle::class)) {
            return new SymfonyStyle($input, $output);
        }

        return null;
    }
}
