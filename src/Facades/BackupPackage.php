<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Facades;

use Illuminate\Support\Facades\Facade;
use Maksym\LaravelBackupPackage\Services\BackupService;

class BackupPackage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BackupService::class;
    }
}
