<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Array helper functions
 */

if (!function_exists('array_flatten')) {

    /**
     * Squashes a nested, multi-dimensional array into a flat one-dimensional array.
     * Perfect for crushing complex arrays into a single list!
     *
     * @example array_flatten([1, [2, 3], [4, [5]]]) // => [1, 2, 3, 4, 5]
     *
     * @param array $array The array to be flattened.
     * @return array The squashed, flat array.
     */
    function array_flatten(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }
}

if (!function_exists('array_dot')) {

    /**
     * Flattens a multi-dimensional associative array into a single-level array using dot notation.
     * Ideal for mapping nested configuration trees!
     *
     * @example array_dot(['app' => ['db' => ['user' => 'michel']]]) // => ['app.db.user' => 'michel']
     *
     * @param array $array The array to dot-flatten.
     * @param string $rootKey The base key prefix (used internally for recursion).
     * @return array The dot-notated flat array.
     */
    function array_dot(array $array, string $rootKey = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $key = strval($key);
            $key = $rootKey !== '' ? ($rootKey . '.' . $key) : $key;
            if (is_array($value)) {
                $result = $result + array_dot($value, $key);
                continue;
            }
            $result[$key] = $value;
        }

        return $result;
    }
}

if (!function_exists('array_group_by')) {

    /**
     * Groups array items by a specific key or property.
     * Super handy to group users by role or products by category!
     *
     * @example array_group_by([['name' => 'Alice', 'role' => 'admin'], ['name' => 'Bob', 'role' => 'user']], 'role')
     *          // => ['admin' => [['name' => 'Alice', 'role' => 'admin']], 'user' => [['name' => 'Bob', 'role' => 'user']]]
     *
     * @param array $array The collection of arrays or objects.
     * @param string $key The key or property name to group by.
     * @return array The items grouped by key.
     */
    function array_group_by(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $value) {
            $group = $value;
            if (is_array($value)) {
                $group = $value[$key];
            } elseif (is_object($value)) {
                $group = $value->$key;
            }
            $result[$group][] = $value;
        }
        return $result;
    }
}
