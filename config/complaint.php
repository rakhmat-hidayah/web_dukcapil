<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Complaint Email Notification System Configuration
    |--------------------------------------------------------------------------
    |
    | Governs automatic email notifications dispatched when a new complaint
    | is submitted. Zero hardcoded recipients or secrets.
    |
    */
    'notifications' => [
        'enabled'      => filter_var(env('COMPLAINT_NOTIFICATION_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'email'        => env('COMPLAINT_NOTIFICATION_EMAIL'),
        'from_address' => env('MAIL_FROM_ADDRESS'),
        'from_name'    => env('MAIL_FROM_NAME', 'Dukcapil Kabupaten Dompu'),
    ],
];
