<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage;

use Illuminate\Support\ServiceProvider;
use Maksym\LaravelBackupPackage\Console\Commands\BackupCommand;
use Maksym\LaravelBackupPackage\Dumpers\MariadbDumper;
use Maksym\LaravelBackupPackage\Dumpers\MysqlDumper;
use Maksym\LaravelBackupPackage\Dumpers\PgsqlDumper;
use Maksym\LaravelBackupPackage\Services\BackupService;

class BackupPackageProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/database-backup.php', 'database-backup');
        $this->app->singleton(BackupService::class, fn($app) => new BackupService(
            dumpers: [
                'mysql' => new MysqlDumper(),
                'pgsql' => new PgsqlDumper(),
                'mariadb' => new MariadbDumper(),
            ],
        ));
    }

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/database-backup.php' => config_path('database-backup.php'),
            ],
            'database-backup-config',
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackupCommand::class,
            ]);
        }
    }
}
