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

    'wechat_pay' => [
        'app_id' => env('WECHAT_PAY_APP_ID'),
        'mch_id' => env('WECHAT_PAY_MCH_ID'),
        'serial_no' => env('WECHAT_PAY_SERIAL_NO'),
        'private_key_path' => env('WECHAT_PAY_PRIVATE_KEY_PATH'),
        'api_v3_key' => env('WECHAT_PAY_API_V3_KEY'),
        'notify_url' => env('WECHAT_PAY_NOTIFY_URL'),
        'public_key_id' => env('WECHAT_PAY_PUBLIC_KEY_ID'),
        'public_key_path' => env('WECHAT_PAY_PUBLIC_KEY_PATH'),
    ],

];
