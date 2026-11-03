<?php

namespace Maksym\LaravelBackupPackage\Tests\Unit;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maksym\LaravelBackupPackage\Services\BackupService;
use Mockery;


beforeEach(function () {
    Storage::fake('local');
    $this->directoryForBackups = Storage::disk('local')->path('backups') . '/';
    File::ensureDirectoryExists($this->directoryForBackups);

    config()->set('database-backup.path_to_backup', $this->directoryForBackups);


});

afterEach(function () {
    File::deleteDirectory($this->directoryForBackups);
});

it('dumping keep only last N files', function () {

    $existingBackups = 5;
    $maxCopies = 3;

    $dbConnection = config('database.default');
    $fakeBackups = $this->makeFakeBackups($existingBackups,
        $this->directoryForBackups,
        config("database.connections.$dbConnection.database"),
    )->sortBy('datetime');

    $countBackupsToDelete = $existingBackups + 1 - $maxCopies;
    $oldestBackupsShouldBeDeleted = $fakeBackups->take($countBackupsToDelete);
    $lastBackupsShouldBeKept = $fakeBackups->diffKeys($oldestBackupsShouldBeDeleted);

    $backupService = $this->makeServiceWithFakeDumper();
    $backupService->startDump($maxCopies);

    $backupsFromDirectory = array_map(
        fn($file) => $file->getFilename(),
        File::files($this->directoryForBackups),
    );

    $keptFilesNames = $lastBackupsShouldBeKept->pluck('filename')->all();
    $deletedFilesNames = $oldestBackupsShouldBeDeleted->pluck('filename')->all();

    expect($backupsFromDirectory)->toHaveCount($maxCopies)
        ->and(array_diff($keptFilesNames, $backupsFromDirectory))->toBeEmpty()
        ->and(array_intersect($deletedFilesNames, $backupsFromDirectory))->toBeEmpty();
});

