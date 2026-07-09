<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Unit test for CommandFinder class
 */

namespace Test\Michel\Framework\Core;

use Michel\Framework\Core\Finder\CommandFinder;
use Michel\UniTester\TestCase;
use Test\Michel\Framework\Core\Command\AnotherCommandTest;
use Test\Michel\Framework\Core\Command\SampleCommandTest;

class CommandFinderTest extends TestCase
{
    protected function setUp(): void
    {
        // TODO: Implement setUp() method.
    }

    protected function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    protected function execute(): void
    {
        $this->testFound();
        $this->testFoundCache();
    }

    public function testFound()
    {
        $commands = (new CommandFinder([__DIR__ . '/Command']))->findCommandClasses();
        $this->assertCount(2, $commands);
    }

    public function testFoundCache()
    {
        $cacheDir  = __DIR__ . '/cache';
        $targetDir = __DIR__ . '/Command';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $cacheFile = $cacheDir . '/' . md5('cmd_' . $targetDir) . '.php';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        $this->assertFalse(file_exists($cacheFile));

        $commands = (new CommandFinder([$targetDir], $cacheDir))->findCommandClasses();
        $this->assertCount(2, $commands);
        $this->assertTrue(file_exists($cacheFile));

        $classes  = require $cacheFile;
        $needles  = [
            SampleCommandTest::class,
            AnotherCommandTest::class,
        ];
        rsort($classes);
        rsort($needles);
        $this->assertEquals($needles, $classes);

        // Second call should hit the cache
        $commands = (new CommandFinder([$targetDir], $cacheDir))->findCommandClasses();
        $this->assertCount(2, $commands);

        unlink($cacheFile);
        rmdir($cacheDir);
    }
}
