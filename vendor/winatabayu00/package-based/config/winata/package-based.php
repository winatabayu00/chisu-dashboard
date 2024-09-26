<?php

return [
    'performable' => true,

    /**
     * The prefer_deletion key type.
     * possible values: softDeletes, isActive.
     */
    'prefer_deletion' => 'softDeletes',

    'using_performable' => [
        'performer_on_create' => false,
        'performer_on_update' => false,
        'performer_on_delete' => false,
        'performer_on_restore' => false,
    ],
    'performable_columns' => [
        'on_create' => 'created_by',
        'on_update' => 'updated_by',
        'on_delete' => 'deleted_by',
        'on_restore' => 'restored_by',
    ],

    'model' => [
        'users' => config('auth.providers.users.model'),
        /**
          * The user_key_type key type.
          * possible values: int, uuid.
          */
        'user_key_type' => 'int', // int or uuid

        'performer_table' => 'users'
    ]
];
