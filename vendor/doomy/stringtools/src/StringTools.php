<?php

namespace Doomy\Helper;

class StringTools
{
    public static function normalizeStringForUri(string $string): string
    {
        $urlEncoded = urlencode(static::removeCzDiacritics($string));
        $string = str_replace('+', '-', $urlEncoded);
        return str_replace('.', '', $string);
    }

    public static function underscoresToCamelCase($string, $capitalizeFirstCharacter = false)
    {


        $str = str_replace('_', '', ucwords(strtolower($string), '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    public static function isAllCaps(string $string): bool
    {
        return strtoupper($string) == $string;
    }

    private static function removeCzDiacritics($string) {
        $conversionTable = json_decode(file_get_contents(__DIR__ . "/../resources/czCharConversionMap.json"), TRUE);
        return strtr($string, $conversionTable);
    }
}
