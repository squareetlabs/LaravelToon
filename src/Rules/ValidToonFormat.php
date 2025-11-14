<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Rules;

use Illuminate\Contracts\Validation\Rule;
use Squareetlabs\LaravelToon\Services\ToonService;

class ValidToonFormat implements Rule
{
    public function __construct(
        private readonly ToonService $toon = new ToonService(),
    ) {}

    public function passes($attribute, $value): bool
    {
        try {
            $this->toon->decode((string)$value);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function message(): string
    {
        return 'El valor no es un formato TOON válido.';
    }
}

