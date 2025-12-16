<?php

namespace Michel\Framework\Core\ErrorHandler;

use ErrorException;
use Throwable;
use function error_reporting;
use function in_array;
use function set_error_handler;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

final class ErrorHandler
{
    private array $deprecations = [];

    public static function register(): self
    {
        error_reporting(E_ALL);
        ini_set("display_errors", '0');
        ini_set("display_startup_errors", '0');
        ini_set('html_errors', (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') ? '0' : '1');

        $handler = new self();
        set_error_handler($handler);
        set_exception_handler([$handler, 'handleException']);
        return $handler;
    }

    public function __invoke(int $level, string $message, ?string $file = null, ?int $line = null): void
    {
        if (!error_reporting()) {
            return;
        }
        if (in_array($level, [E_USER_DEPRECATED, E_DEPRECATED])) {
            $this->deprecations[] = ['level' => $level, 'file' => $file, ' line' => $line, 'message' => $message];
            return;
        }

        throw new ErrorException($message, 0, $level, $file, $line);
    }

    public function handleException(Throwable $exception): void
    {
        $message = sprintf(
            "Uncaught Exception: %s\nIn file: %s:%d\nStack trace:\n%s\n",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        if (ini_get('error_log')) {
            error_log($message);
        }
        echo $message;
        exit(1);
    }

    public function clean(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * @return array
     */
    public function getDeprecations(): array
    {
        return $this->deprecations;
    }
}
