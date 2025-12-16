<?php

namespace Test\Michel\Framework\Core;

use Michel\UniTester\TestCase;
use Test\Michel\Framework\Core\Mock\ContainerMock;
use Test\Michel\Framework\Core\Mock\MiddlewareMock;
use Psr\Http\Server\MiddlewareInterface;
use Test\Michel\Framework\Core\Controller\SampleControllerTest;

class ControllerTest extends TestCase
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
        $this->testMiddleware();
        $this->testInvalidMiddleware();
        $this->testGet();
    }
    public function testMiddleware()
    {
        $middleware = new MiddlewareMock();
        $controller = new SampleControllerTest([$middleware]);
        $middlewares = $controller->getMiddlewares();
        $this->assertInstanceOf(MiddlewareInterface::class, $middlewares[0]);
    }

    public function testInvalidMiddleware()
    {
        $this->expectException(\InvalidArgumentException::class, function () {
            $invalidMiddleware = 'InvalidMiddlewareClass';
            new SampleControllerTest([$invalidMiddleware]);
        });

    }

    public function testGet()
    {
        $controller = new SampleControllerTest([]);
        $container = new ContainerMock([
            'service_id' => 'service_instance'
        ]);
        $controller->setContainer($container);
        $this->assertEquals('service_instance', $controller->testGet('service_id'));
    }
}
