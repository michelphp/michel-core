<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Data collector for application profiling and debugging
 */

namespace Michel\Framework\Core\Debug;

final class DebugDataCollector
{
    private array $data = [];
    private bool $isEnabled;

    public function __construct(bool $isEnabled = false)
    {
        $this->isEnabled = $isEnabled;
    }

    public function add(string $key, $value): void
    {
        if (!$this->isEnabled) {
            return;
        }
        $this->data[$key] = $value;
    }

    public function push(string $key, $value): void
    {
        if (!$this->isEnabled) {
            return;
        }
        $this->data[$key][] = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
