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
        'github_embedding_model' => env('GITHUB_EMBEDDING_MODEL', 'text-embedding-3-small'),

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

        // Chat behavior settings
        'chat' => [
            'max_tokens' => env('OPENAI_CHAT_MAX_TOKENS', 150),
            'temperature' => env('OPENAI_CHAT_TEMPERATURE', 0.4),
            'history_limit' => env('OPENAI_CHAT_HISTORY_LIMIT', 4),
            'require_clarification' => env('OPENAI_CHAT_REQUIRE_CLARIFICATION', true),
            'clarification_only_until' => env('OPENAI_CHAT_CLARIFY_TURNS', 2),
            'include_past_conversations' => env('OPENAI_CHAT_INCLUDE_PAST_CONVERSATIONS', false),
            'past_conversation_similarity' => env('OPENAI_CHAT_PAST_SIMILARITY', 0.6),
            'current_conversation_similarity' => env('OPENAI_CHAT_CURRENT_SIMILARITY', 0.4),
        ],
    ],

    'crisis' => [
        'alert_email' => env('CRISIS_ALERT_EMAIL', 'crisis@unipulse.edu'),
        'sms_enabled' => env('CRISIS_SMS_ENABLED', false),
    ],

    'ml_clustering' => [
        'url' => env('ML_CLUSTERING_URL', 'http://127.0.0.1:5000'),
    ],

    'ai' => [
        'base_url' => env('AI_SERVICE_URL', 'http://127.0.0.1:8001'),
        'timeout' => env('AI_SERVICE_TIMEOUT', 30),
        'retries' => env('AI_SERVICE_RETRIES', 2),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
    ],
    'pinecone' => [
        'api_key'         => env('PINECONE_API_KEY'),
        'index_host'      => env('PINECONE_INDEX_HOST'),       // e.g. 'https://your-index-xxxxx.svc.pinecone.io'
        'namespace'       => env('PINECONE_NAMESPACE', 'unipulse'),
        'enabled'         => env('PINECONE_ENABLED', false),    // Toggle Pinecone on/off (graceful fallback)
        'embedding_model' => env('PINECONE_EMBEDDING_MODEL', 'multilingual-e5-large'),
    ],

];
