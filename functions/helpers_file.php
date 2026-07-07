<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * File helper functions
 */

if (!function_exists('filepath_join')) {

    /**
     * Glues multiple path segments together into one clean, unified file path.
     * No more worrying about trailing slashes or backslashes, this function tidies it all up!
     *
     * @example filepath_join('var', 'cache', 'dev') // => 'var/cache/dev' (or 'var\cache\dev' on Windows)
     * @example filepath_join('/usr/bin/', '/php')  // => '/usr/bin/php'
     *
     * @param string ...$paths The path segments to join.
     * @return string The beautifully joined and sanitized path.
     */
    function filepath_join(...$paths): string
    {
        $cleanedPaths = [];
        foreach ($paths as $path) {
            $path = trim($path);
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            if (empty($path)) {
                continue;
            }

            $path = rtrim($path, DIRECTORY_SEPARATOR);
            $cleanedPaths[] = $path;
        }

        return implode(DIRECTORY_SEPARATOR, $cleanedPaths);
    }
}



