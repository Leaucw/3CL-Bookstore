<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'purchases_api' => [
        'base'    => env('PURCHASES_API_BASE', 'http://localhost/api/v1'),
        'token'   => env('PURCHASES_API_TOKEN', null),
        'timeout' => (float)env('PURCHASES_API_TIMEOUT', 10),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'books_api' => [
        'base'    => env('BOOKS_API_BASE', 'http://127.0.0.1:8001/api/v1'),
        'timeout' => (float) env('BOOKS_API_TIMEOUT', 5),
    ],

    'users_api' => [
        'base'    => env('USERS_API_BASE', 'http://127.0.0.1:8001/api/v1'),
        'timeout' => (float) env('USERS_API_TIMEOUT', 5),
    ],

    'orders_api' => [
        'base' => env('ORDERS_API_BASE', 'http://127.0.0.1:8001/api/v1'),
        'timeout' => (float) env('USERS_API_TIMEOUT', 5),

    ],


];
