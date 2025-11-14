<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Toon;

class Primitives
{
    public static function encode(mixed $value): string
    {
        if (null === $value) {
            return Constants::NULL_VALUE;
        }

        if (true === $value) {
            return Constants::TRUE_VALUE;
        }

        if (false === $value) {
            return Constants::FALSE_VALUE;
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (is_float($value)) {
            if (!is_finite($value)) {
                return Constants::NULL_VALUE;
            }

            $formatted = sprintf('%.17G', $value);

            return $formatted;
        }

        if (is_string($value)) {
            return self::encodeString($value);
        }

        return '';
    }

    private static function encodeString(string $value): string
    {
        // No quotes needed for simple alphanumeric strings
        if (self::needsQuoting($value)) {
            return Constants::QUOTE_CHAR.self::escape($value).Constants::QUOTE_CHAR;
        }

        return $value;
    }

    private static function needsQuoting(string $value): bool
    {
        if ('' === $value) {
            return true;
        }

        // Reserved words
        if (in_array($value, [Constants::NULL_VALUE, Constants::TRUE_VALUE, Constants::FALSE_VALUE], true)) {
            return true;
        }

        // Numeric strings
        if (is_numeric($value)) {
            return true;
        }

        // Contains special characters
        if (preg_match('/[\\s:,\[\]{}\-"\\\n\r\t]/', $value)) {
            return true;
        }

        return false;
    }

    private static function escape(string $value): string
    {
        $result = '';
        for ($i = 0; $i < strlen($value); ++$i) {
            $char = $value[$i];
            if (in_array($char, Constants::ESCAPABLE_CHARS, true)) {
                $result .= Constants::ESCAPE_CHAR.Constants::ESCAPE_MAP[$char];
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    public static function decode(string $value): mixed
    {
        $value = trim($value);

        if ('' === $value) {
            return '';
        }

        // Handle quoted strings
        if (Constants::QUOTE_CHAR === $value[0]) {
            return self::unquote($value);
        }

        // Handle null
        if (Constants::NULL_VALUE === $value) {
            return null;
        }

        // Handle booleans
        if (Constants::TRUE_VALUE === $value) {
            return true;
        }

        if (Constants::FALSE_VALUE === $value) {
            return false;
        }

        // Handle numbers
        if (is_numeric($value)) {
            if (false !== strpos($value, '.')) {
                return (float)$value;
            }

            return (int)$value;
        }

        return $value;
    }

    private static function unquote(string $value): string
    {
        // Remove surrounding quotes
        $value = substr($value, 1, -1);
        if (null === $value) {
            return '';
        }

        // Unescape characters
        $result = '';
        $i = 0;
        while ($i < strlen($value)) {
            if (Constants::ESCAPE_CHAR === $value[$i] && $i + 1 < strlen($value)) {
                $nextChar = $value[$i + 1];
                if (isset(Constants::UNESCAPE_MAP[$nextChar])) {
                    $result .= Constants::UNESCAPE_MAP[$nextChar];
                    $i += 2;
                } else {
                    $result .= $value[$i];
                    ++$i;
                }
            } else {
                $result .= $value[$i];
                ++$i;
            }
        }

        return $result;
    }
}

