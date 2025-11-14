<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Facades;

use Illuminate\Support\Facades\Facade;
use Squareetlabs\LaravelToon\Services\ToonService;

/**
 * @method static string encode(mixed $data)
 * @method static mixed decode(string $toon)
 * @method static array estimateTokens(string $content)
 * @method static array compress(mixed $data)
 * @method static array analyzeCompression(mixed $original)
 * @method static float calculateCompressionRatio(mixed $original)
 * @method static array compareWithJson(mixed $data)
 * @method static array getMetrics(mixed $data)
 *
 * @see ToonService
 */
class Toon extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'toon';
    }
}

