<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Toon;

class Toon
{
    public static function encode(mixed $data, ?EncodeOptions $options = null): string
    {
        $options ??= new EncodeOptions();

        return Encoders::encode($data, $options);
    }

    public static function encodeCompact(mixed $data): string
    {
        return self::encode($data, EncodeOptions::compact());
    }

    public static function encodeReadable(mixed $data): string
    {
        return self::encode($data, EncodeOptions::readable());
    }

    public static function encodeTabular(mixed $data): string
    {
        return self::encode($data, EncodeOptions::tabular());
    }

    public static function decode(string $toon): mixed
    {
        $lines = explode("\n", trim($toon));

        return self::parseLines($lines);
    }

    private static function parseLines(array $lines, int &$index = 0, int $expectedIndent = 0): mixed
    {
        $result = [];
        $isObject = false;
        $isList = false;

        while ($index < count($lines)) {
            $line = $lines[$index];
            $indent = self::getIndent($line);
            $content = trim($line);

            if ('' === $content) {
                ++$index;

                continue;
            }

            if ($indent < $expectedIndent) {
                break;
            }

            if ($indent > $expectedIndent) {
                ++$index;

                continue;
            }

            if (str_contains($content, Constants::OBJECT_MARKER)) {
                $isObject = true;
                [$key, $value] = self::parseObjectLine($content);
                ++$index;

                if ($index < count($lines)) {
                    $nextLine = $lines[$index];
                    $nextIndent = self::getIndent($nextLine);
                    if ($nextIndent > $expectedIndent) {
                        $result[$key] = self::parseLines($lines, $index, $nextIndent);

                        continue;
                    }
                }

                $result[$key] = $value;

                continue;
            }

            if (str_contains($content, Constants::ARRAY_MARKER)) {
                $isList = true;
                $result[] = self::parseArrayLine($content);
                ++$index;

                continue;
            }

            $result[] = Primitives::decode($content);
            ++$index;
        }

        return $result;
    }

    private static function parseObjectLine(string $line): array
    {
        $parts = explode(Constants::OBJECT_MARKER, $line, 2);
        $key = trim($parts[0]);
        $value = isset($parts[1]) ? trim($parts[1]) : null;

        if ('' === $value || null === $value) {
            return [$key, null];
        }

        return [$key, Primitives::decode($value)];
    }

    private static function parseArrayLine(string $line): mixed
    {
        // Extract array length
        if (preg_match('/^(\d+)\]/', $line, $matches)) {
            $length = (int)$matches[1];
            $line = substr($line, strlen($matches[0]));
            $line = trim($line);

            // Check for field headers (tabular format)
            if (str_contains($line, Constants::ARRAY_FIELD_WRAPPER_START)) {
                preg_match('/\{([^}]+)\}/', $line, $fieldMatches);
                if (isset($fieldMatches[1])) {
                    $fields = explode(Constants::FIELD_DELIMITER, $fieldMatches[1]);

                    return [
                        'length' => $length,
                        'fields' => $fields,
                        'type' => 'tabular',
                    ];
                }
            }

            // Extract values
            if (str_contains($line, Constants::ARRAY_HEADER_DELIMITER)) {
                $parts = explode(Constants::ARRAY_HEADER_DELIMITER, $line, 2);
                $values = explode(Constants::FIELD_DELIMITER, trim($parts[1] ?? ''));

                return array_map(fn ($v) => Primitives::decode($v), $values);
            }
        }

        return [];
    }

    private static function getIndent(string $line): int
    {
        $count = 0;
        foreach (str_split($line) as $char) {
            if (' ' === $char) {
                ++$count;
            } else {
                break;
            }
        }

        return (int)($count / 2);
    }
}

