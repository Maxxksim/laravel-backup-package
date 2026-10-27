<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Dumpers;


class MysqlDumper extends DatabaseDumper
{
    public string $dumpTool = 'mysqldump';
    protected string $enVariablePSW = 'MYSQL_PWD';

    public function command(string $pathToDump, string $user, string $password, string $host, string $port, string $database, array $options): array
    {
        return [
            $this->findDumpBinary(),
            '-u', $user,
            '-h', $host,
            '-P', $port,
            ...$options,
            '--result-file=' . $pathToDump,
            $database,
        ];
    }
}