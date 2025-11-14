<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Console\Commands;

use Illuminate\Console\Command;
use Squareetlabs\LaravelToon\Services\CompressionMetrics;
use Squareetlabs\LaravelToon\Services\CostCalculator;

class ToonBenchmarkCommand extends Command
{
    protected $signature = 'toon:benchmark 
        {file : Ruta del archivo JSON}
        {--iterations=100 : Número de iteraciones}
        {--model= : Modelo LLM para comparar costos}';

    protected $description = 'Ejecuta benchmark de rendimiento y estimación de costos';

    public function handle(
        CompressionMetrics $metrics,
        CostCalculator $costCalculator
    ): int {
        $file = $this->argument('file');
        $iterations = (int)$this->option('iterations');
        $model = $this->option('model');

        if (!file_exists($file)) {
            $this->error("Archivo no encontrado: {$file}");

            return self::FAILURE;
        }

        $content = file_get_contents($file);
        if (false === $content) {
            $this->error("No se pudo leer el archivo: {$file}");

            return self::FAILURE;
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $this->info('⚡ BENCHMARK DE RENDIMIENTO Y COSTOS');
            $this->newLine();

            // Performance benchmark
            $this->info('Ejecutando benchmark de rendimiento...');
            $benchmark = $metrics->benchmark($data, $iterations);

            $this->table(['Métrica', 'Valor'], [
                ['Iteraciones', number_format($benchmark['iterations'])],
                ['Tiempo de codificación (total)', $benchmark['encode_time_seconds'].' s'],
                ['Tiempo por operación (encode)', $benchmark['encode_time_per_op_ms'].' ms'],
                ['Tiempo de decodificación (total)', $benchmark['decode_time_seconds'].' s'],
                ['Tiempo por operación (decode)', $benchmark['decode_time_per_op_ms'].' ms'],
                ['Tiempo total', $benchmark['total_time_seconds'].' s'],
            ]);

            // Cost comparison
            $this->newLine();
            $this->info('Comparación de tamaños:');

            $sizes = $metrics->compareSizes($data);
            $this->table(['Formato', 'Bytes', 'KB'], [
                ['JSON', number_format($sizes['json']['size_bytes']), $sizes['json']['size_kb']],
                ['TOON Readable', number_format($sizes['toon_readable']['size_bytes']), $sizes['toon_readable']['size_kb']],
                ['TOON Compact', number_format($sizes['toon_compact']['size_bytes']), $sizes['toon_compact']['size_kb']],
                ['TOON Tabular', number_format($sizes['toon_tabular']['size_bytes']), $sizes['toon_tabular']['size_kb']],
            ]);

            // Cost estimation if model is provided
            if ($model) {
                $this->newLine();
                $this->info('Estimación de costos de API:');

                $costComparison = $costCalculator->estimateWithJsonComparison($model, $data, 'input');

                if ($costComparison['success']) {
                    $this->table(['Formato', 'Tokens', 'Costo'], [
                        ['JSON', number_format($costComparison['json']['tokens']), $costComparison['json']['cost_formatted']],
                        ['TOON', number_format($costComparison['toon']['tokens']), $costComparison['toon']['cost_formatted']],
                        ['Ahorros', number_format($costComparison['savings']['tokens']), $costComparison['savings']['cost_formatted'].' ('.$costComparison['savings']['percent'].'%)'],
                    ]);
                } else {
                    $this->warn('Modelo no encontrado: '.$model);
                }
            }

            $this->newLine();
            $this->info('✓ Benchmark completado exitosamente');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error durante benchmark: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

