<?php

namespace TaylorNetwork\BackupImporter\Commands;


use Illuminate\Console\GeneratorCommand;

class ImporterNewCommand extends GeneratorCommand
{
    protected $signature = 'importer:new {name}';

    protected $type = 'Importer';

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        return implode(DIRECTORY_SEPARATOR, [__DIR__,'stubs','Importer.stub']);
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return config('backup-importer.namespace', $rootNamespace.'\\Backup\\Importers');
    }
}