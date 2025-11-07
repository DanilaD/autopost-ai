<?php

/**
 * AI Configuration
 *
 * This configuration file defines all AI providers, their settings,
 * fallback chains, rate limits, and other AI-related configurations.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used when
    | no specific provider is requested. Supported providers: openai,
    | anthropic, google, local.
    |
    */
    'default' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Chain
    |--------------------------------------------------------------------------
    |
    | This array defines the order of providers to try when the primary
    | provider fails. The system will automatically fallback to the next
    | provider in the chain if the current one is unavailable.
    |
    */
    'fallback_chain' => [
        'openai',
        'anthropic',
        'google',
        'local',
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API integration including API key, base URL,
    | and model settings.
    |
    */
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'base' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1/'),
        'timeout' => env('OPENAI_TIMEOUT', 30),
        'max_retries' => env('OPENAI_MAX_RETRIES', 3),
        'models' => [
            'text' => [
                'gpt-4o' => [
                    'cost_per_token' => 0.005,
                    'max_tokens' => 128000,
                    'context_window' => 128000,
                ],
                'gpt-4o-mini' => [
                    'cost_per_token' => 0.00015,
                    'max_tokens' => 128000,
                    'context_window' => 128000,
                ],
                'gpt-3.5-turbo' => [
                    'cost_per_token' => 0.0005,
                    'max_tokens' => 16385,
                    'context_window' => 16385,
                ],
            ],
            'image' => [
                'dall-e-3' => [
                    'cost_per_image' => 0.04,
                    'sizes' => ['1024x1024', '1024x1792', '1792x1024'],
                ],
                'dall-e-2' => [
                    'cost_per_image' => 0.02,
                    'sizes' => ['256x256', '512x512', '1024x1024'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Anthropic Claude API integration.
    |
    */
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'base' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1/'),
        'timeout' => env('ANTHROPIC_TIMEOUT', 30),
        'max_retries' => env('ANTHROPIC_MAX_RETRIES', 3),
        'models' => [
            'text' => [
                'claude-3-opus-20240229' => [
                    'cost_per_token' => 0.015,
                    'max_tokens' => 4096,
                    'context_window' => 200000,
                ],
                'claude-3-sonnet-20240229' => [
                    'cost_per_token' => 0.003,
                    'max_tokens' => 4096,
                    'context_window' => 200000,
                ],
                'claude-3-haiku-20240307' => [
                    'cost_per_token' => 0.00025,
                    'max_tokens' => 4096,
                    'context_window' => 200000,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google AI (Gemini) API integration.
    |
    */
    'google' => [
        'key' => env('GOOGLE_AI_API_KEY'),
        'base' => env('GOOGLE_AI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/'),
        'timeout' => env('GOOGLE_AI_TIMEOUT', 30),
        'max_retries' => env('GOOGLE_AI_MAX_RETRIES', 3),
        'models' => [
            'text' => [
                'gemini-pro' => [
                    'cost_per_token' => 0.0005,
                    'max_tokens' => 30720,
                    'context_window' => 30720,
                ],
                'gemini-pro-vision' => [
                    'cost_per_token' => 0.0005,
                    'max_tokens' => 12288,
                    'context_window' => 12288,
                ],
            ],
            'image' => [
                'imagen-3' => [
                    'cost_per_image' => 0.02,
                    'sizes' => ['1024x1024', '1024x1792', '1792x1024'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Local AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for local AI models via Ollama.
    |
    */
    'local' => [
        'base' => env('LOCAL_AI_BASE_URL', 'http://localhost:11434'),
        'timeout' => env('LOCAL_AI_TIMEOUT', 60),
        'max_retries' => env('LOCAL_AI_MAX_RETRIES', 2),
        'models' => [
            'text' => [
                'llama2' => [
                    'cost_per_token' => 0.0, // Free
                    'max_tokens' => 4096,
                    'context_window' => 4096,
                ],
                'codellama' => [
                    'cost_per_token' => 0.0, // Free
                    'max_tokens' => 16384,
                    'context_window' => 16384,
                ],
                'mistral' => [
                    'cost_per_token' => 0.0, // Free
                    'max_tokens' => 32768,
                    'context_window' => 32768,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration for rate limiting AI requests to prevent abuse and
    | manage API quotas.
    |
    */
    'rate_limits' => [
        'requests_per_minute' => env('AI_RATE_LIMIT_RPM', 60),
        'tokens_per_minute' => env('AI_RATE_LIMIT_TPM', 150000),
        'images_per_hour' => env('AI_RATE_LIMIT_IPH', 50),
        'burst_limit' => env('AI_RATE_LIMIT_BURST', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching AI responses to improve performance and
    | reduce API costs.
    |
    */
    'cache' => [
        'enabled' => env('AI_CACHE_ENABLED', true),
        'ttl' => env('AI_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => env('AI_CACHE_PREFIX', 'ai_generation'),
        'store' => env('AI_CACHE_STORE', 'redis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Moderation
    |--------------------------------------------------------------------------
    |
    | Configuration for content moderation and safety checks.
    |
    */
    'moderation' => [
        'enabled' => env('AI_MODERATION_ENABLED', true),
        'strict_mode' => env('AI_MODERATION_STRICT', false),
        'auto_moderate' => env('AI_AUTO_MODERATE', true),
        'providers' => ['openai', 'google'], // Providers that support moderation
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Management
    |--------------------------------------------------------------------------
    |
    | Configuration for cost tracking and budget management.
    |
    */
    'cost_management' => [
        'track_costs' => env('AI_TRACK_COSTS', true),
        'budget_alerts' => env('AI_BUDGET_ALERTS', true),
        'daily_limit_usd' => env('AI_DAILY_LIMIT_USD', 100),
        'monthly_limit_usd' => env('AI_MONTHLY_LIMIT_USD', 1000),
        'free_tier_priority' => env('AI_FREE_TIER_PRIORITY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring AI provider performance and reliability.
    |
    */
    'monitoring' => [
        'enabled' => env('AI_MONITORING_ENABLED', true),
        'track_response_times' => env('AI_TRACK_RESPONSE_TIMES', true),
        'track_error_rates' => env('AI_TRACK_ERROR_RATES', true),
        'alert_threshold_ms' => env('AI_ALERT_THRESHOLD_MS', 5000),
        'error_rate_threshold' => env('AI_ERROR_RATE_THRESHOLD', 0.05), // 5%
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Generation Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for AI content generation.
    |
    */
    'defaults' => [
        'temperature' => env('AI_DEFAULT_TEMPERATURE', 0.7),
        'max_tokens' => env('AI_DEFAULT_MAX_TOKENS', 1000),
        'top_p' => env('AI_DEFAULT_TOP_P', 1.0),
        'frequency_penalty' => env('AI_DEFAULT_FREQUENCY_PENALTY', 0.0),
        'presence_penalty' => env('AI_DEFAULT_PRESENCE_PENALTY', 0.0),
    ],
];
