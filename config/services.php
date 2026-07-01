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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME', 'pengajuankegiatan_bot'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
        'timeout' => (int) env('TELEGRAM_TIMEOUT', 10),
        'connect_timeout' => (int) env('TELEGRAM_CONNECT_TIMEOUT', 5),
    ],

    'unuja' => [
        'base_url' => env('UNUJA_API_BASE_URL', 'https://v2-api.unuja.ac.id'),
        'login_url' => env('UNUJA_API_LOGIN_URL', 'https://v2-api.unuja.ac.id/log/masuk'),
        'username' => env('UNUJA_API_USERNAME'),
        'password' => env('UNUJA_API_PASSWORD'),
        'api_key_header' => env('UNUJA_API_KEY_HEADER', 'unujasimptapikey'),
        'timeout' => env('UNUJA_API_TIMEOUT', 10),
    ],

];
