<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Dumpers;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\ExecutableFinder;

abstract class DatabaseDumper
{
    abstract protected string $dumpTool {
        get;
        set;
    }

    abstract protected function command(
        string $pathToDump,
        string $user,
        string $password,
        string $host,
        string $port,
        string $database,
        array  $options,
    ): array;

    protected function findDumpBinary(): string
    {
        return new ExecutableFinder()->find($this->dumpTool);
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
        return new Process()->run($this->command($pathToDump, $user, $password, $host, $port, $database, $options))->throw();
    }
}