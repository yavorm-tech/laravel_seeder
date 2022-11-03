<?php

namespace Eighty8\LaravelSeeder\Command;

use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputOption;

class SeedReset extends AbstractSeedMigratorCommand
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'seed:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback all database seeders';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        // Prepare the migrator.
        $this->prepareMigrator();

        // Reset the migrator.

        $pretend =  $this->input->getOption('pretend');
        if(!$pretend){
            $this->info('Removing seeded data for '.ucfirst($this->getEnvironment()).' environment...');
            $pretend = false;
            $this->migrator->reset($this->files, $pretend);
            $this->info('Removed seeded data for '.ucfirst($this->getEnvironment()).' environment');
        }else {
            $this->info('Pretending to remove seeded data for '.ucfirst($this->getEnvironment()).' environment...');
            $this->migrator->reset($this->files, $pretend);
            $this->info('Pretended to remove seeded data for '.ucfirst($this->getEnvironment()).' environment');
        }


    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['env', null, InputOption::VALUE_OPTIONAL, 'The environment to use for the seeders.'],
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
        ];
    }
}
