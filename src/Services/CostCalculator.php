<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Services;

class CostCalculator
{
    public function __construct(
        private readonly array $models = [],
        private readonly ToonService $toon = new ToonService(),
        private readonly TokenAnalyzer $tokenAnalyzer = new TokenAnalyzer(),
    ) {}

    public function estimateCost(string $model, mixed $data, string $role = 'input'): array
    {
        $tokens = $this->tokenAnalyzer->estimateToon($data);
        $costPerMillionTokens = $this->getPricePerMillionTokens($model, $role);

        if (null === $costPerMillionTokens) {
            return [
                'success' => false,
                'error' => "Modelo '{$model}' no configurado o rol '{$role}' no válido",
            ];
        }

        $cost = ($tokens / 1_000_000) * $costPerMillionTokens;

        return [
            'success' => true,
            'model' => $model,
            'role' => $role,
            'tokens' => $tokens,
            'cost_per_million' => $costPerMillionTokens,
            'cost' => round($cost, 6),
            'cost_formatted' => '$'.number_format($cost, 4),
        ];
    }

    public function compareModels(mixed $data, string $role = 'input'): array
    {
        $tokens = $this->tokenAnalyzer->estimateToon($data);
        $results = [];

        foreach ($this->models as $model => $prices) {
            if (isset($prices[$role])) {
                $costPerMillionTokens = $prices[$role];
                $cost = ($tokens / 1_000_000) * $costPerMillionTokens;

                $results[$model] = [
                    'tokens' => $tokens,
                    'cost_per_million' => $costPerMillionTokens,
                    'cost' => round($cost, 6),
                    'cost_formatted' => '$'.number_format($cost, 4),
                ];
            }
        }

        // Sort by cost (lowest first)
        uasort($results, fn ($a, $b) => $a['cost'] <=> $b['cost']);

        return [
            'tokens' => $tokens,
            'role' => $role,
            'models' => $results,
            'cheapest' => array_key_first($results) ?? null,
            'most_expensive' => array_key_last($results) ?? null,
        ];
    }

    public function estimateWithJsonComparison(string $model, mixed $data, string $role = 'input'): array
    {
        $jsonTokens = $this->tokenAnalyzer->estimateJson($data);
        $toonTokens = $this->tokenAnalyzer->estimateToon($data);

        $costPerMillionTokens = $this->getPricePerMillionTokens($model, $role);

        if (null === $costPerMillionTokens) {
            return [
                'success' => false,
                'error' => "Modelo '{$model}' no configurado o rol '{$role}' no válido",
            ];
        }

        $jsonCost = ($jsonTokens / 1_000_000) * $costPerMillionTokens;
        $toonCost = ($toonTokens / 1_000_000) * $costPerMillionTokens;
        $savings = $jsonCost - $toonCost;
        $savingsPercent = $jsonCost > 0 ? (($savings / $jsonCost) * 100) : 0;

        return [
            'success' => true,
            'model' => $model,
            'role' => $role,
            'json' => [
                'tokens' => $jsonTokens,
                'cost' => round($jsonCost, 6),
                'cost_formatted' => '$'.number_format($jsonCost, 4),
            ],
            'toon' => [
                'tokens' => $toonTokens,
                'cost' => round($toonCost, 6),
                'cost_formatted' => '$'.number_format($toonCost, 4),
            ],
            'savings' => [
                'tokens' => $jsonTokens - $toonTokens,
                'cost' => round($savings, 6),
                'cost_formatted' => '$'.number_format($savings, 4),
                'percent' => round($savingsPercent, 2),
            ],
        ];
    }

    public function estimateBatchCost(string $model, array $dataItems, string $role = 'input'): array
    {
        $totalTokens = 0;
        $costs = [];

        foreach ($dataItems as $index => $item) {
            $tokens = $this->tokenAnalyzer->estimateToon($item);
            $totalTokens += $tokens;
            $costPerMillionTokens = $this->getPricePerMillionTokens($model, $role);

            if (null === $costPerMillionTokens) {
                continue;
            }

            $cost = ($tokens / 1_000_000) * $costPerMillionTokens;
            $costs[$index] = [
                'tokens' => $tokens,
                'cost' => round($cost, 6),
            ];
        }

        $costPerMillionTokens = $this->getPricePerMillionTokens($model, $role);
        $totalCost = null === $costPerMillionTokens ? 0 : ($totalTokens / 1_000_000) * $costPerMillionTokens;

        return [
            'success' => null !== $costPerMillionTokens,
            'model' => $model,
            'role' => $role,
            'items_count' => count($dataItems),
            'items' => $costs,
            'total_tokens' => $totalTokens,
            'total_cost' => round($totalCost, 6),
            'total_cost_formatted' => '$'.number_format($totalCost, 4),
            'average_cost_per_item' => count($costs) > 0 ? round($totalCost / count($costs), 6) : 0,
        ];
    }

    public function budgetAnalysis(string $model, float $budget, mixed $data, string $role = 'input'): array
    {
        $costResult = $this->estimateCost($model, $data, $role);

        if (!$costResult['success']) {
            return [
                'success' => false,
                'error' => $costResult['error'],
            ];
        }

        $costPerRequest = $costResult['cost'];
        $requestsAffordable = (int)floor($budget / $costPerRequest);
        $percentBudgetUsed = $budget > 0 ? (($costPerRequest / $budget) * 100) : 0;

        return [
            'success' => true,
            'model' => $model,
            'budget' => round($budget, 2),
            'cost_per_request' => round($costPerRequest, 6),
            'cost_per_request_formatted' => '$'.number_format($costPerRequest, 4),
            'requests_affordable' => $requestsAffordable,
            'percent_budget_per_request' => round($percentBudgetUsed, 2),
            'remaining_budget_after_one_request' => round($budget - $costPerRequest, 6),
        ];
    }

    public function priceComparison(mixed $data, string $role = 'input'): array
    {
        $tokens = $this->tokenAnalyzer->estimateToon($data);
        $comparison = [];

        foreach ($this->models as $model => $prices) {
            if (isset($prices[$role])) {
                $costPerMillionTokens = $prices[$role];
                $cost = ($tokens / 1_000_000) * $costPerMillionTokens;

                $comparison[$model] = [
                    'cost_per_million' => $costPerMillionTokens,
                    'cost_for_this_request' => round($cost, 6),
                    'cost_for_1m_tokens' => '$'.number_format($costPerMillionTokens, 4),
                    'cost_for_this_request_formatted' => '$'.number_format($cost, 4),
                ];
            }
        }

        // Sort by cost
        uasort($comparison, fn ($a, $b) => $a['cost_for_this_request'] <=> $b['cost_for_this_request']);

        return [
            'tokens' => $tokens,
            'role' => $role,
            'models' => $comparison,
        ];
    }

    private function getPricePerMillionTokens(string $model, string $role): ?float
    {
        // First check in instance models
        if (isset($this->models[$model][$role])) {
            return $this->models[$model][$role];
        }

        // Then check in config
        $configModels = config('laravel-toon.cost_calculation.models', []);
        if (isset($configModels[$model][$role])) {
            return $configModels[$model][$role];
        }

        return null;
    }

    public function getAvailableModels(): array
    {
        $configModels = config('laravel-toon.cost_calculation.models', []);
        $all = array_merge($this->models, $configModels);

        $result = [];
        foreach ($all as $model => $prices) {
            $result[$model] = array_keys($prices);
        }

        return $result;
    }
}

