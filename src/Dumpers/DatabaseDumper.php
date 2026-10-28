<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Dumpers;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;

abstract class DatabaseDumper
{
    abstract protected array $dumpTools {
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
        array $options = [],
    ): array;

    protected function findDumpBinary(): string
    {
        $finder = new ExecutableFinder;

        foreach ($this->dumpTools as $dumpTool) {
            $path = $finder->find($dumpTool) ?? $finder->find(
                $dumpTool,
                null,
                [config('database-backup.path_to_dump_tool')],
            );

            if ($path) {
                break;
            }
        }

        if (! $path) {
            throw new RuntimeException(
                'The backup package could not find the dump tool on your system to do backup:'
                .implode(', ', $this->dumpTools).'.',
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
        array $options = [],
    ): ProcessResult {
        return Process::env([$this->enVariablePSW => $password])
            ->run($this->command($pathToDump, $user, $host, $port, $database, $options))
            ->throw();
    }
}
