<?php

namespace Eighty8\LaravelSeeder;

use Illuminate\Support\Composer;
use Illuminate\Support\ServiceProvider;
use Eighty8\LaravelSeeder\Command\SeedRun;
use Eighty8\LaravelSeeder\Command\SeedMake;
use Eighty8\LaravelSeeder\Command\SeedReset;
use Eighty8\LaravelSeeder\Command\SeedStatus;
use Eighty8\LaravelSeeder\Command\SeedInstall;
use Eighty8\LaravelSeeder\Command\SeedRefresh;
use Eighty8\LaravelSeeder\Command\SeedRollback;
use Eighty8\LaravelSeeder\Migration\SeederMigrator;
use Eighty8\LaravelSeeder\Repository\SeederRepository;
use Eighty8\LaravelSeeder\Migration\SeederMigrationCreator;
use Eighty8\LaravelSeeder\Migration\SeederMigratorInterface;
use Eighty8\LaravelSeeder\Repository\SeederRepositoryInterface;

class SeederServiceProvider extends ServiceProvider
{
    const SEEDERS_CONFIG_PATH = __DIR__ . '/../../config/seeders.php';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    protected $commands = [];

    /**
     * Boots the service provider.
     */
    public function boot(): void
    {
        $this->publishes([self::SEEDERS_CONFIG_PATH => base_path('config/seeders.php')]);

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(self::SEEDERS_CONFIG_PATH, 'seeders');

        $this->registerRepository();

        $this->registerMigrator();

        $this->registerCommands();
    }

    /**
     * Register the SeederRepository.
     */
    private function registerRepository(): void
    {
        $this->app->singleton(SeederRepository::class, function ($app) {
            return new SeederRepository($app['db'], config('seeders.table'));
        });

        $this->app->bind(SeederRepositoryInterface::class, function ($app) {
            return $app[SeederRepository::class];
        });
    }

    /**
     * Register the SeederMigrator.
     */
    private function registerMigrator(): void
    {
        $this->app->singleton(SeederMigrator::class, function ($app) {
            return new SeederMigrator($app[SeederRepositoryInterface::class], $app['db'], $app['files']);
        });

        $this->app->bind(SeederMigratorInterface::class, function ($app) {
            return $app[SeederMigrator::class];
        });

        $this->app->singleton(SeederMigrationCreator::class, function ($app) {
            return new SeederMigrationCreator($app['files'], $app->basePath('stubs'));
        });
    }

    /**
     * Registers the Seeder Artisan commands.
     */
    private function registerCommands(): void
    {

        if (\in_array(SeedInstall::class, config('seeders.commands'))) {
            $this->app->bind(SeedInstall::class, function ($app) {
                return new SeedInstall($app[SeederRepositoryInterface::class]);
            });

            array_push($this->commands, SeedInstall::class);
        }


        if (\in_array(SeedMake::class, config('seeders.commands'))) {
            $this->app->bind(SeedMake::class, function ($app) {
                return new SeedMake($app[SeederMigrationCreator::class], $app[Composer::class]);
            });

            array_push($this->commands, SeedMake::class);
        }

        if (\in_array(SeedRefresh::class, config('seeders.commands'))) {
            $this->app->bind(SeedRefresh::class, function () {
                return new SeedRefresh();
            });

            array_push($this->commands, SeedRefresh::class);
        }

        if (\in_array(SeedReset::class, config('seeders.commands'))) {
            $this->app->bind(SeedReset::class, function ($app) {
                return new SeedReset($app[SeederMigrator::class]);
            });

            array_push($this->commands, SeedReset::class);
        }

        if (\in_array(SeedRollback::class, config('seeders.commands'))) {
            $this->app->bind(SeedRollback::class, function ($app) {
                return new SeedRollback($app[SeederMigrator::class]);
            });

            array_push($this->commands, SeedRollback::class);
        }

        if (\in_array(SeedRun::class, config('seeders.commands'))) {
            $this->app->bind(SeedRun::class, function ($app) {
                return new SeedRun($app[SeederMigrator::class]);
            });

            array_push($this->commands, SeedRun::class);
        }

        if (\in_array(SeedStatus::class, config('seeders.commands'))) {
            $this->app->bind(SeedStatus::class, function ($app) {
                return new SeedStatus($app[SeederMigrator::class]);
            });

            array_push($this->commands, SeedStatus::class);
        }

        $this->commands($this->commands);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        $providers = \array_merge($this->commands,
        [
            SeederRepository::class,
            SeederRepositoryInterface::class,
            SeederMigrator::class,
            SeederMigratorInterface::class,
            SeederMigrationCreator::class,
        ]);

        return $providers;
    }
}
