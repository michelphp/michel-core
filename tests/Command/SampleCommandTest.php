<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Sample command for unit testing purposes
 */

namespace Test\Michel\Framework\Core\Command;

use Michel\Console\Command\CommandInterface;
use Michel\Console\InputInterface;
use Michel\Console\OutputInterface;

final class SampleCommandTest implements CommandInterface
{
    public function getName(): string
    {
        return 'test:sample';
    }

    public function getDescription(): string
    {
        return 'Sample command for testing';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
        return [];
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        // no-op
    }
}
