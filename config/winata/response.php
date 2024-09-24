<?php

return [

    'app_name' => env('APP_NAME', 'UNKNOWN APP NAME'),

    'log_event_class' => \Winata\Core\Response\Events\OnErrorEvent::class,

    /*
        * add credential logging
     */
    'performer' => [
        'model' => \App\Models\User::class, // accept only from auth user
        'column' => 'performed_id'
    ],

    'reportable' => [
        'telegram' => [
            'logging' => false
        ],
    ],
    'driver' => [
        'telegram' => [
            'token' => '6386997255:AAH099F7fjaOD8O4NGYhU-kY27xWfzMK1_A', // your api token
            'chat_id' => '-1001948695866',
            'formatting' => [
                'title' => '*ERROR EXCEPTION*',
                'cc' => '#winatabayu00',
            ],
        ],
        'database' => [

        ]
    ],
];
