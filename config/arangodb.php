<?php

declare(strict_types=1);

return [
    'datetime_format' => 'Y-m-d\TH:i:s.vp',
    'schema' => [
        /*
         * @see https://docs.arangodb.com/stable/develop/http-api/collections/#create-a-collection_body_keyOptions_allowUserKeys
         */
        'keyOptions' => [
            'allowUserKeys' => true,
            'type' => 'traditional',
        ],
        'key_handling' => [
            'prioritize_configured_key_type' => false,
            'use_traditional_over_autoincrement' => true,
        ],
        // Key type prioritization takes place in the following order:
        // 1: table config within the migration file (this always takes priority)
        // 2: The id column methods such as id() and ...Increments() methods in the migration file
        // 3: The configured key type above.
        // The order of 2 and 3 can be swapped; in which case the configured key takes priority over column methods.
        // These settings are merged, individual keyOptions can be overridden in this way.
    ],
];
