<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Squareetlabs\LaravelToon\LaravelToonServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelToonServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Toon' => 'Squareetlabs\LaravelToon\Facades\Toon',
        ];
    }
}

