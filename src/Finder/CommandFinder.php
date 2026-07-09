<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Finder to locate command classes in directories or from explicit class names.
 */

namespace Michel\Framework\Core\Finder;

use Michel\Console\Command\CommandInterface;

final class CommandFinder extends AbstractClassFinder
{
    protected function getTargetClassOrInterface(): string
    {
        return CommandInterface::class;
    }

    protected function getCachePrefix(): string
    {
        return 'cmd';
    }

    /**
     * @return string[] Fully-qualified class names implementing CommandInterface
     */
    public function findCommandClasses(): array
    {
        return $this->findClasses();
    }
}
