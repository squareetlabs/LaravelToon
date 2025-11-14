<?php

declare(strict_types=1);

namespace Squareetlabs\LaravelToon\Toon;

class Constants
{
    public const OBJECT_MARKER = ':';
    public const ARRAY_MARKER = '[';
    public const ARRAY_MARKER_END = ']';
    public const ARRAY_HEADER_DELIMITER = ':';
    public const FIELD_DELIMITER = ',';
    public const LIST_ITEM_MARKER = '-';
    public const ESCAPE_CHAR = '\\';
    public const QUOTE_CHAR = '"';
    public const ARRAY_FIELD_WRAPPER_START = '{';
    public const ARRAY_FIELD_WRAPPER_END = '}';
    public const ARRAY_LENGTH_OPTIONAL_MARKER = '#';

    // Special values
    public const NULL_VALUE = 'null';
    public const TRUE_VALUE = 'true';
    public const FALSE_VALUE = 'false';

    // Escaped characters
    public const ESCAPABLE_CHARS = ['\\', '"', "\n", "\r", "\t"];
    public const ESCAPE_MAP = [
        '\\' => '\\',
        '"' => '"',
        "\n" => 'n',
        "\r" => 'r',
        "\t" => 't',
    ];
    public const UNESCAPE_MAP = [
        '\\' => '\\',
        '"' => '"',
        'n' => "\n",
        'r' => "\r",
        't' => "\t",
    ];
}

