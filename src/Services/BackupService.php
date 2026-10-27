<?php

namespace Maksym\LaravelBackupPackage\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

use Maksym\LaravelBackupPackage\Dumpers\DatabaseDumper;
use Maksym\LaravelBackupPackage\Dumpers\MysqlDumper;

class BackupService
{
    private string $pathToDumpDir;
    private string $driver;
    private array $options;
    private string $host;
    private string $port;
    private string $database;
    private string $user;
    private string $password;

    private array $dumpers = [
        'mysql' => MysqlDumper::class,
    ];

    public function __construct()
    {
        $this->pathToDumpDir = config('database-backup.path_to_backup');
        $this->driver = config('database.default');
        $this->options = config("database-backup.$this->driver.options");
        $this->host = config("database.connections.$this->driver.host");
        $this->port = config("database.connections.$this->driver.port");
        $this->database = config("database.connections.$this->driver.database");
        $this->user = config("database.connections.$this->driver.username");
        $this->password = config("database.connections.$this->driver.password");
    }


    public function startDump(int $max): string
    {
        if (!array_key_exists($this->driver, $this->dumpers)) {
            throw new InvalidArgumentException("The package doesn't support the type of database: $this->driver");
        }

        /** @var DatabaseDumper $dumper */
        $dumper = app($this->dumpers[$this->driver]);

        File::ensureDirectoryExists($this->pathToDumpDir);

        try {

            $backups = $this->getSortedBackups();

            $countBackups = count($backups);

            if ($countBackups < $max) {
                $this->dump($dumper);

            } elseif ($countBackups === $max) {
                $fileToDelete = $backups->keys()->first();
                File::delete($this->pathToDumpDir . $fileToDelete);
                $this->dump($dumper);

            } else {
                $countToKeep = $countBackups - $max;
                $filesToDelete = $backups->take($countToKeep);

                foreach ($filesToDelete as $filename => $date) {
                    File::delete($this->pathToDumpDir . $filename);
                }
                $this->dump($dumper);
            }
        } catch (Exception $e) {
            throw $e;
        }

        return 'Database backups have created successfully.';
    }

    private function generatePathToDump(): string
    {
        return $this->pathToDumpDir . $this->database . '_' . now()->format('Y-m-d_His') . '.sql';
    }

    private function getSortedBackups(): Collection
    {
        $files = File::files($this->pathToDumpDir);

        $backups = [];

        foreach ($files as $file) {
            $filename = $file->getBasename('.sql');

            if (!str_starts_with($filename, $this->database . '_')) {
                continue;
            }
            $offset = mb_strlen($this->database . '_');
            $datetimeString = substr($filename, $offset);
            $datetime = Carbon::createFromFormat('Y-m-d_His', $datetimeString);

            $backups[$file->getBasename()] = ['datetime' => $datetime];
        }

        return collect($backups)->sortBy('datetime');
    }

    private function dump(DatabaseDumper $dumper): void
    {
        $dumper->runDumpCommand(
            $this->generatePathToDump(),
            $this->user,
            $this->password,
            $this->host,
            $this->port,
            $this->database,
            $this->options,
        );
    }
}