<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * SetupDatabaseCommand is responsible for automating the database setup process in the Symfony application.
 *
 * This includes dropping and recreating the database, running migrations, loading fixtures, and clearing the cache.
 */
#[AsCommand(
    name: 'app:setup-database',
    description: 'This command is responsible for automating the process of configuring and initializing the database for the Symfony application.',
)]
class SetupDatabaseCommand extends Command
{
    /**
     * The default name for the command.
     */
    protected static $defaultName = 'app:setup-database';

    /**
     * Executes the database setup commands.
     *
     * This method runs a series of commands to prepare the database for the Symfony application.
     * The following commands are executed in sequence:
     * 1. Drop the database if it exists.
     * 2. Create a new database.
     * 3. Run database migrations.
     * 4. Load fixtures into the database.
     * 5. Clear the cache.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The command exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Define the commands to be executed for setting up the database
        $commands = [
            ['php', 'bin/console', 'doctrine:database:drop', '--force', '--if-exists'],
            ['php', 'bin/console', 'doctrine:database:create'],
            ['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction'],
            ['php', 'bin/console', 'doctrine:fixtures:load', '--no-interaction'],
            ['php', 'bin/console', 'cache:clear'],
        ];

        // Loop through each command and execute it
        foreach ($commands as $command) {
            $process = new Process($command);
            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });

            // Check if the process was successful
            if (!$process->isSuccessful()) {
                $output->writeln('<error>Error when executing ' . implode(' ', $command) . '</error>');
                return Command::FAILURE;
            }
        }
        // Output success message if all commands executed successfully
        $output->writeln('<info>Database successfully created!</info>');
        return Command::SUCCESS;
    }
}
