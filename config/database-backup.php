<?php

declare(strict_types=1);

return [
    'mysql' => [
        'options' => ['--routines', '--events', '--users']
    ],
    'path_to_backup' => env('BACKUP_DIR_PATH', storage_path('app/database/backups/')),
    'max_copies' => env('BACKUP_MAX_COPIES', 1),
    'path_to_dump_tool' => '/your/absolute/path/to/dump/tool',
];