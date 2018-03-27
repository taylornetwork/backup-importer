<?php

namespace TaylorNetwork\BackupImporter;

use Illuminate\Support\ServiceProvider as BaseProvider;
use TaylorNetwork\BackupImporter\Commands\ImporterNewCommand;
use TaylorNetwork\BackupImporter\Commands\ImporterRunCommand;

class ServiceProvider extends BaseProvider
{
    public function boot()
    {
        $this->publishes([
            implode(DIRECTORY_SEPARATOR, [__DIR__,'config','backup-importer.php']) => config_path('backup-importer.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(implode(DIRECTORY_SEPARATOR, [__DIR__,'config','backup-importer.php']), 'backup-importer');

        $this->commands([
            ImporterNewCommand::class,
            ImporterRunCommand::class,
        ]);
    }
}