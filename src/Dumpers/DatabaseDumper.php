<?php

namespace Maksym\LaravelBackupPackage\Dumpers;

use AllowDynamicProperties;
use http\Encoding\Stream\Inflate;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;


class DatabaseDumper
{
    protected string $driver;
    protected string $database;

    protected string $options;
    protected string $password;

    public function __construct()
    {
        $this->driver = config('database.default');
        $this->database = config('database.connections.' . $this->driver . '.database');
        $this->options = config('backup.' . $this->driver . '.options');
        $this->password = config('database.connections.' . $this->driver . '.password');
    }

    public function findDumpBinary(): string
    {
        return new ExecutableFinder()->find(static::DUMP_TOOL);
    }

    protected function backup(): void
    {
        $process = new Process(static::command($this->password));
        $process->run();
    }
}