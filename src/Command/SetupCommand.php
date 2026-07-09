<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Interactive command to install optional Michel packages
 */

namespace Michel\Framework\Core\Command;

use Michel\Console\Command\CommandInterface;
use Michel\Console\InputInterface;
use Michel\Console\OutputInterface;
use Michel\Console\Output\ConsoleOutput;

class SetupCommand implements CommandInterface
{
    /**
     * Available Michel packages catalog.
     * Each entry: ['name' => 'vendor/package', 'description' => '...']
     *
     * This will be replaced by an API call in the future.
     */
    private const PACKAGES = [
        ['name' => 'michel/paper-orm', 'description' => 'Database ORM with attribute mapping'],
        ['name' => 'michel/michel-auth', 'description' => 'Authentication & Security'],
    ];

    public function getName(): string
    {
        return 'michel:setup';
    }

    public function getDescription(): string
    {
        return 'Interactive wizard to install optional Michel packages.';
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
        $io = new ConsoleOutput($output);

        $io->title('Michel Framework Setup 🚀');
        $io->writeln("Select the packages you want to install.\n");

        // Display available packages as a numbered table
        $io->table(
            ['#', 'Package', 'Description'],
            array_map(function (array $pkg, int $index) {
                return [
                    (string)($index + 1),
                    $pkg['name'],
                    $pkg['description'],
                ];
            }, self::PACKAGES, array_keys(self::PACKAGES))
        );

        $io->writeln('');
        $selection = $io->ask('Enter package numbers to install (e.g. 1,2) or press Enter to skip');

        if (empty($selection)) {
            $io->writeln("\n  No packages selected. You can run this command again anytime.\n");
            return;
        }

        // Parse user selection
        $indices = array_unique(array_filter(
            array_map('intval', explode(',', $selection)),
            fn(int $i) => $i >= 1 && $i <= count(self::PACKAGES)
        ));

        if (empty($indices)) {
            $io->warning('No valid selection. Aborting.');
            return;
        }

        // Collect selected packages
        $toInstall = [];
        foreach ($indices as $index) {
            $toInstall[] = self::PACKAGES[$index - 1]['name'];
        }

        $io->writeln("\n  Packages to install:");
        $io->numberedList($toInstall);

        if (!$io->confirm('Proceed with installation?')) {
            $io->writeln("\n  Installation cancelled.\n");
            return;
        }

        // Run composer require
        $command = 'composer require ' . implode(' ', $toInstall) . ' --ansi';
        $io->writeColor("\n  Running: $command\n\n", 'yellow');
        passthru($command);

        $io->success('Setup complete! Your packages are ready.');
    }
}
