<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Toon;

class LineWriter
{
    private array $lines = [];
    private int $indent = 0;

    public function __construct(
        private readonly int $indentSize = 2,
    ) {}

    public function line(string $content = ''): void
    {
        $indentation = str_repeat(' ', $this->indent * $this->indentSize);
        $this->lines[] = $indentation.$content;
    }

    public function indent(): void
    {
        ++$this->indent;
    }

    public function dedent(): void
    {
        if ($this->indent > 0) {
            --$this->indent;
        }
    }

    public function getContent(): string
    {
        return implode("\n", $this->lines);
    }

    public function getLines(): array
    {
        return $this->lines;
    }
}

