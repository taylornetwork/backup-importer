<?php

namespace TaylorNetwork\BackupImporter\Commands;


use Illuminate\Console\GeneratorCommand;

class ImporterNewCommand extends GeneratorCommand
{
    protected $signature = 'importer:new {name}';

    /**
     * @inheritDoc
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/Importer.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Backup\\Importers';
    }
}