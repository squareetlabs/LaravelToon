<?php

declare(strict_types=1);

use Squareetlabs\LaravelToon\Services\CompressionMetrics;
use Squareetlabs\LaravelToon\Services\CostCalculator;
use Squareetlabs\LaravelToon\Services\TokenAnalyzer;
use Squareetlabs\LaravelToon\Services\ToonService;
use Squareetlabs\LaravelToon\Toon\EncodeOptions;

if (!function_exists('toon')) {
    /**
     * Encode data to TOON format
     */
    function toon(mixed $data, ?EncodeOptions $options = null): string
    {
        return app(ToonService::class)->encode($data, $options);
    }
}

if (!function_exists('toon_compact')) {
    /**
     * Encode data to compact TOON format
     */
    function toon_compact(mixed $data): string
    {
        return app(ToonService::class)->encodeCompact($data);
    }
}

if (!function_exists('toon_readable')) {
    /**
     * Encode data to readable TOON format
     */
    function toon_readable(mixed $data): string
    {
        return app(ToonService::class)->encodeReadable($data);
    }
}

if (!function_exists('toon_tabular')) {
    /**
     * Encode data to tabular TOON format
     */
    function toon_tabular(mixed $data): string
    {
        return app(ToonService::class)->encodeTabular($data);
    }
}

if (!function_exists('toon_decode')) {
    /**
     * Decode TOON format to PHP array
     */
    function toon_decode(string $toon): mixed
    {
        return app(ToonService::class)->decode($toon);
    }
}

if (!function_exists('toon_convert')) {
    /**
     * Convert data to TOON format with specified format
     */
    function toon_convert(mixed $data, string $format = 'readable'): string
    {
        return app(ToonService::class)->convert($data, $format);
    }
}

if (!function_exists('toon_compress')) {
    /**
     * Compress and get detailed metrics
     */
    function toon_compress(mixed $data): array
    {
        return app(ToonService::class)->compress($data);
    }
}

if (!function_exists('toon_metrics')) {
    /**
     * Get full compression metrics
     */
    function toon_metrics(mixed $data): array
    {
        return app(ToonService::class)->getMetrics($data);
    }
}

if (!function_exists('toon_estimate_tokens')) {
    /**
     * Estimate tokens in content
     */
    function toon_estimate_tokens(string $content): int
    {
        return app(TokenAnalyzer::class)->estimate($content);
    }
}

if (!function_exists('toon_compare_json_vs_toon')) {
    /**
     * Compare tokens between JSON and TOON
     */
    function toon_compare_json_vs_toon(mixed $data): array
    {
        return app(TokenAnalyzer::class)->compareJsonVsToon($data);
    }
}

if (!function_exists('toon_analyze')) {
    /**
     * Analyze content with token metrics
     */
    function toon_analyze(string $content): array
    {
        return app(TokenAnalyzer::class)->analyze($content);
    }
}

if (!function_exists('toon_compression_summary')) {
    /**
     * Get summary of compression
     */
    function toon_compression_summary(mixed $data): array
    {
        return app(CompressionMetrics::class)->summary($data);
    }
}

if (!function_exists('toon_full_metrics')) {
    /**
     * Get full compression and token metrics
     */
    function toon_full_metrics(mixed $data): array
    {
        return app(CompressionMetrics::class)->full($data);
    }
}

if (!function_exists('toon_benchmark')) {
    /**
     * Run performance benchmark
     */
    function toon_benchmark(mixed $data, int $iterations = 100): array
    {
        return app(CompressionMetrics::class)->benchmark($data, $iterations);
    }
}

if (!function_exists('toon_cost_estimate')) {
    /**
     * Estimate API cost for data
     */
    function toon_cost_estimate(string $model, mixed $data, string $role = 'input'): array
    {
        return app(CostCalculator::class)->estimateCost($model, $data, $role);
    }
}

if (!function_exists('toon_cost_compare_models')) {
    /**
     * Compare costs across multiple models
     */
    function toon_cost_compare_models(mixed $data, string $role = 'input'): array
    {
        return app(CostCalculator::class)->compareModels($data, $role);
    }
}

if (!function_exists('toon_cost_with_json_comparison')) {
    /**
     * Estimate cost with JSON vs TOON comparison
     */
    function toon_cost_with_json_comparison(string $model, mixed $data, string $role = 'input'): array
    {
        return app(CostCalculator::class)->estimateWithJsonComparison($model, $data, $role);
    }
}

if (!function_exists('toon_budget_analysis')) {
    /**
     * Analyze if data fits within budget
     */
    function toon_budget_analysis(string $model, float $budget, mixed $data, string $role = 'input'): array
    {
        return app(CostCalculator::class)->budgetAnalysis($model, $budget, $data, $role);
    }
}

