<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Tests\Faker;

use Illuminate\Support\Facades\File;
use Maksym\LaravelBackupPackage\Dumpers\DatabaseDumper;

class FakeDumper extends DatabaseDumper
{
    protected array $dumpTools = ['fakedumptool'];
    protected string $enVariablePSW = 'FAKE_ENV_PSW';

    protected function command(string $pathToDump, string $user, string $host, string $port, string $database, array $options = []): array
    {
        File::put($pathToDump, 'testing backup');

        return ['echo', 'test command'];
    }
}