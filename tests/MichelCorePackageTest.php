<?php

namespace Test\Michel\Framework\Core;

use Michel\Console\CommandRunner;
use Michel\Framework\Core\ErrorHandler\ExceptionHandler;
use Michel\Framework\Core\Package\MichelCorePackage;
use Michel\RouterInterface;
use Michel\RouterMiddleware;
use Michel\UniTester\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class MichelCorePackageTest extends TestCase
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
        $this->testGetDefinitions();
        $this->testGetParameters();
        $this->testGetRoutes();
        $this->testGetListeners();
        $this->testGetCommands();
    }

    public function testGetDefinitions()
    {
        $package = new MichelCorePackage();

        $definitions = $package->getDefinitions();
        $this->assertNotEmpty($definitions);

        $this->assertArrayHasKey(EventDispatcherInterface::class, $definitions);
        $this->assertTrue(is_callable($definitions[EventDispatcherInterface::class]));

        $this->assertArrayHasKey(RouterInterface::class, $definitions);
        $this->assertTrue(is_callable($definitions[RouterInterface::class]));

        $this->assertArrayHasKey('render', $definitions);
        $this->assertTrue(is_callable($definitions['render']));

        $this->assertArrayHasKey(CommandRunner::class, $definitions);
        $this->assertTrue(is_callable($definitions[CommandRunner::class]));

        $this->assertArrayHasKey(RouterMiddleware::class, $definitions);
        $this->assertTrue(is_callable($definitions[RouterMiddleware::class]));

        $this->assertArrayHasKey(ExceptionHandler::class, $definitions);
        $this->assertTrue(is_callable($definitions[ExceptionHandler::class]));
    }

    public function testGetParameters()
    {
        $package = new MichelCorePackage();

        $parameters = $package->getParameters();

        $this->assertNotEmpty($parameters);
        $this->assertArrayHasKey('app.url', $parameters);
        $this->assertArrayHasKey('app.locale', $parameters);
        $this->assertArrayHasKey('app.template_dir', $parameters);
    }

    public function testGetRoutes()
    {
        $package = new MichelCorePackage();
        $routes = $package->getRoutes();
        $this->assertEmpty($routes);
    }

    public function testGetListeners()
    {
        $package = new MichelCorePackage();
        $listeners = $package->getListeners();
        $this->assertEmpty($listeners);
    }

    public function testGetCommands()
    {
        $package = new MichelCorePackage();
        $commands = $package->getCommandSources();
        $this->assertNotEmpty($commands);
        foreach ($commands as $command) {
            $this->assertTrue(is_string($command));
        }
    }
}
