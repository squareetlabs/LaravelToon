<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Adapters;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AnthropicAdapter extends BaseLLMAdapter
{
    private ?PendingRequest $client = null;

    public function getName(): string
    {
        return 'Anthropic/Claude';
    }

    public function isEnabled(): bool
    {
        return config('laravel-toon.adapters.anthropic.enabled', false);
    }

    public function sendMessage(
        string $message,
        ?string $model = null,
        ?array $options = null
    ): array {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Anthropic adapter not enabled'];
        }

        $model ??= $this->getDefaultModel();
        $options ??= [];

        $toonMessage = $this->compressMessage($message);

        $payload = array_merge([
            'model' => $model,
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $toonMessage],
            ],
        ], $options);

        try {
            $response = $this->getClient()
                ->post('/messages', $payload)
                ->throw()
                ->json();

            return [
                'success' => true,
                'adapter' => 'anthropic',
                'model' => $model,
                'original_message_tokens' => $this->tokenAnalyzer->estimate($message),
                'compressed_message_tokens' => $this->tokenAnalyzer->estimate($toonMessage),
                'response' => $response,
                'compressed_message' => $toonMessage,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function chat(
        array $messages,
        ?string $model = null,
        ?array $options = null
    ): array {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Anthropic adapter not enabled'];
        }

        $model ??= $this->getDefaultModel();
        $options ??= [];

        // Compress messages
        $compressedMessages = array_map(fn ($msg) => [
            ...$msg,
            'content' => $this->compressMessage($msg['content']),
        ], $messages);

        $payload = array_merge([
            'model' => $model,
            'max_tokens' => 2048,
            'messages' => $compressedMessages,
        ], $options);

        try {
            $response = $this->getClient()
                ->post('/messages', $payload)
                ->throw()
                ->json();

            $originalTokens = array_sum(array_map(
                fn ($msg) => $this->tokenAnalyzer->estimate($msg['content']),
                $messages
            ));

            $compressedTokens = array_sum(array_map(
                fn ($msg) => $this->tokenAnalyzer->estimate($msg['content']),
                $compressedMessages
            ));

            return [
                'success' => true,
                'adapter' => 'anthropic',
                'model' => $model,
                'messages_count' => count($messages),
                'original_tokens' => $originalTokens,
                'compressed_tokens' => $compressedTokens,
                'tokens_saved' => $originalTokens - $compressedTokens,
                'response' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getDefaultModel(): string
    {
        return config('laravel-toon.adapters.anthropic.default_model', 'claude-3-sonnet-20240229');
    }

    public function getAvailableModels(): array
    {
        return [
            'claude-3-opus-20240229',
            'claude-3-sonnet-20240229',
            'claude-3-haiku-20240307',
            'claude-2.1',
            'claude-2',
        ];
    }

    private function getClient(): PendingRequest
    {
        if (null === $this->client) {
            $baseUrl = config('laravel-toon.adapters.anthropic.base_url', 'https://api.anthropic.com');
            $apiKey = config('laravel-toon.adapters.anthropic.api_key');
            $timeout = config('laravel-toon.adapters.anthropic.timeout', 30);

            $this->client = Http::baseUrl($baseUrl)
                ->withHeader('x-api-key', $apiKey)
                ->withHeader('anthropic-version', '2023-06-01')
                ->withHeader('content-type', 'application/json')
                ->timeout($timeout);
        }

        return $this->client;
    }
}

