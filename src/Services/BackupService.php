<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Maksym\LaravelBackupPackage\Dumpers\DatabaseDumper;
use Maksym\LaravelBackupPackage\Dumpers\MariadbDumper;
use Maksym\LaravelBackupPackage\Dumpers\MysqlDumper;
use Maksym\LaravelBackupPackage\Dumpers\PgsqlDumper;

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
    private int $maxCopies;

    private const int DEFAULT_MAX_COPIES = 1;

    public function __construct(private readonly array $dumpers)
    {
        $this->setSettings();
    }

    private function setSettings(): void
    {
        $defaultConnection = config('database.default');
        $driver = config("database.connections.$defaultConnection.driver");

        if (!$this->checkDumperExists($driver)) {
            throw new InvalidArgumentException(
                "The backup package doesn't support the type of database: $driver",
            );
        }

        $this->driver = $driver;
        $this->pathToDumpDir = config('database-backup.path_to_backup');
        $this->options = config("database-backup.$defaultConnection.options", []);
        $this->host = config("database.connections.$defaultConnection.host");
        $this->port = config("database.connections.$defaultConnection.port");
        $this->database = config("database.connections.$defaultConnection.database");
        $this->user = config("database.connections.$defaultConnection.username");
        $this->password = config("database.connections.$defaultConnection.password");
        $this->maxCopies = config('database-backup.max_copies');
    }

    private function checkDumperExists(string $driver): bool
    {
        if (!array_key_exists($driver, $this->dumpers)) {
            return false;
        }

        return true;
    }

    public function startDump(?int $maxCopies = null): bool
    {
        File::ensureDirectoryExists($this->pathToDumpDir);

        $maxCopies = $this->getMaxCopies($maxCopies);

        /** @var DatabaseDumper $dumper */
        $dumper = $this->dumpers[$this->driver];

        $this->dump($dumper);
        $backups = $this->getSortedBackups();
        $countBackups = count($backups);

        if ($countBackups > $maxCopies) {
            $filesToDelete = $backups->take($countBackups - $maxCopies);

            foreach ($filesToDelete as $filename => $date) {
                File::delete($this->pathToDumpDir . $filename);
            }
        }

        return true;
    }

    private function generatePathToDump(): string
    {
        return rtrim($this->pathToDumpDir, '/') .
            '/' .
            $this->database .
            '_' .
            now()->format('Y-m-d_His') .
            '.sql';
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

    private function getMaxCopies(?int $receivedMax = null): int
    {
        if (!is_null($receivedMax)) {

            if ($receivedMax <= 0) {
                return self::DEFAULT_MAX_COPIES;
            }

            return $receivedMax;
        }

        if ($this->maxCopies <= 0) {
            return self::DEFAULT_MAX_COPIES;
        }

        return $this->maxCopies;
    }
}
