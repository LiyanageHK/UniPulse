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

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'o3'),  // GitHub Models: o3
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'use_github_models' => env('USE_GITHUB_MODELS', true),
        // Separate tokens for chat and embeddings
        'github_token' => env('GITHUB_TOKEN'),  // Token for o3 chat model
        'github_embedding_token' => env('GITHUB_EMBEDDING_TOKEN'),  // Token for embeddings
        'api_url' => env('USE_GITHUB_MODELS', true) 
            ? 'https://models.inference.ai.azure.com/chat/completions'
            : 'https://api.openai.com/v1/chat/completions',
        'embedding_url' => env('USE_GITHUB_MODELS', true)
            ? 'https://models.inference.ai.azure.com/embeddings'
            : 'https://api.openai.com/v1/embeddings',
    ],

    'crisis' => [
        'alert_email' => env('CRISIS_ALERT_EMAIL', 'crisis@unipulse.edu'),
        'sms_enabled' => env('CRISIS_SMS_ENABLED', false),
    ],


];
