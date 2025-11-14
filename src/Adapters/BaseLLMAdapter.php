<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Adapters;

use Squareetlabs\LaravelToon\Services\CostCalculator;
use Squareetlabs\LaravelToon\Services\TokenAnalyzer;
use Squareetlabs\LaravelToon\Services\ToonService;

abstract class BaseLLMAdapter
{
    protected ToonService $toon;
    protected TokenAnalyzer $tokenAnalyzer;
    protected CostCalculator $costCalculator;

    public function __construct()
    {
        $this->toon = app(ToonService::class);
        $this->tokenAnalyzer = app(TokenAnalyzer::class);
        $this->costCalculator = app(CostCalculator::class);
    }

    abstract public function getName(): string;

    abstract public function isEnabled(): bool;

    abstract public function sendMessage(
        string $message,
        ?string $model = null,
        ?array $options = null
    ): array;

    abstract public function chat(
        array $messages,
        ?string $model = null,
        ?array $options = null
    ): array;

    public function compressMessage(string $message, string $format = 'compact'): string
    {
        return $this->toon->convert($message, $format);
    }

    public function compressData(mixed $data, string $format = 'compact'): string
    {
        return $this->toon->convert($data, $format);
    }

    public function estimateMessageCost(string $message, ?string $model = null, string $role = 'input'): array
    {
        $model ??= $this->getDefaultModel();

        return $this->costCalculator->estimateCost($model, $message, $role);
    }

    public function analyzeMessage(string $message): array
    {
        return [
            'message' => $message,
            'original_tokens' => $this->tokenAnalyzer->estimateJson(['content' => $message])['tokens_estimate'],
            'compressed_tokens' => $this->tokenAnalyzer->estimate($message),
            'analysis' => $this->tokenAnalyzer->analyze($message),
        ];
    }

    abstract public function getDefaultModel(): string;

    abstract public function getAvailableModels(): array;
}

