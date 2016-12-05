<?php
/**
 * for events attribute
 */
return [
    /* ------------------------------------
     * `event` TABLE
     * Events table
     * --------------------------------- */
    'event' => [

        /* ------------------------------------
         * COLUMNS DEFINITIONS
         * --------------------------------- */
        'columns' => [
            'id' => [
                'type' => 'bigint',
                'options' => [
                    'autoincrement' => 1,
                    'length' => 10
                ]
            ],
            'title' => [
                'type' => 'string',
                'options' => [
                    // tittle according MSDN use 512 on Internet Explorer
                    // we use 240 because google only getting 50 - 60 characters
                    'length' => 240,
                    'notnull' => false,
                    'default' => null
                ]
            ],
            'permalink' => [
                'type' => 'string',
                'options' => [
                    // slug is 200 characters
                    'length' => 200,
                    'notnull' => true
                ]
            ],
            'content' => [
                'type' => 'text',
                'options' => [
                    // don't set length to make it sure as longtext on MySQL
                    'notnull' => true,
                    'default' => ''
                ]
            ],
            // meta properties
            'property' => [
                'type' => 'text',
                'options' => [
                    // don't set length to make it sure as longtext on MySQL
                    'notnull' => true,
                    'default' => ''
                ]
            ],
            // event doing
            'time_event_from' => [
                'type' => 'datetime',
                'options' => [
                    'notnull' => true,
                ]
            ],
            'time_event_to' => [
                'type' => 'datetime',
                'options' => [
                    'notnull' => true,
                ]
            ],
            'time_created' => [
                'type' => 'datetime',
                'options' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ]
            ],
            'time_update' => [
                'type' => 'datetime',
                'options' => [
                    'default' => '0000-00-00 00:00:00',
                ]
            ]
        ],

        /* ------------------------------------
         * TABLE PROPERTIES
         * --------------------------------- */
        'properties' => [
            'primaryKey' => ['id'],
            'uniqueIndex' => [
                // give args key as multiple arguments
                'args' => [
                    ['permalink'], 'unique_slug_event'
                ]
            ]
        ]
    ],

    /* ------------------------------------
     * `event_meta` TABLE
     *
     * Meta data of user
     * --------------------------------- */
    'event_meta' => [

        /* ------------------------------------
         * COLUMNS DEFINITIONS
         * --------------------------------- */
        'columns' => [
            'id' => [
                'type' => 'bigint',
                'options' => [
                    'autoincrement' => 1,
                    'length' => 10
                ]
            ],
            'the_id' => [
                'type' => 'bigint',
                'options' => [
                    'length' => 10
                ]
            ],
            'name' => [
                'type' => 'string',
                'options' => [
                    'length' => 240,
                    'notnull' => true,
                ]
            ],
            'meta_value' => [
                'type' => 'text',
                'options' => [
                    // don't set length to make it sure as longtext on MySQL
                    'notnull' => true,
                    'default' => ''
                ]
            ],
            'time_created' => [
                'type' => 'datetime',
                'options' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ]
            ],
            'time_update' => [
                'type' => 'datetime',
                'options' => [
                    'default' => '0000-00-00 00:00:00',
                ]
            ]
        ],

        /* ------------------------------------
         * TABLE PROPERTIES
         * --------------------------------- */
        'properties' => [
            'primaryKey' => ['id'],
        ]
    ],
];
