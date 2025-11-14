<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon;

use Illuminate\Support\ServiceProvider;
use Squareetlabs\LaravelToon\Console\Commands\ToonAnalyzeCommand;
use Squareetlabs\LaravelToon\Console\Commands\ToonBenchmarkCommand;
use Squareetlabs\LaravelToon\Console\Commands\ToonConvertCommand;
use Squareetlabs\LaravelToon\Console\Commands\ToonDashboardCommand;
use Squareetlabs\LaravelToon\Services\CompressionMetrics;
use Squareetlabs\LaravelToon\Services\CostCalculator;
use Squareetlabs\LaravelToon\Services\TokenAnalyzer;
use Squareetlabs\LaravelToon\Services\ToonService;

class LaravelToonServiceProvider extends ServiceProvider
{
    /**
     * Servicios principales de LaravelToon.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-toon.php',
            'laravel-toon'
        );

        // Registrar servicios principales
        $this->app->singleton('toon', function ($app) {
            return new ToonService();
        });

        $this->app->singleton(ToonService::class, function ($app) {
            return $app->make('toon');
        });

        $this->app->singleton(TokenAnalyzer::class, function ($app) {
            return new TokenAnalyzer();
        });

        $this->app->singleton(CompressionMetrics::class, function ($app) {
            return new CompressionMetrics();
        });

        $this->app->singleton(CostCalculator::class, function ($app) {
            return new CostCalculator(config('laravel-toon.cost_calculation.models', []));
        });
    }

    /**
     * Inicializar servicios en la aplicación.
     */
    public function boot(): void
    {
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../config/laravel-toon.php' => config_path('laravel-toon.php'),
        ], 'laravel-toon-config');

        // Registrar comandos Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                ToonConvertCommand::class,
                ToonAnalyzeCommand::class,
                ToonBenchmarkCommand::class,
                ToonDashboardCommand::class,
            ]);
        }
    }
}

