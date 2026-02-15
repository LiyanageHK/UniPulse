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
        // Provider for chat: 'github', 'openai', or 'azure'
        'provider' => env('OPENAI_PROVIDER', 'azure'),
        // Provider for embeddings: 'github', 'openai', or 'azure' (defaults to main provider)
        'embedding_provider' => env('OPENAI_EMBEDDING_PROVIDER', env('OPENAI_PROVIDER', 'azure')),
        
        // API Keys by provider
        'api_key' => env('OPENAI_API_KEY'),
        'github_token' => env('GITHUB_TOKEN'),
        'github_embedding_token' => env('GITHUB_EMBEDDING_TOKEN'),
        'azure_api_key' => env('AZURE_OPENAI_API_KEY'),
        'azure_embedding_api_key' => env('AZURE_OPENAI_EMBEDDING_API_KEY'),
        
        // Default model names (used for openai/azure providers)
        'model' => env('OPENAI_MODEL', 'gpt-4.1'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        
        // GitHub-specific model names (used when provider=github)
        'github_chat_model' => env('GITHUB_CHAT_MODEL', 'openai/gpt-4.1'),
        'github_embedding_model' => env('GITHUB_EMBEDDING_MODEL', 'text-embedding-3-large'),
        
        // Azure specific URLs
        'azure_chat_url' => env('AZURE_OPENAI_CHAT_URL'),
        'azure_embedding_url' => env('AZURE_OPENAI_EMBEDDING_URL'),
        
        // OpenAI direct URLs
        'api_url' => 'https://api.openai.com/v1/chat/completions',
        'embedding_url' => 'https://api.openai.com/v1/embeddings',
        
        // GitHub Models URLs (built from base URL)
        'github_base_url' => env('GITHUB_MODELS_BASE_URL', 'https://models.github.ai/inference'),
        'github_chat_url' => env('GITHUB_MODELS_BASE_URL', 'https://models.github.ai/inference') . '/chat/completions',
        'github_embedding_url' => env('GITHUB_MODELS_BASE_URL', 'https://models.github.ai/inference') . '/embeddings',
    ],

    'crisis' => [
        'alert_email' => env('CRISIS_ALERT_EMAIL', 'crisis@unipulse.edu'),
        'sms_enabled' => env('CRISIS_SMS_ENABLED', false),
    ],


];
