<?php

return [
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'demo' => env('PAYMENTS_DEMO', false),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    ],
    'firebase' => [
        'apiKey' => env('FIREBASE_API_KEY', 'AIzaSyBJ9zqfp7Iv5OKX7_jcZwRYujkTggpqNQo'),
        'authDomain' => env('FIREBASE_AUTH_DOMAIN', 'blacklinks-196ac.firebaseapp.com'),
        'projectId' => env('FIREBASE_PROJECT_ID', 'blacklinks-196ac'),
        'storageBucket' => env('FIREBASE_STORAGE_BUCKET', 'blacklinks-196ac.firebasestorage.app'),
        'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID', '823294920330'),
        'appId' => env('FIREBASE_APP_ID', '1:823294920330:web:f21d6813503f63736e8f73'),
        'measurementId' => env('FIREBASE_MEASUREMENT_ID', 'G-TZR6FNSM5K'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

];
