<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | LaravelToon Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración global para LaravelToon, incluyendo opciones de
    | codificación, análisis de tokens y adaptadores de API.
    |
    */

    'enabled' => env('LARAVEL_TOON_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Opciones de Codificación
    |--------------------------------------------------------------------------
    */
    'encoding' => [
        'indent' => 2,
        'delimiter' => ',',
        'escape_style' => 'backslash', // 'backslash' o 'unicode'
        'min_rows_to_tabular' => 2,
        'max_preview_items' => 200,
        'pretty_print' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Análisis de Tokens
    |--------------------------------------------------------------------------
    */
    'token_analysis' => [
        'enabled' => true,
        'estimate_method' => 'character_ratio', // 'character_ratio' o 'word_count'
        'chars_per_token' => 4,
        'cache_results' => true,
        'cache_ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cálculo de Costos
    |--------------------------------------------------------------------------
    */
    'cost_calculation' => [
        'enabled' => true,
        'currency' => 'USD',
        
        // Precios por 1M tokens (actualizar según necesidad)
        'models' => [
            'gpt-4o' => [
                'input' => 0.0025,
                'output' => 0.010,
            ],
            'gpt-4-turbo' => [
                'input' => 0.01,
                'output' => 0.03,
            ],
            'gpt-4' => [
                'input' => 0.03,
                'output' => 0.06,
            ],
            'gpt-3.5-turbo' => [
                'input' => 0.0005,
                'output' => 0.0015,
            ],
            'claude-3-opus' => [
                'input' => 0.015,
                'output' => 0.075,
            ],
            'claude-3-sonnet' => [
                'input' => 0.003,
                'output' => 0.015,
            ],
            'claude-3-haiku' => [
                'input' => 0.00025,
                'output' => 0.00125,
            ],
            'gemini-pro' => [
                'input' => 0.000125,
                'output' => 0.000375,
            ],
            'mistral-large' => [
                'input' => 0.008,
                'output' => 0.024,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Adaptadores LLM
    |--------------------------------------------------------------------------
    */
    'adapters' => [
        'openai' => [
            'enabled' => env('OPENAI_API_KEY') !== null,
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_API_BASE', 'https://api.openai.com/v1'),
            'default_model' => 'gpt-4o',
            'timeout' => 30,
        ],

        'anthropic' => [
            'enabled' => env('ANTHROPIC_API_KEY') !== null,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => 'https://api.anthropic.com',
            'default_model' => 'claude-3-sonnet-20240229',
            'timeout' => 30,
        ],

        'gemini' => [
            'enabled' => env('GEMINI_API_KEY') !== null,
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => 'https://generativelanguage.googleapis.com',
            'default_model' => 'gemini-pro',
            'timeout' => 30,
        ],

        'mistral' => [
            'enabled' => env('MISTRAL_API_KEY') !== null,
            'api_key' => env('MISTRAL_API_KEY'),
            'base_url' => 'https://api.mistral.ai',
            'default_model' => 'mistral-large-latest',
            'timeout' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'auto_compress' => false,
        'min_response_size' => 1024, // bytes
        'compression_threshold' => 50, // porcentaje mínimo para comprimir
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('APP_DEBUG', false),
        'channel' => 'single',
        'level' => 'debug',
        'log_compression_stats' => true,
        'log_api_calls' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'store' => env('CACHE_DRIVER', 'file'),
        'ttl' => 3600,
        'prefix' => 'laravel-toon:',
    ],
];

