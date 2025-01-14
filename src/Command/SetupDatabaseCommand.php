<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:setup-database',
    description: 'This command is responsible for automating the process of configuring and initializing the database for the Symfony application.',
)]
class SetupDatabaseCommand extends Command
{

    protected static $defaultName = 'app:setup-database';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commands = [
            ['php', 'bin/console', 'doctrine:database:drop', '--force'], // Remove a base de dados, se necessário
            ['php', 'bin/console', 'doctrine:database:create'],
            ['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction'],
            ['php', 'bin/console', 'doctrine:fixtures:load', '--no-interaction'],
            ['php', 'bin/console', 'cache:clear'],
        ];

        foreach ($commands as $command) {
            $process = new Process($command);
            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $output->writeln('<error>Error when executing ' . implode(' ', $command) . '</error>');
                return Command::FAILURE;
            }
        }

        $output->writeln('<info>Database successfully created!</info>');
        return Command::SUCCESS;
    }
}
