<?php

namespace Joelwmale\SslCertificate;

/**
 * Small wrapper around strpos for string contains.
 *
 * @param  string|array  $needles
 */
function str_contains(string $haystack, $needle): bool
{
    return strpos($haystack, $needle) !== false;
}

/**
 * Determine if the given string starts with a given substring.
 *
 * @param  mixed  $haystack
 */
function starts_with($haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if ($needle != '' && mb_strpos($haystack, $needle) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Determine if a given string ends with a given substring.
 *
 * @param  string|array  $needles
 */
function ends_with(string $haystack, $needles): bool
{
    foreach ((array) $needles as $needle) {
        if ((string) $needle === substr($haystack, -length($needle))) {
            return true;
        }
    }

    return false;
}

/**
 * Returns the portion of string specified by the start and length parameters.
 */
function substr(string $string, int $start, ?int $length = null): string
{
    return mb_substr($string, $start, $length, 'UTF-8');
}
