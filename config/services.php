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

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
        'version' => '2023-06-01',
    ],

    // Alibaba Cloud Model Studio (DashScope) — Qwen models
    // OpenAI-compatible endpoint (Singapore region)
    'dashscope' => [
        'key'      => env('DASHSCOPE_API_KEY'),
        'model'    => env('DASHSCOPE_MODEL', 'qwen-turbo'),
        'base_url' => env('DASHSCOPE_BASE_URL', 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'),
    ],

    'zoho' => [
        'client_id' => env('ZOHO_CLIENT_ID'),
        'client_secret' => env('ZOHO_CLIENT_SECRET'),
        'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
        'organization_id' => env('ZOHO_ORGANIZATION_ID'),
        'base_url' => env('ZOHO_BASE_URL', 'https://www.zohoapis.com/invoice/v3'),
    ],

];
