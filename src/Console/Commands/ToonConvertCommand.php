<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Console\Commands;

use Illuminate\Console\Command;
use Squareetlabs\LaravelToon\Services\ToonService;

class ToonConvertCommand extends Command
{
    protected $signature = 'toon:convert 
        {file : Ruta del archivo a convertir}
        {--format=readable : Formato de salida (readable, compact, tabular)}
        {--output= : Ruta del archivo de salida (opcional)}
        {--decode : Decodificar de TOON a JSON}
        {--pretty : Pretty print JSON}';

    protected $description = 'Convierte archivos JSON a TOON o viceversa';

    public function handle(ToonService $toonService): int
    {
        $file = $this->argument('file');
        $format = $this->option('format');
        $output = $this->option('output');
        $decode = $this->option('decode');
        $pretty = $this->option('pretty');

        if (!file_exists($file)) {
            $this->error("Archivo no encontrado: {$file}");

            return self::FAILURE;
        }

        $content = file_get_contents($file);
        if (false === $content) {
            $this->error("No se pudo leer el archivo: {$file}");

            return self::FAILURE;
        }

        $this->info('Procesando archivo...');
        $this->newLine();

        try {
            if ($decode) {
                $result = $toonService->decode($content);
                if ($pretty) {
                    $result = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                } else {
                    $result = json_encode($result, JSON_THROW_ON_ERROR);
                }
                $outputFormat = 'JSON';
            } else {
                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                $result = $toonService->convert($data, $format);
                $outputFormat = strtoupper($format).' TOON';
            }

            if ($output) {
                file_put_contents($output, $result);
                $this->info("✓ Archivo guardado: {$output}");
            } else {
                $this->line($result);
            }

            $this->newLine();
            $this->info("Conversión completada exitosamente a {$outputFormat}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error durante la conversión: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}

