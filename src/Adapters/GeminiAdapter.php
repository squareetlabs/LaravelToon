<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Adapters;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GeminiAdapter extends BaseLLMAdapter
{
    private ?PendingRequest $client = null;

    public function getName(): string
    {
        return 'Google Gemini';
    }

    public function isEnabled(): bool
    {
        return config('laravel-toon.adapters.gemini.enabled', false);
    }

    public function sendMessage(
        string $message,
        ?string $model = null,
        ?array $options = null
    ): array {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Gemini adapter not enabled'];
        }

        $model ??= $this->getDefaultModel();
        $options ??= [];

        $toonMessage = $this->compressMessage($message);

        $payload = array_merge([
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $toonMessage]]],
            ],
        ], $options);

        try {
            $apiKey = config('laravel-toon.adapters.gemini.api_key');
            $response = Http::baseUrl('https://generativelanguage.googleapis.com/v1beta/models')
                ->withQueryParameters(['key' => $apiKey])
                ->post("/{$model}:generateContent", $payload)
                ->throw()
                ->json();

            return [
                'success' => true,
                'adapter' => 'gemini',
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
            return ['success' => false, 'error' => 'Gemini adapter not enabled'];
        }

        $model ??= $this->getDefaultModel();
        $options ??= [];

        // Compress messages
        $compressedMessages = array_map(fn ($msg) => [
            'role' => $msg['role'],
            'parts' => [['text' => $this->compressMessage($msg['content'])]],
        ], $messages);

        $payload = array_merge([
            'contents' => $compressedMessages,
        ], $options);

        try {
            $apiKey = config('laravel-toon.adapters.gemini.api_key');
            $response = Http::baseUrl('https://generativelanguage.googleapis.com/v1beta/models')
                ->withQueryParameters(['key' => $apiKey])
                ->post("/{$model}:generateContent", $payload)
                ->throw()
                ->json();

            $originalTokens = array_sum(array_map(
                fn ($msg) => $this->tokenAnalyzer->estimate($msg['content']),
                $messages
            ));

            $compressedTokens = array_sum(array_map(
                fn ($msg) => $this->tokenAnalyzer->estimate($msg['content']),
                $messages
            ));

            return [
                'success' => true,
                'adapter' => 'gemini',
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
        return config('laravel-toon.adapters.gemini.default_model', 'gemini-pro');
    }

    public function getAvailableModels(): array
    {
        return [
            'gemini-pro',
            'gemini-pro-vision',
        ];
    }
}

