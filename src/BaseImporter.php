<?php

namespace TaylorNetwork\BackupImporter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use TaylorNetwork\BackupImporter\ImportException;

abstract class BaseImporter
{
    /**
     * The related model class
     *
     * @var string
     */
    protected $model;


    /**
     * Set to true if the importer doesn't have a model associated with it
     *
     * @var bool
     */
    protected $ignoreModel = false;

    /**
     * The table name on the backup DB
     *
     * @var string
     */
    protected $backupTableName;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Number of items imported
     *
     * @var int
     */
    protected $imported = 0;

    /**
     * BaseImporter constructor.
     *
     * @param Connection $connection
     * @throws ImportException
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        if(!isset($this->model) && !$this->ignoreModel) {
            $this->model = $this->guessModel();
        }

        if(!isset($this->backupTableName)) {
            $this->backupTableName = $this->guessBackupTableName();
        }

    }

    /**
     * Define how the data will be imported
     *
     * If the tables are identical define the import as
     *
     * public function import(): int
     * {
     *      return $this->simpleImport();
     * }
     *
     * If not, access the query by $this->query();
     *
     * Run a $this->increment() in your foreach loop, then return $this->imported
     *
     * @return int
     */
    abstract public function import(): int;

    /**
     * Guess the model if not set
     *
     * @return string
     * @throws ImportException
     */
    public function guessModel(): string
    {
        $modelName = str_replace('Importer', '', class_basename(get_class($this)));

        if(function_exists('get_model')) {
            return get_model($modelName);
        }

        if(config('models.namespace', null) !== null) {
            $namespace = config('models.namespace');

            if(substr($namespace, -1) !== '\\') {
                $namespace .= '\\';
            }

            return $namespace . studly_case($modelName);
        }

        if(class_exists('\\App\\' . studly_case($modelName))) {
            return '\\App\\' . studly_case($modelName);
        }

        throw new ImportException('Could not determine model in ' . get_class($this) . '. Set a $model variable to fix.');
    }

    /**
     * Guess the backup table name if not set
     *
     * @return string
     */
    public function guessBackupTableName(): string
    {
        $modelName = str_replace('Importer', '', class_basename(get_class($this)));

        return str_plural(snake_case($modelName));
    }

    /**
     * Columns to get and map
     *
     * @return array
     */
    public function getColumnMap(): array
    {
        return ['*'];
    }

    /**
     * Get the connection
     *
     * @return Connection
     */
    public function connection(): Connection
    {
        return $this->connection;
    }

    /**
     * Get query builder
     *
     * @return Builder
     */
    public function builder(): Builder
    {
        return $this->connection()->table($this->backupTableName);
    }

    /**
     * Get the items from query
     *
     * @return Collection
     */
    public function items(): Collection
    {
        return $this->select()->get();
    }

    /**
     * Get the builder after select statement
     *
     * @return Builder
     */
    public function select(): Builder
    {
        return $this->builder()->select($this->getColumnMap());
    }

    /**
     * Increment imported count
     */
    public function increment(): void
    {
        $this->imported++;
    }

    /**
     * Simple import, gets everything and creates it in the new DB.
     *
     * @return int
     */
    public function simpleImport(): int
    {
        foreach($this->items() as $item) {
            $this->getModel()->fill((array) $item)->save();
            $this->increment();
        }

        return $this->getImportTotal();
    }

    /**
     * Called before import
     */
    public function init(): void
    {
        if(!$this->ignoreModel) {
            $this->unguardModel();
        }
    }

    /**
     * Called after import
     */
    public function cleanUp(): void
    {
        if(!$this->ignoreModel) {
            $this->reguardModel();
        }
    }

    /**
     * UnGuard the model, allows you to manually set IDs, etc.
     */
    public function unguardModel(): void
    {
        $model = $this->model;
        $model::unguard();
    }

    /**
     * ReGuard the model
     */
    public function reguardModel(): void
    {
        $model = $this->model;
        $model::reguard();
    }

    /**
     * Get the total items imported
     *
     * @return int
     */
    public function getImportTotal(): int
    {
        return $this->imported;
    }

    /**
     * Get an Instance of the Model
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return new $this->model;
    }

    /**
     * __get
     *
     * @param $name
     * @return mixed
     */
    public function __get($name): mixed
    {
        return $this->$name;
    }
}