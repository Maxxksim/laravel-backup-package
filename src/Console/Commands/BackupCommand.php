<?php

namespace Maksym\LaravelBackupPackage\Console\Commands;

use Exception;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Maksym\LaravelBackupPackage\Services\BackupService;

#[Signature('backup:create {--max= : max copies}')]
class BackupCommand extends Command
{
    private const int DEFAULT_MAX_COPIES = 1;

    public function handle(BackupService $backupService): int
    {
        try {
            $infoMessage = $backupService->startDump($this->getMaxCopies());
        } catch (Exception $e) {
            $this->error($e);
            return self::FAILURE;
        }

        $this->info($infoMessage);

        return self::SUCCESS;
    }

    private function getMaxCopies()
    {
        $max = filter_var(
            $this->option('max') ?? self::DEFAULT_MAX_COPIES,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        if (!$max) {
            throw new InvalidArgumentException('The --max option must be a valid integer greater than 0.');
        }

        return $max;
    }
}