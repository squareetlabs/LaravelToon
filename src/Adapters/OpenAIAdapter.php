<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Adapters;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class OpenAIAdapter extends BaseLLMAdapter
{
    private ?PendingRequest $client = null;

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function isEnabled(): bool
    {
        return config('laravel-toon.adapters.openai.enabled', false);
    }

    public function sendMessage(
        string $message,
        ?string $model = null,
        ?array $options = null
    ): array {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'OpenAI adapter not enabled'];
        }

        $model ??= $this->getDefaultModel();
        $options ??= [];

        $toonMessage = $this->compressMessage($message);

        $payload = array_merge([
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $toonMessage],
            ],
            'temperature' => 0.7,
        ], $options);

        try {
            $response = $this->getClient()
                ->post('/chat/completions', $payload)
                ->throw()
                ->json();

            return [
                'success' => true,
                'adapter' => 'openai',
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
            return ['success' => false, 'error' => 'OpenAI adapter not enabled'];
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
            'messages' => $compressedMessages,
            'temperature' => 0.7,
        ], $options);

        try {
            $response = $this->getClient()
                ->post('/chat/completions', $payload)
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
                'adapter' => 'openai',
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
        return config('laravel-toon.adapters.openai.default_model', 'gpt-4o');
    }

    public function getAvailableModels(): array
    {
        return [
            'gpt-4o',
            'gpt-4-turbo',
            'gpt-4',
            'gpt-3.5-turbo',
        ];
    }

    private function getClient(): PendingRequest
    {
        if (null === $this->client) {
            $baseUrl = config('laravel-toon.adapters.openai.base_url', 'https://api.openai.com/v1');
            $apiKey = config('laravel-toon.adapters.openai.api_key');
            $timeout = config('laravel-toon.adapters.openai.timeout', 30);

            $this->client = Http::baseUrl($baseUrl)
                ->withHeader('Authorization', 'Bearer '.$apiKey)
                ->withHeader('Content-Type', 'application/json')
                ->timeout($timeout);
        }

        return $this->client;
    }
}

