<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Container mock for testing purposes
 */

namespace Test\Michel\Framework\Core\Mock;

use Psr\Container\ContainerInterface;

class ContainerMock implements ContainerInterface
{
    private array $definitions;
    public function __construct(array $definitions = [])
    {
        $this->definitions = $definitions;
    }

    public function get(string $id)
    {
        $value = $this->definitions[$id] ?? null;
        if ($value instanceof \Closure) {
            return $value($this);
        }
        return $value;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

}
