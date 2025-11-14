<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Toon;

class EncodeOptions
{
    public function __construct(
        public readonly int $indent = 2,
        public readonly string $delimiter = ',',
        public readonly bool $prettyPrint = false,
        public readonly int $minRowsToTabular = 2,
        public readonly int $maxPreviewItems = 200,
    ) {}

    public static function compact(): self
    {
        return new self(
            indent: 0,
            delimiter: ',',
            prettyPrint: false,
            minRowsToTabular: 2,
        );
    }

    public static function readable(): self
    {
        return new self(
            indent: 2,
            delimiter: ',',
            prettyPrint: true,
            minRowsToTabular: 2,
        );
    }

    public static function tabular(): self
    {
        return new self(
            indent: 0,
            delimiter: "\t",
            prettyPrint: false,
            minRowsToTabular: 1,
        );
    }
}

