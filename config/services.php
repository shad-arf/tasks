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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'base_url' => env('WHATSAPP_BASE_URL', 'https://wa.bazrgan.com'),
        'send_endpoint' => env('WHATSAPP_SEND_ENDPOINT', '/api/send'),
        'token_url' => env('WHATSAPP_TOKEN_URL', '/api/auth/token'),
        'token' => env('WHATSAPP_TOKEN', env('WHATSAPP_API_TOKEN')),
        'client_id' => env('WHATSAPP_CLIENT_ID', env('CLIENT_ID')),
        'client_secret' => env('WHATSAPP_CLIENT_SECRET', env('CLIENT_SECRET')),
        'account' => env('WHATSAPP_ACCOUNT', 'books'),
    ],

];
