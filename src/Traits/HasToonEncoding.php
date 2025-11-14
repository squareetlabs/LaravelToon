<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Traits;

use Squareetlabs\LaravelToon\Services\ToonService;

/**
 * Trait para agregar capacidades TOON a modelos Eloquent o clases
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasToonEncoding
{
    public function toToon(string $format = 'readable'): string
    {
        return app(ToonService::class)->convert($this->toArray(), $format);
    }

    public function toToonCompact(): string
    {
        return app(ToonService::class)->encodeCompact($this->toArray());
    }

    public function toToonReadable(): string
    {
        return app(ToonService::class)->encodeReadable($this->toArray());
    }

    public function getToonMetrics(): array
    {
        return app(ToonService::class)->getMetrics($this->toArray());
    }

    public function getToonCompressionRatio(): float
    {
        return app(ToonService::class)->calculateCompressionRatio($this->toArray());
    }
}

