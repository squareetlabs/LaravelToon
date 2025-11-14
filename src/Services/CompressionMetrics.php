<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Services;

class CompressionMetrics
{
    public function __construct(
        private readonly ToonService $toon = new ToonService(),
        private readonly TokenAnalyzer $tokenAnalyzer = new TokenAnalyzer(),
    ) {}

    public function full(mixed $data): array
    {
        $compressed = $this->toon->compress($data);
        $tokenComparison = $this->tokenAnalyzer->compareJsonVsToon($data);

        return [
            'data_summary' => [
                'type' => gettype($data),
                'size' => is_string($data) ? strlen($data) : (is_array($data) ? count($data) : 0),
            ],
            'compression' => [
                'json_size_bytes' => $compressed['original']['size_bytes'],
                'toon_size_bytes' => $compressed['compressed']['size_bytes'],
                'bytes_reduced' => $compressed['metrics']['bytes_reduced'],
                'percent_reduced' => $compressed['metrics']['percent_reduced'],
                'compression_ratio' => $compressed['metrics']['compression_ratio'],
            ],
            'tokens' => [
                'json_tokens' => $tokenComparison['json_tokens'],
                'toon_tokens' => $tokenComparison['toon_tokens'],
                'tokens_saved' => $tokenComparison['tokens_saved'],
                'percent_saved' => $tokenComparison['percent_saved'],
                'efficiency_ratio' => $tokenComparison['efficiency_ratio'],
            ],
            'content' => [
                'original_json' => $compressed['original']['content'],
                'compressed_toon' => $compressed['compressed']['content'],
            ],
            'recommendations' => $this->generateRecommendations($compressed, $tokenComparison),
        ];
    }

    public function summary(mixed $data): array
    {
        $compressed = $this->toon->compress($data);

        return [
            'json_size_bytes' => $compressed['original']['size_bytes'],
            'toon_size_bytes' => $compressed['compressed']['size_bytes'],
            'bytes_saved_percent' => $compressed['metrics']['percent_reduced'],
            'tokens_saved_percent' => $compressed['metrics']['token_percent_reduced'],
        ];
    }

    public function detailed(mixed $data): array
    {
        return $this->full($data);
    }

    public function benchmark(mixed $data, int $iterations = 100): array
    {
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; ++$i) {
            $this->toon->encode($data);
        }
        $encodeTime = microtime(true) - $startTime;

        $startTime = microtime(true);
        $encoded = $this->toon->encode($data);
        for ($i = 0; $i < $iterations; ++$i) {
            $this->toon->decode($encoded);
        }
        $decodeTime = microtime(true) - $startTime;

        return [
            'iterations' => $iterations,
            'encode_time_seconds' => round($encodeTime, 4),
            'encode_time_per_op_ms' => round(($encodeTime / $iterations) * 1000, 4),
            'decode_time_seconds' => round($decodeTime, 4),
            'decode_time_per_op_ms' => round(($decodeTime / $iterations) * 1000, 4),
            'total_time_seconds' => round($encodeTime + $decodeTime, 4),
        ];
    }

    public function compareSizes(mixed $data): array
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $toon = $this->toon->encode($data);
        $toonCompact = $this->toon->encodeCompact($data);
        $toonTabular = $this->toon->encodeTabular($data);

        return [
            'json' => [
                'size_bytes' => strlen($json),
                'size_kb' => round(strlen($json) / 1024, 2),
            ],
            'toon_readable' => [
                'size_bytes' => strlen($toon),
                'size_kb' => round(strlen($toon) / 1024, 2),
            ],
            'toon_compact' => [
                'size_bytes' => strlen($toonCompact),
                'size_kb' => round(strlen($toonCompact) / 1024, 2),
            ],
            'toon_tabular' => [
                'size_bytes' => strlen($toonTabular),
                'size_kb' => round(strlen($toonTabular) / 1024, 2),
            ],
        ];
    }

    private function generateRecommendations(array $compressed, array $tokens): array
    {
        $recommendations = [];

        if ($compressed['metrics']['percent_reduced'] > 70) {
            $recommendations[] = [
                'level' => 'excellent',
                'message' => 'Excelente compresión: mayor al 70% de reducción',
                'action' => 'Use TOON para este tipo de datos en producción',
            ];
        } elseif ($compressed['metrics']['percent_reduced'] > 50) {
            $recommendations[] = [
                'level' => 'good',
                'message' => 'Buena compresión: 50-70% de reducción',
                'action' => 'TOON es altamente recomendado para este contenido',
            ];
        } elseif ($compressed['metrics']['percent_reduced'] > 30) {
            $recommendations[] = [
                'level' => 'moderate',
                'message' => 'Compresión moderada: 30-50% de reducción',
                'action' => 'Considere usar TOON para casos de uso con restricción de tokens',
            ];
        } else {
            $recommendations[] = [
                'level' => 'low',
                'message' => 'Compresión baja: menos del 30%',
                'action' => 'TOON puede no ser ideal para este tipo de datos',
            ];
        }

        if ($tokens['percent_saved'] > 60) {
            $recommendations[] = [
                'level' => 'important',
                'message' => 'Ahorro significativo de tokens: superior al 60%',
                'action' => 'Esto resultará en ahorros sustanciales en costos de API',
            ];
        }

        return $recommendations;
    }
}

