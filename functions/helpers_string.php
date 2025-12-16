<?php

if (!function_exists('__e')) {

    /**
     * Encodes a string for HTML entities.
     *
     * @param string $str The string to encode.
     * @param int $flags Flags for htmlentities.
     * @param string $encoding The character encoding.
     * @return string The encoded string.
     */
    function __e(string $str, int $flags = ENT_QUOTES, string $encoding = 'UTF-8'): string
    {
        return htmlentities($str, $flags, $encoding);
    }
}

if (!function_exists('str_starts_with')) {

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function str_starts_with(string $haystack, string $needle): bool
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

if (!function_exists('str_ends_with')) {

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function str_ends_with(string $haystack, string $needle): bool
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('str_contains')) {

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    function str_contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('human_readable_bytes')) {
    function human_readable_bytes(int $size, int $precision = 2): string
    {
        if ($size <= 0) {
            return '0 B';
        }
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $class = (int) floor($base);
        return round(pow(1024, $base - $class), $precision) . ' ' . $suffixes[$class];
    }
}

if (!function_exists('_m_convert')) {
    /**
     * @deprecated Use human_readable_bytes instead
     */
    function _m_convert($size): string
    {
        return human_readable_bytes((int) $size);
    }
}
