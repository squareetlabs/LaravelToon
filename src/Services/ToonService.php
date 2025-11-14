<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Services;

use Squareetlabs\LaravelToon\Toon\EncodeOptions;
use Squareetlabs\LaravelToon\Toon\Toon;

class ToonService
{
    public function encode(mixed $data, ?EncodeOptions $options = null): string
    {
        return Toon::encode($data, $options ?? new EncodeOptions());
    }

    public function encodeCompact(mixed $data): string
    {
        return Toon::encodeCompact($data);
    }

    public function encodeReadable(mixed $data): string
    {
        return Toon::encodeReadable($data);
    }

    public function encodeTabular(mixed $data): string
    {
        return Toon::encodeTabular($data);
    }

    public function decode(string $toon): mixed
    {
        return Toon::decode($toon);
    }

    public function convert(mixed $data, string $format = 'readable'): string
    {
        return match ($format) {
            'compact' => $this->encodeCompact($data),
            'tabular' => $this->encodeTabular($data),
            'readable' => $this->encodeReadable($data),
            default => $this->encode($data),
        };
    }

    public function estimateTokens(string $content): array
    {
        $chars = strlen($content);
        $words = str_word_count($content);
        $charsPerToken = config('laravel-toon.token_analysis.chars_per_token', 4);
        $estimatedTokens = (int)ceil($chars / $charsPerToken);

        return [
            'words' => $words,
            'chars' => $chars,
            'tokens_estimate' => $estimatedTokens,
            'method' => 'character_ratio',
        ];
    }

    public function compress(mixed $data): array
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $toon = $this->encodeReadable($data);

        $jsonSize = strlen($json);
        $toonSize = strlen($toon);
        $bytesReduced = $jsonSize - $toonSize;
        $percentReduced = $jsonSize > 0 ? (($bytesReduced / $jsonSize) * 100) : 0;

        $jsonTokens = $this->estimateTokens($json)['tokens_estimate'];
        $toonTokens = $this->estimateTokens($toon)['tokens_estimate'];
        $tokensReduced = $jsonTokens - $toonTokens;
        $tokenPercentReduced = $jsonTokens > 0 ? (($tokensReduced / $jsonTokens) * 100) : 0;

        return [
            'success' => true,
            'original' => [
                'format' => 'json',
                'size_bytes' => $jsonSize,
                'content' => $json,
                'tokens_estimate' => $jsonTokens,
            ],
            'compressed' => [
                'format' => 'toon',
                'size_bytes' => $toonSize,
                'content' => $toon,
                'tokens_estimate' => $toonTokens,
            ],
            'metrics' => [
                'bytes_reduced' => $bytesReduced,
                'percent_reduced' => round($percentReduced, 2),
                'tokens_reduced' => $tokensReduced,
                'token_percent_reduced' => round($tokenPercentReduced, 2),
                'compression_ratio' => $jsonSize > 0 ? round($toonSize / $jsonSize, 3) : 0,
            ],
        ];
    }

    public function analyzeCompression(mixed $original): array
    {
        return $this->compress($original);
    }

    public function calculateCompressionRatio(mixed $original): float
    {
        $json = json_encode($original, JSON_THROW_ON_ERROR);
        $toon = $this->encodeReadable($original);

        $jsonSize = strlen($json);
        if (0 === $jsonSize) {
            return 0;
        }

        return round(strlen($toon) / $jsonSize, 3);
    }

    public function compareWithJson(mixed $data): array
    {
        return $this->compress($data);
    }

    public function getMetrics(mixed $data): array
    {
        $compressed = $this->compress($data);

        return [
            'data' => $data,
            'json_size_bytes' => $compressed['original']['size_bytes'],
            'json_tokens' => $compressed['original']['tokens_estimate'],
            'toon_size_bytes' => $compressed['compressed']['size_bytes'],
            'toon_tokens' => $compressed['compressed']['tokens_estimate'],
            'bytes_saved' => $compressed['metrics']['bytes_reduced'],
            'bytes_saved_percent' => $compressed['metrics']['percent_reduced'],
            'tokens_saved' => $compressed['metrics']['tokens_reduced'],
            'tokens_saved_percent' => $compressed['metrics']['token_percent_reduced'],
            'compression_ratio' => $compressed['metrics']['compression_ratio'],
            'original_format' => $compressed['original']['content'],
            'compressed_format' => $compressed['compressed']['content'],
        ];
    }
}

