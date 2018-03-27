<?php

namespace TaylorNetwork\BackupImporter;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Importer
{
    /**
     * Define only specific importers to use or use ['*'] for all;
     *
     * @var array
     */
    public $imports;

    /**
     * Namespace for the importers
     *
     * @var string
     */
    public $namespace;

    /**
     * Show status messages (true if running from command line)
     *
     * @var bool
     */
    public $showMessages = true;

    /**
     * @var Connection
     */
    public $connection;

    /**
     * Tally all the imported items
     *
     * @var int
     */
    public $totalImported = 0;

    /**
     * Importer constructor.
     *
     * @throws ImportException
     */
    public function __construct()
    {
        $this->imports = Config::get('backup-importer.use-importers', ['*']);
        $this->namespace = Config::get('backup-importer.namespace', 'App\\Backup\\Importers');

        if(Config::get('database.connections.backup', null) !== null) {
            throw new ImportException('The \'backup\' database connection has not been set in config/database.php');
        }

        $this->connection = DB::connection('backup');

    }

    /**
     * Import using the defined importers
     *
     * @return int
     */
    public function import(): int
    {
        $imports = $this->imports;

        if(in_array('*', $imports)) {
            $imports = [];
            $importsPath = __DIR__.'/Imports';
            foreach(glob($importsPath . '/*.php') as $importer) {
                if($importer !== $importsPath.'/BaseImporter.php') {
                    $imports[] = $importer;
                }
            }
        }

        foreach($imports as $importer) {
            $this->msg('===== START =====');
            $instance = new $importer($this->connection);
            $this->msg('Loaded ' . $importer);

            $this->msg('Executing \'$instance->init()\'');
            $instance->init();
            $this->msg('Done init phase.');

            $this->msg('Importing items via \'$instance->import()\'');
            $imported = $instance->import();
            $this->totalImported += $imported;
            $this->msg('Imported ' . $imported . ' items.');

            $this->msg('Executing \'$instance->cleanUp()\'');
            $instance->cleanUp();
            $this->msg('Done cleaning up.');
            $this->msg('===== END ====');
        }

        return $this->totalImported;
    }

    /**
     * Show a message
     *
     * @param string $message
     */
    public function msg(string $message): void
    {
        if($this->showMessages) {
            if(function_exists('dump')) {
                dump($message);
            } else {
                echo $message;
            }
        }
    }
}