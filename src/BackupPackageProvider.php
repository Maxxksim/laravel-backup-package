<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage;

use Illuminate\Support\ServiceProvider;
use Maksym\LaravelBackupPackage\Console\Commands\BackupCommand;

class BackupPackageProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/database-backup.php', 'database-backup');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/database-backup.php' => config_path('database-backup.php')
        ], 'database-backup-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackupCommand::class,
            ]);
        }
    }
}
