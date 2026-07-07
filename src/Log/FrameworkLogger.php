<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Internal framework logger — autonomous, zero PSR-3 dependency.
 * Writes JSON lines to a single rotating file (size-based).
 */

namespace Michel\Framework\Core\Log;

use DateTimeImmutable;

final class FrameworkLogger
{
    /**
     * Default maximum file size before rotation (10 MB).
     */
    private const DEFAULT_MAX_SIZE = 10 * 1024 * 1024;

    /**
     * Number of rotated files to keep (app.log.1, app.log.2, ...).
     */
    private const MAX_FILES = 3;

    private const LOG_FILE = 'app.log';

    private string $logDir;
    private int $maxSize;

    public function __construct(string $logDir, int $maxSize = self::DEFAULT_MAX_SIZE)
    {
        $this->logDir = rtrim($logDir, '/\\');
        $this->maxSize = $maxSize;

        if (!is_dir($this->logDir) && !mkdir($this->logDir, 0755, true) && !is_dir($this->logDir)) {
            throw new \RuntimeException(sprintf('Log directory "%s" could not be created.', $this->logDir));
        }
    }

    /**
     * Write a log entry.
     *
     * @param string               $channel  e.g. 'request', 'profiler', 'kernel'
     * @param string               $level    e.g. 'info', 'error', 'debug'
     * @param string               $message
     * @param array<string, mixed> $context  Any extra key/value pairs to include in the JSON line
     */
    public function log(string $channel, string $level, string $message, array $context = []): void
    {
        $entry = array_merge(
            [
                '@timestamp' => (new DateTimeImmutable())->format('c'),
                'channel'    => $channel,
                'level'      => $level,
                'message'    => $message,
            ],
            $context
        );

        $this->write(json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }

    /**
     * Shorthand for channel-less raw data (e.g. profiler payloads).
     *
     * @param array<string, mixed> $data
     */
    public function logRaw(string $channel, array $data): void
    {
        $entry = array_merge(['channel' => $channel], $data);
        $this->write(json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }

    private function write(string $line): void
    {
        $path = $this->logDir . DIRECTORY_SEPARATOR . self::LOG_FILE;

        $this->rotateIfNeeded($path);

        error_log($line, 3, $path);
    }

    private function rotateIfNeeded(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (filesize($path) < $this->maxSize) {
            return;
        }

        // Shift old rotated files: app.log.2 → delete, app.log.1 → app.log.2, app.log → app.log.1
        for ($i = self::MAX_FILES; $i >= 1; $i--) {
            $old = $path . '.' . $i;
            $new = $path . '.' . ($i + 1);
            if (file_exists($old)) {
                if ($i === self::MAX_FILES) {
                    unlink($old);
                } else {
                    rename($old, $new);
                }
            }
        }

        rename($path, $path . '.1');
    }
}
