<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Toon;

use DateTime;
use DateTimeInterface;

class Normalize
{
    public static function normalize(mixed $value): mixed
    {
        if (null === $value || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return self::normalizeArray($value);
        }

        if (is_object($value)) {
            return self::normalizeObject($value);
        }

        return null;
    }

    private static function normalizeArray(array $array): mixed
    {
        $result = [];
        foreach ($array as $key => $item) {
            $normalizedKey = is_int($key) ? $key : (string)$key;
            $result[$normalizedKey] = self::normalize($item);
        }

        return $result;
    }

    private static function normalizeObject(object $object): mixed
    {
        if ($object instanceof DateTime || $object instanceof DateTimeInterface) {
            return $object->format(DateTimeInterface::ATOM);
        }

        // Handle enums
        if (method_exists($object, 'name') && method_exists($object, 'cases')) {
            if (method_exists($object, 'value')) {
                return $object->value;
            }

            return $object->name;
        }

        // Try to convert object to array
        if (method_exists($object, 'toArray')) {
            return self::normalize($object->toArray());
        }

        if (method_exists($object, 'jsonSerialize')) {
            return self::normalize($object->jsonSerialize());
        }

        return json_decode(json_encode($object, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}

