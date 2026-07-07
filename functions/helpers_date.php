<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Date helper functions
 */

if (!function_exists('days_between')) {

    /**
     * Calculates the number of days between two dates.
     * Perfect for counting down how many sleeps are left until Christmas!
     *
     * @example days_between(new DateTime('2026-12-20'), new DateTime('2026-12-25')) // => 5
     *
     * @param DateTime $datetime1 The first date.
     * @param DateTime $datetime2 The second date.
     * @return int The number of days separating them.
     */
    function days_between(DateTime $datetime1, DateTime $datetime2): int
    {
        $interval = $datetime1->diff($datetime2);
        return $interval->days;
    }
}

if (!function_exists('is_leap_year')) {

    /**
     * Checks if a year is a leap year (meaning February gets a bonus 29th day!).
     * Great for knowing if you get an extra 24 hours of coding time this year!
     *
     * @example is_leap_year(new DateTime('2024-01-01')) // => true
     * @example is_leap_year(new DateTime('2026-01-01')) // => false
     *
     * @param DateTime $date The date containing the year to test.
     * @return bool True if it's a leap year, false otherwise.
     */
    function is_leap_year(DateTime $date): bool
    {
        $year = $date->format('Y');
        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }
}

if (!function_exists('is_weekend')) {

    /**
     * Determines if a date falls on a weekend.
     * The ultimate tool to know if it's time to close the terminal and grab a beer!
     *
     * @example is_weekend(new DateTime('2026-07-11')) // => true (Saturday)
     * @example is_weekend(new DateTime('2026-07-07')) // => false (Tuesday)
     *
     * @param DateTime $date The date to test.
     * @return bool True if weekend, false otherwise.
     */
    function is_weekend(DateTime $date): bool
    {
        return in_array($date->format('N'), [6, 7]);
    }
}

if (!function_exists('is_today')) {

    /**
     * Checks if the given date is today.
     * Helps you verify if you're living in the present or stuck in the past!
     *
     * @example is_today(new DateTime('now')) // => true
     *
     * @param DateTime $date The date to check.
     * @return bool True if today, false otherwise.
     */
    function is_today(DateTime $date): bool
    {
        return $date->format('Y-m-d') === (new DateTime())->format('Y-m-d');
    }
}

if (!function_exists('is_past')) {

    /**
     * Checks if a date has already passed.
     * Perfect for checking if your homework or task was due yesterday (oops!).
     *
     * @example is_past(new DateTime('2000-01-01')) // => true
     * @example is_past(new DateTime('2050-01-01')) // => false
     *
     * @param DateTime $date The date to check.
     * @return bool True if in the past, false otherwise.
     */
    function is_past(DateTime $date): bool
    {
        return (new DateTime()) > $date;
    }
}
