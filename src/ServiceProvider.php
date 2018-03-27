<?php

namespace TaylorNetwork\BackupImporter;

use Illuminate\Support\ServiceProvider as BaseProvider;
use TaylorNetwork\BackupImporter\Commands\ImporterNewCommand;

class ServiceProvider extends BaseProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/backup-importer.php' => config_path('backup-importer.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/backup-importer.php', 'backup-importer');

        $this->commands([
            ImporterNewCommand::class,
        ]);
    }
}