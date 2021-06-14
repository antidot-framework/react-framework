<?php

declare(strict_types=1);

namespace Antidot\React;

use Drift\Server\Console\RunServerCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

class RunServerCommandFactory
{
    public function __invoke(ContainerInterface $container): RunServerCommand
    {
        /** @var array<string, array> $globalConfig */
        $globalConfig = $container->get('config');
        /** @var array<string, string|int> $config */
        $config = $globalConfig['server'];

        $command = new RunServerCommand(dirname('./'), 'server:run');
        $command->setDescription('Run Drift HTTP Server');
        $definition = $command->getDefinition();

        $path = new InputArgument('path', InputArgument::OPTIONAL, $definition->getArgument('path')->getDescription());
        $path->setDefault(sprintf('%s:%s', $config['host'], $config['port']));
        $definition->setArguments([$path]);

        $adapter = $definition->getOption('adapter');
        $adapter->setDefault(DriftKernelAdapter::class);
        $staticFolder = $definition->getOption('static-folder');
        $staticFolder->setDefault((string)$config['static_folder']);
        $workers = $definition->getOption('workers');
        $workers->setDefault((string)$config['workers']);
        $concurrentRequests = $definition->getOption('concurrent-requests');
        $concurrentRequests->setDefault((string)$config['max_concurrency']);
        $bufferSize = $definition->getOption('request-body-buffer');
        $bufferSize->setDefault((string)$config['buffer_size']);

        return $command;
    }
}
