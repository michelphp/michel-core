<?php


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Test\Michel\Framework\Core\Mock\ContainerMock;
use Test\Michel\Framework\Core\Mock\ServerRequestMock;
use Test\Michel\Framework\Core\Response\ResponseTest;

return [
    'server_request' => static function (): ServerRequestInterface {
        return new ServerRequestMock();
    },
    'response_factory' => static function (): ResponseFactoryInterface {
        return new class implements ResponseFactoryInterface {
            public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
            {
                return new ResponseTest();
            }
        };
    },
    'server_request_factory' => static function (): ServerRequestFactoryInterface {

        return new class implements ServerRequestFactoryInterface {
            public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
            {
                // TODO: Implement createServerRequest() method.
            }
        };

    },
    'container' => static function (array $definitions, array $options): ContainerInterface {
        return new ContainerMock($definitions);
    },
    'custom_environments' => ['test'],
];
