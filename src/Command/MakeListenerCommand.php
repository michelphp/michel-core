<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Command to generate a new event listener class
 */

namespace Michel\Framework\Core\Command;

use Michel\Console\Argument\CommandArgument;
use Michel\Console\Command\CommandInterface;
use Michel\Console\InputInterface;
use Michel\Console\Output\ConsoleOutput;
use Michel\Console\OutputInterface;

final class MakeListenerCommand extends AbstractMakeCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'make:listener';
    }

    public function getDescription(): string
    {
        return 'Generate a new event listener class';
    }

    public function getOptions(): array
    {
        return [];
    }

    public function getArguments(): array
    {
        return [
            new CommandArgument("name", true, null, "The name of the listener, ex : App\\Listeners\\OrderCreatedListener")
        ];
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new ConsoleOutput($output);
        $listenerName = $input->getArgumentValue('name');

        $filename = $this->createClass($listenerName);
        $io->success("Class $listenerName created successfully at $filename.");
    }

    protected function template(string $classNamespace, string $curtClassName): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace $classNamespace;

final class $curtClassName
{
    public function __invoke(object \$event): void
    {
        // TODO: Implement listener logic here
    }
}
PHP;
    }
}
