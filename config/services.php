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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'twitter' => [
        'account' => env('TW_API_ACCOUNT', ''),
        'client_id' => env('TW_API_KEY', ''),
        'client_secret' => env('TW_API_SECRET', ''),
        'bearer' => env('TW_API_BEARER', ''),
        'redirect' => 'http://newsthisbot.test/permission/twitter/callback',

        'bot_account' => env('TW_API_ACCOUNT', ''),
        'bot_id' => env('TW_API_BOT_ID', ''),
        'bot_secret' => env('TW_API_BOT_SECRET', ''),
    ],

];
