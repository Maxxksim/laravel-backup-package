<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Maksym\LaravelBackupPackage\BackupPackageProvider;
use Maksym\LaravelBackupPackage\Services\BackupService;
use Maksym\LaravelBackupPackage\Tests\Faker\FakeDumper;
use Orchestra\Testbench\TestCase as Orchestra;


abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BackupPackageProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'test_db_driver',
            'database' => 'test_db',
            'username' => 'test_username',
            'password' => 'test_password',
            'host' => 'test_host',
            'port' => 'test_port'
        ]);
    }

    function makeServiceWithFakeDumper(): BackupService
    {
        return new BackupService([
            'test_db_driver' => new FakeDumper()
        ]);
    }

    function makeFakeBackups(int $number, string $directoryForBackups, string $database): Collection
    {
        $backups = [];

        for ($i = 0; $i < $number; $i++) {
            $datetime = now()->subDays($number - $i);
            $path = $this->generateFakePathToDump($directoryForBackups, $database, $datetime);
            $backups[] = ['filename' => basename($path), 'datetime' => $this->getDatetimeFromFilename(basename($path))];
            File::put($path, 'test backup');
        }

        return collect($backups);
    }

    function generateFakePathToDump(string $directoryForBackups, string $database, Carbon $datetime): string
    {
        return rtrim($directoryForBackups, '/') .
            '/' .
            $database .
            '_' .
            $datetime->format('Y-m-d_His') .
            '.sql';
    }

    function getDatetimeFromFilename(string $filename): Carbon
    {
        preg_match('/\d{4}-\d{2}-\d{2}_\d{6}/', $filename, $match);
        return Carbon::createFromFormat('Y-m-d_His', $match[0]);
    }
}
