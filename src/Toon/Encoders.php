<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Toon;

class Encoders
{
    public static function encode(mixed $data, EncodeOptions $options = new EncodeOptions()): string
    {
        $normalized = Normalize::normalize($data);
        $writer = new LineWriter($options->indent);

        self::encodeValue($normalized, $writer, $options);

        return $writer->getContent();
    }

    private static function encodeValue(mixed $value, LineWriter $writer, EncodeOptions $options): void
    {
        if (null === $value || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            $writer->line(Primitives::encode($value));

            return;
        }

        if (is_array($value)) {
            self::encodeArray($value, $writer, $options);

            return;
        }

        $writer->line();
    }

    private static function encodeArray(array $array, LineWriter $writer, EncodeOptions $options): void
    {
        if (empty($array)) {
            $writer->line('[]');

            return;
        }

        // Check if it's associative or indexed
        $isAssociative = !array_is_list($array);

        if ($isAssociative) {
            self::encodeObject($array, $writer, $options);
        } else {
            self::encodeIndexedArray($array, $writer, $options);
        }
    }

    private static function encodeObject(array $object, LineWriter $writer, EncodeOptions $options): void
    {
        foreach ($object as $key => $value) {
            $keyStr = is_int($key) ? (string)$key : $key;

            if (is_array($value) && !empty($value)) {
                $writer->line($keyStr.Constants::OBJECT_MARKER);
                $writer->indent();
                self::encodeArray($value, $writer, $options);
                $writer->dedent();
            } elseif (is_array($value)) {
                $writer->line($keyStr.Constants::OBJECT_MARKER.' []');
            } else {
                $encoded = Primitives::encode($value);
                $writer->line($keyStr.Constants::OBJECT_MARKER.' '.$encoded);
            }
        }
    }

    private static function encodeIndexedArray(array $array, LineWriter $writer, EncodeOptions $options): void
    {
        $count = count($array);
        $allPrimitives = true;
        $allObjects = true;

        foreach ($array as $item) {
            if (is_array($item)) {
                $allPrimitives = false;
            } else {
                $allObjects = false;
            }
        }

        // All primitives - inline format
        if ($allPrimitives) {
            $encoded = array_map(fn ($item) => Primitives::encode($item), $array);
            $content = implode($options->delimiter, $encoded);
            $writer->line($count.Constants::ARRAY_MARKER_END.Constants::ARRAY_HEADER_DELIMITER.' '.$content);

            return;
        }

        // Check if uniform objects
        if ($allObjects && count($array) >= $options->minRowsToTabular) {
            $isUniform = self::isUniformArray($array);
            if ($isUniform) {
                self::encodeTabularArray($array, $writer, $options);

                return;
            }
        }

        // List format with hyphens
        $writer->line($count.Constants::ARRAY_MARKER_END.Constants::OBJECT_MARKER);
        $writer->indent();
        foreach ($array as $item) {
            if (is_array($item)) {
                self::encodeArray($item, $writer, $options);
            } else {
                $writer->line(Primitives::encode($item));
            }
        }
        $writer->dedent();
    }

    private static function isUniformArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        $firstKeys = null;
        foreach ($array as $item) {
            if (!is_array($item)) {
                return false;
            }

            $keys = array_keys($item);
            if (null === $firstKeys) {
                $firstKeys = $keys;
            } elseif ($keys !== $firstKeys) {
                return false;
            }
        }

        return true;
    }

    private static function encodeTabularArray(array $array, LineWriter $writer, EncodeOptions $options): void
    {
        $count = count($array);
        $keys = array_keys(reset($array) ?? []);
        $keysStr = implode($options->delimiter, $keys);

        $writer->line($count.Constants::ARRAY_MARKER_END.Constants::ARRAY_FIELD_WRAPPER_START.$keysStr.Constants::ARRAY_FIELD_WRAPPER_END.Constants::OBJECT_MARKER);
        $writer->indent();

        foreach ($array as $row) {
            $values = [];
            foreach ($keys as $key) {
                $values[] = Primitives::encode($row[$key] ?? null);
            }
            $writer->line(implode($options->delimiter, $values));
        }

        $writer->dedent();
    }
}

