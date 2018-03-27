<?php

namespace TaylorNetwork\BackupImporter\Commands;

use Illuminate\Console\Command;
use TaylorNetwork\BackupImporter\Importer;

class ImporterRunCommand extends Command
{
    protected $signature = 'importer:run';

    public function handle()
    {
        $importer = new Importer;
        $importer->showMessages = true;
        $total = $importer->import();

        $this->alert('Imported ' . $total . ' items successfully.');
    }
}