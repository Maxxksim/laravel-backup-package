<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Dumpers;


class MysqlDumper extends DatabaseDumper
{
    const string DUMP_TOOL = 'mysqldump';

    public function command(string $password): array
    {
        return [
            $this->findDumpBinary(),
            '-u',
            'root',
            "-p{$password}",
            $this->options,
            $this->database,
        ];
    }
}