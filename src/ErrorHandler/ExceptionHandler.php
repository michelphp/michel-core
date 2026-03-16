<?php

namespace Michel\Framework\Core\ErrorHandler;

use InvalidArgumentException;
use Michel\Framework\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Michel\Framework\Core\ErrorHandler\ErrorRenderer\JsonErrorRenderer;
use Michel\Framework\Core\Http\Exception\HttpException;
use Michel\Framework\Core\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionHandler
{
    private ResponseFactoryInterface $responseFactory;
    private array $options;

    public function __construct(ResponseFactoryInterface $responseFactory, array $options = [])
    {
        $this->responseFactory = $responseFactory;

        $debug = $options['debug'] ?? false;

        if (!isset($options['json_response'])) {
            $options['json_response'] = new JsonErrorRenderer($this->responseFactory, $debug);
        } elseif (!is_callable($options['json_response'])) {
            throw new InvalidArgumentException('Option "json_response" must be callable.');
        }

        if (!isset($options['html_response'])) {
            $options['html_response'] = new HtmlErrorRenderer($this->responseFactory, $debug);
        } elseif (!is_callable($options['html_response'])) {
            throw new InvalidArgumentException('Option "html_response" must be callable.');
        }

        $this->options = $options;
    }

    public function renderByMimetype(string $mimeType, Throwable $exception): ResponseInterface
    {
        if (!$exception instanceof HttpExceptionInterface) {
            $exception = new HttpException(500, $exception->getMessage(), (int)$exception->getCode(), $exception);
        }

        $mimeType = strtolower($mimeType);
        if ($mimeType === 'application/json') {
            return $this->renderJsonResponse($exception);
        }
        return $this->renderHtmlResponse($exception);
    }

    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        return $this->renderByMimetype($request->getHeaderLine('accept'), $exception);
    }

    protected function renderJsonResponse(HttpExceptionInterface $exception): ResponseInterface
    {
        $renderer = $this->options['json_response'];
        return $renderer($exception);
    }

    protected function renderHtmlResponse(HttpExceptionInterface $exception): ResponseInterface
    {
        $renderer = $this->options['html_response'];
        return $renderer($exception);
    }
}
