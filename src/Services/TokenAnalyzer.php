<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Services;

use Illuminate\Support\Facades\Cache;

class TokenAnalyzer
{
    private const CACHE_PREFIX = 'laravel-toon:tokens:';

    public function __construct(
        private readonly ToonService $toon = new ToonService(),
    ) {}

    public function estimate(string $content): int
    {
        $cacheKey = self::CACHE_PREFIX.md5($content);

        if (config('laravel-toon.token_analysis.cache_results', true)) {
            $cached = Cache::store(config('laravel-toon.cache.store', 'file'))
                ->get($cacheKey);
            if (null !== $cached) {
                return $cached;
            }
        }

        $tokens = $this->calculateTokens($content);

        if (config('laravel-toon.token_analysis.cache_results', true)) {
            Cache::store(config('laravel-toon.cache.store', 'file'))
                ->put($cacheKey, $tokens, config('laravel-toon.token_analysis.cache_ttl', 3600));
        }

        return $tokens;
    }

    public function estimateJson(mixed $data): int
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->estimate($json);
    }

    public function estimateToon(mixed $data): int
    {
        $toon = $this->toon->encode($data);

        return $this->estimate($toon);
    }

    public function compareJsonVsToon(mixed $data): array
    {
        $jsonTokens = $this->estimateJson($data);
        $toonTokens = $this->estimateToon($data);
        $tokensSaved = $jsonTokens - $toonTokens;
        $percentSaved = $jsonTokens > 0 ? (($tokensSaved / $jsonTokens) * 100) : 0;

        return [
            'json_tokens' => $jsonTokens,
            'toon_tokens' => $toonTokens,
            'tokens_saved' => $tokensSaved,
            'percent_saved' => round($percentSaved, 2),
            'efficiency_ratio' => $jsonTokens > 0 ? round($toonTokens / $jsonTokens, 3) : 0,
        ];
    }

    public function analyze(string $content): array
    {
        $tokens = $this->estimate($content);
        $chars = strlen($content);
        $words = str_word_count($content);

        return [
            'content' => $content,
            'length_chars' => $chars,
            'length_words' => $words,
            'tokens_estimated' => $tokens,
            'chars_per_token' => $chars > 0 ? round($chars / $tokens, 2) : 0,
            'analysis_method' => config('laravel-toon.token_analysis.estimate_method', 'character_ratio'),
        ];
    }

    public function analyzeJson(mixed $data): array
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->analyze($json);
    }

    public function analyzeToon(mixed $data): array
    {
        $toon = $this->toon->encode($data);

        return $this->analyze($toon);
    }

    public function budgetTokens(int $maxTokens, mixed $data): array
    {
        $toonTokens = $this->estimateToon($data);
        $available = $maxTokens - $toonTokens;
        $percentUsed = $maxTokens > 0 ? (($toonTokens / $maxTokens) * 100) : 0;
        $percentAvailable = 100 - $percentUsed;

        return [
            'max_tokens' => $maxTokens,
            'tokens_used' => $toonTokens,
            'tokens_available' => max(0, $available),
            'percent_used' => round($percentUsed, 2),
            'percent_available' => round($percentAvailable, 2),
            'within_budget' => $toonTokens <= $maxTokens,
        ];
    }

    private function calculateTokens(string $content): int
    {
        $method = config('laravel-toon.token_analysis.estimate_method', 'character_ratio');

        return match ($method) {
            'word_count' => $this->estimateByWordCount($content),
            'character_ratio' => $this->estimateByCharacterRatio($content),
            default => $this->estimateByCharacterRatio($content),
        };
    }

    private function estimateByCharacterRatio(string $content): int
    {
        $chars = strlen($content);
        $charsPerToken = config('laravel-toon.token_analysis.chars_per_token', 4);

        return (int)ceil($chars / $charsPerToken);
    }

    private function estimateByWordCount(string $content): int
    {
        $words = str_word_count($content);

        // Average: 1.3 tokens per word
        return (int)ceil($words * 1.3);
    }

    public function clearCache(): void
    {
        Cache::store(config('laravel-toon.cache.store', 'file'))
            ->forget(self::CACHE_PREFIX.'*');
    }
}

