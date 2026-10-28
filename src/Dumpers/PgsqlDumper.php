<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Dumpers;

class PgsqlDumper extends DatabaseDumper
{
    public array $dumpTools = ['pg_dump'];

    protected string $enVariablePSW = 'PGPASSWORD';

    public function command(string $pathToDump, string $user, string $host, string $port, string $database, array $options = []): array
    {
        return [
            $this->findDumpBinary(),
            '-U', $user,
            '-h', $host,
            '-p', $port,
            ...$options,
            '-f'.$pathToDump,
            $database,
        ];
    }
}
