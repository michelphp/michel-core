<?php

namespace Test\Michel\Framework\Core;

use Michel\Framework\Core\Finder\ControllerFinder;
use Michel\UniTester\TestCase;
use Test\Michel\Framework\Core\Controller\SampleControllerTest;
use Test\Michel\Framework\Core\Controller\UserControllerTest;

class ControllerFinderTest extends TestCase
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
        if (PHP_VERSION_ID >= 80000) {
            $controllers = (new ControllerFinder([__DIR__ . '/Controller']))->findControllerClasses();
            $this->assertCount(2, $controllers);
        }
        $this->assertTrue(true);
    }

    public function testFoundCache()
    {
        if (PHP_VERSION_ID >= 80000) {
            $cacheDir = __DIR__ . '/cache';
            $targetDir = __DIR__ . '/Controller';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }
            $fileCache = "$cacheDir/" . md5($targetDir) . '.php';
            if (file_exists($fileCache)) {
                unlink($fileCache);
            }

            $this->assertFalse(file_exists($fileCache));
            $controllers = (new ControllerFinder([$targetDir], $cacheDir))->findControllerClasses();
            $this->assertCount(2, $controllers);
            $this->assertTrue(file_exists($fileCache));

            $classes = require $fileCache;
            $needles = [
                SampleControllerTest::class,
                UserControllerTest::class,
            ];
            rsort($classes);
            rsort($needles);
            $this->assertEquals($needles, $classes);

            $controllers = (new ControllerFinder([$targetDir], $cacheDir))->findControllerClasses();
            $this->assertCount(2, $controllers);
            unlink($fileCache);
            rmdir($cacheDir);
        }
        $this->assertTrue(true);
    }
}
