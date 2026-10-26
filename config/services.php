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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 电商平台开放接口
    |--------------------------------------------------------------------------
    |
    | 未配置 endpoint 的平台会被 erp:ecommerce-sync 跳过（不会写入任何模拟数据），
    | 配置后 SyncEcommerceOrdersJob 会拉取订单并按平台单号幂等入库。
    |
    */

    'ecommerce' => [
        'platforms' => [
            'taobao' => [
                'endpoint' => env('ECOMMERCE_TAOBAO_ENDPOINT'),
                'app_key' => env('ECOMMERCE_TAOBAO_APP_KEY'),
                'app_secret' => env('ECOMMERCE_TAOBAO_APP_SECRET'),
            ],
            'tmall' => [
                'endpoint' => env('ECOMMERCE_TMALL_ENDPOINT'),
                'app_key' => env('ECOMMERCE_TMALL_APP_KEY'),
                'app_secret' => env('ECOMMERCE_TMALL_APP_SECRET'),
            ],
            'jd' => [
                'endpoint' => env('ECOMMERCE_JD_ENDPOINT'),
                'app_key' => env('ECOMMERCE_JD_APP_KEY'),
                'app_secret' => env('ECOMMERCE_JD_APP_SECRET'),
            ],
            'pdd' => [
                'endpoint' => env('ECOMMERCE_PDD_ENDPOINT'),
                'app_key' => env('ECOMMERCE_PDD_APP_KEY'),
                'app_secret' => env('ECOMMERCE_PDD_APP_SECRET'),
            ],
            'douyin' => [
                'endpoint' => env('ECOMMERCE_DOUYIN_ENDPOINT'),
                'app_key' => env('ECOMMERCE_DOUYIN_APP_KEY'),
                'app_secret' => env('ECOMMERCE_DOUYIN_APP_SECRET'),
            ],
            'amazon' => [
                'endpoint' => env('ECOMMERCE_AMAZON_ENDPOINT'),
                'app_key' => env('ECOMMERCE_AMAZON_APP_KEY'),
                'app_secret' => env('ECOMMERCE_AMAZON_APP_SECRET'),
            ],
        ],
    ],

];
