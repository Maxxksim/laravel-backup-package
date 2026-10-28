<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Dumpers;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\ExecutableFinder;
use RuntimeException;

abstract class DatabaseDumper
{
    abstract protected string $dumpTool {
        get;
        set;
    }

    abstract protected string $enVariablePSW {
        get;
        set;
    }

    abstract protected function command(
        string $pathToDump,
        string $user,
        string $host,
        string $port,
        string $database,
        array  $options,
    ): array;

    protected function findDumpBinary(): string
    {
        $finder = new ExecutableFinder();
        $path = $finder->find($this->dumpTool) ?? $finder->find(
            'mysqldump',
            null,
            [config('database-backup.path_to_dump_tool')]
        );

        if (!$path) {
            throw new RuntimeException(
                "The backup package couldn't find the dump tool on your system to do backup: $this->dumpTool",
            );
        }

        return $path;
    }

    public function runDumpCommand(
        string $pathToDump,
        string $user,
        string $password,
        string $host,
        string $port,
        string $database,
        array  $options,
    ): ProcessResult
    {
        return Process::
        env([$this->enVariablePSW => $password])
            ->run($this->command($pathToDump, $user, $host, $port, $database, $options))
            ->throw();
    }
}