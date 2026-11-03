<?php

declare(strict_types=1);

namespace Maksym\LaravelBackupPackage\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Maksym\LaravelBackupPackage\Services\BackupService;

class BackupCommand extends Command
{
    protected $signature = 'backup:create {--max= : max copies}';

    public function handle(BackupService $backupService): int
    {
        try {
            $backupService->startDump($this->getMaxCopies());
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Database backups have created successfully.');

        return self::SUCCESS;
    }

    private function getMaxCopies(): ?int
    {
        if (!($option = $this->option('max'))) {
            return null;
        }

        $max = filter_var($option, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!$max) {
            throw new InvalidArgumentException('The --max option must be a valid integer greater than 0.');
        }

        return $max;
    }
}
