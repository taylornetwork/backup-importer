<?php

namespace TaylorNetwork\BackupImporter;

use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;


class Importer
{
    /**
     * Define only specific importers to use or use ['*'] for all;
     *
     * @var array
     */
    public $importers;

    /**
     * Namespace for the importers
     *
     * @var string
     */
    public $namespace;

    /**
     * Show status messages
     *
     * @var bool
     */
    public $cliMessages;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Tally all the imported items
     *
     * @var int
     */
    public $totalImported = 0;

    /**
     * Importer constructor.
     */
    public function __construct()
    {
        $this->cliMessages = config('backup-importer.cli-messages', true);
        $this->importers = config('backup-importer.use-importers', ['*']);
        $this->namespace = config('backup-importer.namespace', 'App\\Backup\\Importers');
        $this->connection = app(ConnectionFactory::class)->make($this->getDBConfig(), 'backup');
    }

    /**
     * Import using the defined importers
     *
     * @return int
     */
    public function import(): int
    {
        $importers = $this->importers;

        if(in_array('*', $importers)) {
            $importers = [];
            foreach(glob($this->getImportersPath() . DIRECTORY_SEPARATOR . '*.php') as $importer) {
                if($importer !== $this->getImportersPath(). DIRECTORY_SEPARATOR . 'BaseImporter.php') {
                    $importers[] = $this->namespace . '\\' . last(explode(DIRECTORY_SEPARATOR, $importer));
                }
            }
        }

        foreach($importers as $importer) {
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
        if($this->cliMessages) {
            if(function_exists('dump')) {
                dump($message);
            } else {
                echo $message;
            }
        }
    }

    /**
     * Get Backup Database Config
     *
     * @return array
     */
    public function getDBConfig(): array
    {
        $dbConfig = config('backup-importer.db-connection');

        foreach(config('database.connections.' . $dbConfig['driver']) as $key => $value) {
            if(!array_key_exists($key, $dbConfig)) {
                $dbConfig[$key] = $value;
            }
        }

        return $dbConfig;
    }

    /**
     * Get the path of the importers
     *
     * @return string
     */
    public function getImportersPath(): string
    {
        $exploded = explode('\\', $this->namespace);

        if(strtolower($exploded[0]) === 'app') {
            unset($exploded[0]);
        }

        return app_path(implode(DIRECTORY_SEPARATOR, $exploded));
    }

    /**
     * Get the connection
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }
}