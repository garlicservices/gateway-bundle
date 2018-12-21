<?php

namespace Garlic\Gateway\Command;

use Garlic\Bus\Service\CommunicatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;

class HealthCheckHeartBeatCommand extends Command
{
    protected static $defaultName = 'healthcheck:emit';

    protected function configure()
    {
        $this->setDescription('Emit a health check reaction for all available microservices.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Container $container */
        $container = $this->getApplication()->getKernel()->getContainer();
        $container->get(CommunicatorService::class)
            ->serviceDiscoveryEvent(['date' => microtime(true)]);

        $io->success('Heart beat successfully emitted!');
    }
}
