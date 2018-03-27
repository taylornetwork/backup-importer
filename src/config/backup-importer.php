<?php

return [
    /**
     * Where your importers will be stored
     */
    'namespace' => 'App\\Backup\\Importers',

    /**
     * Importers to use, ['*'] for everything in the importers folder.
     *
     * Add only the ones you want to this array if you want to pick and choose
     */
    'use-importers' => ['*'],

    /**
     * Add you backup database connection details here
     *
     * The driver and database keys are required, but everything else will be imported
     * from the config/database.php driver config but NOT overridden
     */
    'db-connection' => [
        'driver' => 'mysql',
        'database' => env('DB_BACKUP_DATABASE', 'backup'),
    ],

    /**
     * Show messages while importing? (Command Line Only)
     */
    'cli-messages' => true,
];