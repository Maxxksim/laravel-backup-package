<?php

declare(strict_types=1);

return [
    'mysql' => [
        'options' => ['--routines', '--events']
    ],
    'path_to_backup' => storage_path('app/database/backups/'),
    'max_copies' => (int)env('BACKUP_MAX_COPIES', 1),
    'path_to_dump_tool' => '/your/absolute/path/to/dump/tool',
];