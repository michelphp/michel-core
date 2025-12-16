<?php

namespace Test\Michel\Framework\Core;

use Michel\Framework\Core\Handler\RequestHandler;
use Michel\UniTester\TestCase;
use Test\Michel\Framework\Core\Mock\ContainerMock;
use Test\Michel\Framework\Core\Mock\ResponseMock;
use Test\Michel\Framework\Core\Mock\ServerRequestMock;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Test\Michel\Framework\Core\Middleware\ResponseMiddlewareTest;

class RequestHandlerTest extends TestCase
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
        $this->testResponseOk();
        $this->testInvalidMiddleware();
        $this->testThenArgument();
    }

    public function testResponseOk()
    {
        /**
         * @var ContainerInterface $container
         * @var ServerRequestInterface $request
         */
        $container = new ContainerMock();
        $request = new ServerRequestMock();
        $handler = new RequestHandler($container, [new ResponseMiddlewareTest()]);
        $this->assertEquals(200, $handler->handle($request)->getStatusCode());
    }

    public function testInvalidMiddleware()
    {
        /**
         * @var ContainerInterface $container
         * @var ServerRequestInterface $request
         */
        $container = new ContainerMock();
        $request = new ServerRequestMock();
        $handler = new RequestHandler($container, [new \stdClass()]);
        $this->expectException(\LogicException::class, function () use($handler, $request) {
            $handler->handle($request);
        });
    }

    public function testThenArgument()
    {
        /**
         * @var ContainerInterface $container
         * @var ServerRequestInterface $request
         */
        $container = new ContainerMock();
        $request = new ServerRequestMock();
        $handler = new RequestHandler($container, [],   function (ServerRequestInterface $request) {
            return new ResponseMock();
        });
        $response = $handler->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

}
