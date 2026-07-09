<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Command to initialize installable packages
 */

namespace Michel\Framework\Core\Command;

use Michel\Console\Command\CommandInterface;
use Michel\Console\InputInterface;
use Michel\Console\OutputInterface;
use Michel\Package\InstallablePackageInterface;
use Michel\Package\PackageInterface;
use Psr\Container\ContainerInterface;

class PackageInitCommand implements CommandInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'package:init';
    }

    public function getDescription(): string
    {
        return 'Initialize all installable packages (create directories, add config, etc.).';
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
        /** @var array<PackageInterface> $packages */
        $packages = $this->container->get('michel.packages');

        $count = 0;
        foreach ($packages as $package) {
            if ($package instanceof InstallablePackageInterface) {
                $output->writeln(sprintf('  ▸ %s', get_class($package)));
                $package->install($this->container, function(string $message) use ($output) {
                    $output->writeln($message);
                });
                $count++;
            }
        }

        if ($count === 0) {
            $output->writeln('  No installable packages found.');
        } else {
            $output->writeln(sprintf("\n  ✔ %d package(s) checked.", $count));
        }
    }
}
