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
];