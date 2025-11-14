<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Squareetlabs\LaravelToon\Services\CompressionMetrics;
use Squareetlabs\LaravelToon\Services\ToonService;

class CompressResponse
{
    public function __construct(
        private readonly ToonService $toon,
        private readonly CompressionMetrics $metrics,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('laravel-toon.middleware.auto_compress', false)) {
            return $response;
        }

        if (!$response->isSuccessful() || !$response->headers->has('Content-Type')) {
            return $response;
        }

        if (!str_contains($response->headers->get('Content-Type', ''), 'application/json')) {
            return $response;
        }

        $content = $response->getContent();
        if (false === $content || strlen($content) < config('laravel-toon.middleware.min_response_size', 1024)) {
            return $response;
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $compressed = $this->toon->compress($data);

            $compressionPercent = $compressed['metrics']['percent_reduced'];
            $threshold = config('laravel-toon.middleware.compression_threshold', 50);

            if ($compressionPercent >= $threshold) {
                $response->setContent($compressed['compressed']['content']);
                $response->headers->set('X-Content-Compressed', 'true');
                $response->headers->set('X-Compression-Percent', round($compressionPercent, 2));
                $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
            }
        } catch (\Exception) {
            // Si hay error, devolver respuesta original
        }

        return $response;
    }
}

