<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Unit test for App class
 */

namespace Test\Michel\Framework\Core;

use InvalidArgumentException;
use Michel\Framework\Core\App;
use Michel\UniTester\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppTest extends TestCase
{

    protected function setUp(): void
    {

    }

    protected function tearDown(): void
    {
        App::reset();
    }

    protected function execute(): void
    {
        $this->testInitWithInValidPath();
        App::initWithPath(__DIR__ . '/config/framework.php');
        $this->testCreateServerRequest();
        $this->testGetResponseFactory();
        $this->testCreateContainer();
        $this->testGetCustomEnvironments();
    }


    public function testInitWithInValidPath()
    {
        $path = 'path/to/your/options.php';
        $this->expectException(InvalidArgumentException::class, function () use($path) {
            App::initWithPath($path);
        });
    }

    public function testCreateServerRequest()
    {
        $request = App::createServerRequest();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    public function testGetResponseFactory()
    {
        $responseFactory = App::getResponseFactory();
        $this->assertInstanceOf(ResponseFactoryInterface::class, $responseFactory);
    }

    public function testCreateContainer()
    {
        $definitions = []; // Your container definitions
        $options = []; // Your container options

        $container = App::createContainer($definitions, $options);
        $this->assertInstanceOf(ContainerInterface::class, $container);

        $container = App::getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testGetCustomEnvironments()
    {
        $environments = App::getCustomEnvironments();
        foreach ($environments as $environment) {
            $this->assertTrue(is_string($environment));
        }
    }
}
