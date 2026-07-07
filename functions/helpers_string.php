<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * String helper functions
 */

if (!function_exists('__e')) {

    /**
     * Safely escapes string characters for HTML output.
     * Keeps sneaky hackers away by sanitizing text for the browser!
     *
     * @example __e("<script>alert('hack');</script>") // => "&lt;script&gt;alert('hack');&lt;/script&gt;"
     *
     * @param string $str The dangerous raw string.
     * @param int $flags Escaping strategy configuration flags.
     * @param string $encoding The character set mapping.
     * @return string The safe, clean string.
     */
    function __e(string $str, int $flags = ENT_QUOTES, string $encoding = 'UTF-8'): string
    {
        return htmlentities($str, $flags, $encoding);
    }
}



if (!function_exists('human_readable_bytes')) {

    /**
     * Translates a raw byte size into a beautiful, human-readable format.
     * Makes hard-to-read numbers like 1048576 look friendly like "1 MB"!
     *
     * @example human_readable_bytes(1048576) // => "1 MB"
     * @example human_readable_bytes(1234)    // => "1.21 KB"
     *
     * @param int $size Number of bytes.
     * @param int $precision Number of decimal points.
     * @return string Human-friendly file size representation.
     */
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
     * Translates a raw byte size (legacy/compatibility function).
     *
     * @deprecated Use human_readable_bytes instead.
     *
     * @param mixed $size Number of bytes.
     * @return string Human-friendly file size.
     */
    function _m_convert($size): string
    {
        return human_readable_bytes((int) $size);
    }
}
