<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Command to generate a new event class
 */

namespace Michel\Framework\Core\Command;

use Michel\Console\Argument\CommandArgument;
use Michel\Console\Command\CommandInterface;
use Michel\Console\InputInterface;
use Michel\Console\Output\ConsoleOutput;
use Michel\Console\OutputInterface;

final class MakeEventCommand extends AbstractMakeCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'make:event';
    }

    public function getDescription(): string
    {
        return 'Generate a new event class';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
        return [
            new CommandArgument("name", true, null, "The name of the event, ex : App\\Event\\OrderCreatedEvent")
        ];
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new ConsoleOutput($output);
        $eventName = $input->getArgumentValue('name');

        $filename = $this->createClass($eventName);
        $io->success("Class $eventName created successfully at $filename.");
    }

    protected function template(string $classNamespace, string $curtClassName): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace $classNamespace;

use Michel\EventDispatcher\Event;

final class $curtClassName extends Event
{
    // TODO: Define event properties and constructor here
}
PHP;
    }
}
