<?php
/**
 * Does Not Delete THIS!
 *
 * Database Structure
 * Doctrine Schema Builder Table
 */
return [


    /* ------------------------------------
     * `event_meta` TABLE
     *
     * Meta data of user
     * --------------------------------- */
    'attachment' => [

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
            // fot post id
            'the_id' => [
                'type' => 'bigint',
                'options' => [
                    'length' => 10,
                    'notnull' => true,
                    'default' => 0
                ]
            ],
            'name' => [
                'type' => 'string',
                'options' => [
                    'length' => 240,
                    'notnull' => true,
                ]
            ],
            'url' => [
                'type' => 'text',
                'options' => [
                    // don't set length to make it sure as longtext on MySQL
                    'notnull' => true,
                    'default' => ''
                ]
            ],
            'type' => [
                'type' => 'string',
                'options' => [
                    'length' => 100,
                    'notnull' => false,
                    'default' => 'post'
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
        ]
    ],

    /* ------------------------------------
     * `options` TABLE
     *
     * Meta data of user
     * --------------------------------- */

    'options' => [

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
            'options_name' => [
                'type' => 'string',
                'options' => [
                    'length' => 240,
                    'notnull' => true
                ]
            ],
            'options_value' => [
                'type' => 'text',
                'options' => [
                    // don't set length to make it sure as longtext on MySQL
                    'notnull' => true,
                    'default' => ''
                ]
            ],
            'options_autoload' => [
                'type' => 'string',
                'options' => [
                    'length' => 10,
                    'notnull' => true,
                    'default' => 'no'
                ]
            ],
        ],

        /* ------------------------------------
         * COLUMNS DEFINITIONS
         * --------------------------------- */
        'properties' => [
            'primaryKey' => ['id'],
            'uniqueIndex' => [
                // give args key as multiple arguments
                'args' => [
                    ['options_name'], 'unique_options_name'
                ]
            ]
        ]
    ],

    /* ------------------------------------
     * `user` TABLE
     *
     * main user data
     * --------------------------------- */
    'user' => [
        /* ------------------------------------
         *  COLUMN DEFINITIONS
         * --------------------------------- */
        'columns' => [

            /* ------------------------------------
             *  -> User Grant Detail Auth
             * --------------------------------- */
            'id' => [
                /* type */
                'type' => 'bigint',
                /* options */
                'options' => [
                    'autoincrement' => 1,
                    'length' => 10
                ]
            ],
            'username' => [
                'type' => 'string',
                'options' => [
                    'length' => 120,
                    'notnull' => 1,
                ]
            ],
            'email' => [
                'type' => 'string',
                'options' => [
                    'length' => 255,
                    'notnull' => false,
                ]
            ],
            'password' => [
                'type' => 'string',
                'options' => [
                    'length'  => 60,
                    'notnull' => false,
                    'default' => null
                ]
            ],
            'level' => [
                'type' => 'string',
                'options' => [
                    'length' => 100,
                    'notnull' => false,
                    'default' => 'member'
                ]
            ],

            /* ------------------------------------
             *  -> User Data
             * --------------------------------- */
            'first_name' => [
                'type' => 'string',
                'options' => [
                    'length' => 120,
                    'notnull' => 1,
                    'default' => ''
                ]
            ],
            'last_name' => [
                'type' => 'string',
                'options' => [
                    'length' => 120,
                    'notnull' => false,
                ]
            ],
            // metadata property
            'property' => [
                'type' => 'text',
                'options' => [
                    'notnull' => false,
                    'default' => null
                ]
            ],

            /* ------------------------------------
             *  -> User Status
             * --------------------------------- */
            'status' => [
                'type' => 'string',
                'options' => [
                    'length' => 100,
                    'notnull' => false,
                    'default' => 'pending'
                ]
            ],

            /* ------------------------------------
             *  -> User Token Authentication
             * --------------------------------- */
            'public_token' => [
                'type' => 'text',
                'options' => [
                    'notnull' => false,
                    'default' => null
                ]
            ],
            'private_token' => [
                'type' => 'text',
                'options' => [
                    'notnull' => false,
                    'default' => null
                ]
            ],

            /* ------------------------------------
             *  -> User Time Records Time
             * --------------------------------- */
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
                    ['username', 'email'], 'unique_auth'
                ]
            ]
        ]
    ],

    /* ------------------------------------
     * `user_meta` TABLE
     *
     * Meta data of user
     * --------------------------------- */
    'user_meta' => [

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

    /* ------------------------------------
     * `post` TABLE
     * Post & Page table
     * --------------------------------- */
    'post' => [

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
            'status' => [
                'type' => 'string',
                'options' => [
                    'length' => 100,
                    'notnull' => false,
                    'default' => 'draft'
                ]
            ],
            'protected' => [
                'type' => 'int',
                'options' => [
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            ],
            'password' => [
                'type' => 'text',
                'options' => [
                    // don't set length to make it sure as longtext on MySQL
                    'notnull' => false,
                    'default' => null
                ]
            ],
            'type' => [
                'type' => 'string',
                'options' => [
                    'length' => 100,
                    'notnull' => false,
                    'default' => 'post'
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
                    ['permalink'], 'unique_slug_post'
                ]
            ]
        ]
    ],

    /* ------------------------------------
     * `post_meta` TABLE
     *
     * Meta data of user
     * --------------------------------- */
    'post_meta' => [

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
