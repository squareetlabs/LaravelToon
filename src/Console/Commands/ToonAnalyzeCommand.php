<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Console\Commands;

use Illuminate\Console\Command;
use Squareetlabs\LaravelToon\Services\CompressionMetrics;
use Squareetlabs\LaravelToon\Services\ToonService;

class ToonAnalyzeCommand extends Command
{
    protected $signature = 'toon:analyze 
        {file : Ruta del archivo JSON a analizar}
        {--verbose : Mostrar análisis detallado}';

    protected $description = 'Analiza compresión y eficiencia de TOON vs JSON';

    public function handle(
        ToonService $toonService,
        CompressionMetrics $metrics
    ): int {
        $file = $this->argument('file');
        $verbose = $this->option('verbose');

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

            $this->info('📊 ANÁLISIS DE COMPRESIÓN TOON');
            $this->newLine();

            if ($verbose) {
                $full = $metrics->full($data);

                // Data summary
                $this->info('Resumen de Datos:');
                $this->table(['Propiedad', 'Valor'], [
                    ['Tipo', $full['data_summary']['type']],
                    ['Tamaño', $full['data_summary']['size']],
                ]);

                // Compression metrics
                $this->info('Compresión:');
                $this->table(['Métrica', 'Valor'], [
                    ['Tamaño JSON', number_format($full['compression']['json_size_bytes']).' bytes'],
                    ['Tamaño TOON', number_format($full['compression']['toon_size_bytes']).' bytes'],
                    ['Bytes reducidos', number_format($full['compression']['bytes_reduced']).' bytes'],
                    ['% Reducción', $full['compression']['percent_reduced'].'%'],
                    ['Ratio compresión', $full['compression']['compression_ratio']],
                ]);

                // Token metrics
                $this->info('Tokens:');
                $this->table(['Métrica', 'Valor'], [
                    ['Tokens JSON', number_format($full['tokens']['json_tokens'])],
                    ['Tokens TOON', number_format($full['tokens']['toon_tokens'])],
                    ['Tokens ahorrados', number_format($full['tokens']['tokens_saved'])],
                    ['% Ahorrado', $full['tokens']['percent_saved'].'%'],
                ]);

                // Recommendations
                if (!empty($full['recommendations'])) {
                    $this->info('Recomendaciones:');
                    foreach ($full['recommendations'] as $rec) {
                        $icon = match ($rec['level']) {
                            'excellent' => '✓',
                            'good' => '→',
                            'moderate' => '~',
                            'important' => '!',
                            default => '•',
                        };
                        $this->info("  {$icon} {$rec['message']}");
                        $this->line("    {$rec['action']}");
                    }
                }

                $this->newLine();

                if ($this->confirm('¿Desea ver el contenido comprimido?')) {
                    $this->info('JSON Original:');
                    $this->line($full['content']['original_json']);
                    $this->newLine();
                    $this->info('TOON Comprimido:');
                    $this->line($full['content']['compressed_toon']);
                }
            } else {
                $summary = $metrics->summary($data);

                $this->table(['Métrica', 'Valor'], [
                    ['JSON Size', number_format($summary['json_size_bytes']).' bytes'],
                    ['TOON Size', number_format($summary['toon_size_bytes']).' bytes'],
                    ['Bytes Saved', $summary['bytes_saved_percent'].'%'],
                    ['Tokens Saved', $summary['tokens_saved_percent'].'%'],
                ]);
            }

            $this->newLine();
            $this->info('✓ Análisis completado');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error durante el análisis: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

