<?php
return [
    /* ------------------------------------
     * `event` TABLE
     * Events table
     * --------------------------------- */
    'license' => [

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
            // for user id
            'the_id' => [
                'type' => 'bigint',
                'options' => [
                    'length' => 10,
                    'notnull' => true,
                    'default' => 0
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
                    ['content'], 'unique_license_content'
                ]
            ]
        ]
    ],

    /* ------------------------------------
     * `event_meta` TABLE
     *
     * Meta data of user
     * --------------------------------- */
    'license_meta' => [

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
