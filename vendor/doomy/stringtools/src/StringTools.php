<?php

namespace Doomy\Helper;

class StringTools
{
    public static function normalizeStringForUri($string) {
        $urlEncoded = urlencode(static::removeCzDiacritics($string));
        return str_replace('+', '-', $urlEncoded);
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