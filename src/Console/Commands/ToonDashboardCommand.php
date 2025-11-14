<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Console\Commands;

use Illuminate\Console\Command;
use Squareetlabs\LaravelToon\Services\CompressionMetrics;
use Squareetlabs\LaravelToon\Services\CostCalculator;
use Squareetlabs\LaravelToon\Services\ToonService;

class ToonDashboardCommand extends Command
{
    protected $signature = 'toon:dashboard';

    protected $description = 'Dashboard interactivo para LaravelToon';

    public function handle(
        ToonService $toonService,
        CompressionMetrics $metrics,
        CostCalculator $costCalculator
    ): int {
        $this->displayHeader();

        while (true) {
            $this->newLine();
            $option = $this->choice('¿Qué desea hacer?', [
                'Convertir JSON a TOON',
                'Decodificar TOON a JSON',
                'Analizar compresión',
                'Estimar costos de API',
                'Ver precios de modelos',
                'Salir',
            ]);

            match ($option) {
                'Convertir JSON a TOON' => $this->convertJsonToToon($toonService),
                'Decodificar TOON a JSON' => $this->decodeToonToJson($toonService),
                'Analizar compresión' => $this->analyzeCompression($metrics),
                'Estimar costos de API' => $this->estimateCosts($costCalculator),
                'Ver precios de modelos' => $this->viewModelPrices($costCalculator),
                'Salir' => break,
            };
        }

        $this->info('¡Hasta luego!');

        return self::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║      🧠 LaravelToon Dashboard 🧠        ║');
        $this->info('║  Token-Optimized Object Notation       ║');
        $this->info('╚════════════════════════════════════════╝');
    }

    private function convertJsonToToon(ToonService $toonService): void
    {
        $input = $this->ask('Ingrese JSON (o ruta de archivo)');

        if (file_exists($input)) {
            $json = file_get_contents($input);
        } else {
            $json = $input;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            $format = $this->choice('Seleccione formato:', ['compact', 'readable', 'tabular']);
            $result = $toonService->convert($data, $format);

            $this->newLine();
            $this->info('Resultado TOON:');
            $this->line($result);

            if ($this->confirm('¿Guardar en archivo?')) {
                $filename = $this->ask('Nombre del archivo (sin extensión)', 'output.toon');
                file_put_contents($filename, $result);
                $this->info("✓ Guardado en: {$filename}");
            }
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    private function decodeToonToJson(ToonService $toonService): void
    {
        $input = $this->ask('Ingrese TOON (o ruta de archivo)');

        if (file_exists($input)) {
            $toon = file_get_contents($input);
        } else {
            $toon = $input;
        }

        try {
            $data = $toonService->decode($toon);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $this->newLine();
            $this->info('Resultado JSON:');
            $this->line($json);

            if ($this->confirm('¿Guardar en archivo?')) {
                $filename = $this->ask('Nombre del archivo (sin extensión)', 'output.json');
                file_put_contents($filename, $json);
                $this->info("✓ Guardado en: {$filename}");
            }
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    private function analyzeCompression(CompressionMetrics $metrics): void
    {
        $input = $this->ask('Ingrese JSON (o ruta de archivo)');

        if (file_exists($input)) {
            $json = file_get_contents($input);
        } else {
            $json = $input;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $analysis = $metrics->full($data);

            $this->newLine();
            $this->info('📊 ANÁLISIS DE COMPRESIÓN:');
            $this->table(['Métrica', 'Valor'], [
                ['JSON Size', number_format($analysis['compression']['json_size_bytes']).' bytes'],
                ['TOON Size', number_format($analysis['compression']['toon_size_bytes']).' bytes'],
                ['Bytes Reducidos', $analysis['compression']['percent_reduced'].'%'],
                ['Tokens Reducidos', $analysis['tokens']['percent_saved'].'%'],
                ['Ratio', $analysis['compression']['compression_ratio']],
            ]);

            $this->info('Recomendaciones:');
            foreach ($analysis['recommendations'] as $rec) {
                $this->line("  ✓ {$rec['message']}");
            }
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    private function estimateCosts(CostCalculator $costCalculator): void
    {
        $input = $this->ask('Ingrese JSON (o ruta de archivo)');

        if (file_exists($input)) {
            $json = file_get_contents($input);
        } else {
            $json = $input;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            $models = $costCalculator->getAvailableModels();
            $modelList = array_keys($models);
            $model = $this->choice('Seleccione modelo:', $modelList);

            $comparison = $costCalculator->estimateWithJsonComparison($model, $data, 'input');

            $this->newLine();
            $this->info("💰 ESTIMACIÓN DE COSTOS - {$model}:");
            $this->table(['Formato', 'Tokens', 'Costo'], [
                ['JSON', number_format($comparison['json']['tokens']), $comparison['json']['cost_formatted']],
                ['TOON', number_format($comparison['toon']['tokens']), $comparison['toon']['cost_formatted']],
                ['Ahorros', number_format($comparison['savings']['tokens']), $comparison['savings']['cost_formatted'].' ('.$comparison['savings']['percent'].'%)'],
            ]);
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }

    private function viewModelPrices(CostCalculator $costCalculator): void
    {
        $models = $costCalculator->getAvailableModels();

        $this->newLine();
        $this->info('💵 PRECIOS DE MODELOS (por 1M tokens):');
        $this->newLine();

        foreach ($models as $model => $roles) {
            $this->info("<fg=cyan>{$model}</>");
            $prices = config('laravel-toon.cost_calculation.models.'.$model, []);
            foreach ($roles as $role) {
                $price = $prices[$role] ?? 'N/A';
                $this->line("  {$role}: \${$price}");
            }
            $this->newLine();
        }
    }
}

