<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Tests\Feature;

use Squareetlabs\LaravelToon\Facades\Toon;
use Squareetlabs\LaravelToon\Services\ToonService;
use Squareetlabs\LaravelToon\Tests\TestCase;

class ToonServiceTest extends TestCase
{
    protected ToonService $toon;

    protected function setUp(): void
    {
        parent::setUp();
        $this->toon = app(ToonService::class);
    }

    public function test_can_encode_simple_data()
    {
        $data = ['name' => 'Juan', 'age' => 30];
        $result = $this->toon->encode($data);

        $this->assertIsString($result);
        $this->assertStringContainsString('name', $result);
        $this->assertStringContainsString('Juan', $result);
    }

    public function test_can_encode_with_facade()
    {
        $data = ['test' => 'data'];
        $result = Toon::encode($data);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_can_decode_toon()
    {
        $data = ['name' => 'Juan', 'email' => 'juan@example.com'];
        $encoded = $this->toon->encode($data);
        $decoded = $this->toon->decode($encoded);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('name', $decoded);
    }

    public function test_compression_reduces_size()
    {
        $data = ['users' => range(1, 100)];
        $metrics = $this->toon->compress($data);

        $this->assertArrayHasKey('metrics', $metrics);
        $this->assertGreaterThan(0, $metrics['metrics']['percent_reduced']);
    }

    public function test_can_get_metrics()
    {
        $data = ['test' => 'data'];
        $metrics = $this->toon->getMetrics($data);

        $this->assertArrayHasKey('json_size_bytes', $metrics);
        $this->assertArrayHasKey('toon_size_bytes', $metrics);
        $this->assertArrayHasKey('compression_ratio', $metrics);
    }

    public function test_can_estimate_tokens()
    {
        $content = 'This is a test content with some words';
        $tokens = $this->toon->estimateTokens($content);

        $this->assertArrayHasKey('tokens_estimate', $tokens);
        $this->assertGreaterThan(0, $tokens['tokens_estimate']);
    }

    public function test_compact_format_is_smaller()
    {
        $data = ['test' => 'data', 'nested' => ['value' => 1, 'value2' => 2]];

        $readable = $this->toon->encodeReadable($data);
        $compact = $this->toon->encodeCompact($data);

        $this->assertLessThanOrEqual(strlen($readable), strlen($compact) + 10);
    }

    public function test_can_compare_with_json()
    {
        $data = ['users' => range(1, 50)];
        $comparison = $this->toon->compareWithJson($data);

        $this->assertArrayHasKey('original', $comparison);
        $this->assertArrayHasKey('compressed', $comparison);
        $this->assertArrayHasKey('metrics', $comparison);
    }

    public function test_compression_ratio_is_valid()
    {
        $data = ['test' => 'data'];
        $ratio = $this->toon->calculateCompressionRatio($data);

        $this->assertGreaterThanOrEqual(0, $ratio);
        $this->assertLessThanOrEqual(1, $ratio);
    }
}

