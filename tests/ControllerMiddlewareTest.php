<?php

namespace Test\Michel\Framework\Core;

use Michel\Framework\Core\Middlewares\ControllerMiddleware;
use Michel\Route;
use Michel\RouterMiddleware;
use Michel\UniTester\TestCase;
use Test\Michel\Framework\Core\Mock\ContainerMock;
use Test\Michel\Framework\Core\Mock\RequestHandlerMock;
use Test\Michel\Framework\Core\Mock\ServerRequestMock;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\Michel\Framework\Core\Controller\SampleControllerTest;

class ControllerMiddlewareTest extends TestCase
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
        $this->testProcessWithCallableController();
        $this->testProcessWithControllerMethod();
        $this->testProcessWithControllerMethodNotExist();
    }
    public function testProcessWithCallableController()
    {
        $container = new ContainerMock();
        $controllerMiddleware = new ControllerMiddleware($container);

        $request = new ServerRequestMock([
            RouterMiddleware::ATTRIBUTE_KEY => new Route('example', '/example', [new SampleControllerTest([])])
        ]);

        $handler = new RequestHandlerMock();
        $response = $controllerMiddleware->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithControllerMethod()
    {
        $response = $this->testProcessWithController('fakeMethod');
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithControllerMethodNotExist()
    {
        $this->expectException(\BadMethodCallException::class, function () {
            $this->testProcessWithController('fakeMethodNotExist');
        });
    }

    private function testProcessWithController(string $controllerMethodName): ResponseInterface
    {
        $controllerClassName = SampleControllerTest::class;
        $container = new ContainerMock([
            $controllerClassName => new SampleControllerTest([])
        ]);
        $controllerMiddleware = new ControllerMiddleware($container);

        $request = new ServerRequestMock([
            RouterMiddleware::ATTRIBUTE_KEY => new Route('example', '/example', [$controllerClassName, $controllerMethodName])
        ]);

        return $controllerMiddleware->process($request, new RequestHandlerMock());
    }
}
