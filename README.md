PHP 8.5

Laravel package to do database backup

To install run the command in your terminal: 

`composer require maxxksim/laravel-backup-package`

To get config file of the package run the command in your terminal: 

`php artisan vendor:publish --tag=database-backup-config`   

Support databases:
- MySQL
- Postgres
- MariaDB

Require: 
- You must have the tools on your system to do backup of support databases:
 1) mysqldump for MySQL
 2) pg_dump for Postgres
 3) mariadb-dump for MariaDB 11.0 and higher otherwise if you version less than 11.0 - mariadb-dump or mysqldump

Description for using:      

- The package will try to find tool for dumping automatically otherwise   
  if finding didn't get result the package will try find it in set path in config file under key `"path_to_dump_tool"`      
  if the two tries didn't get result the package will throw Exception  

  
- You can change path where you want to save your backups:
    just set your path in config file under key `"path_to_backup"`


- You can modify dumping options in config file under key `"options"` for any database

  
- The package includes 2 ways to use dumping of your database:  
  artisan command: `php artisan backup:create --max=N`  
  N - number of copies you want to keep; you don't have to set the option "--max=N" than the package set it in default value - 1    

  
- Facade: `Maksym\LaravelBackupPackage\Facades\BackupPackage::startDump(N);`   
  N - number of copies you want to keep;  
  You can set N in function directly or set it in your env in key BACKUP_MAX_COPIES=N otherwise the package takes default value - 1




