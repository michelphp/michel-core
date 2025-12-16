<?php

namespace Michel\Framework\Core\ErrorHandler;

use Michel\Framework\Core\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Michel\Framework\Core\ErrorHandler\ErrorRenderer\JsonErrorRenderer;
use Michel\Framework\Core\Http\Exception\HttpException;
use Michel\Framework\Core\Http\Exception\HttpExceptionInterface;
use Michel\Resolver\Option;
use Michel\Resolver\OptionsResolver;
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
        $resolver = (new OptionsResolver(
            [
                Option::bool("debug", false),
                Option::mixed("json_response", new JsonErrorRenderer($this->responseFactory, $options['debug']))
                    ->validator(static function ($value) {
                        return is_callable($value);
                    }),
                Option::mixed("html_response", new HtmlErrorRenderer($this->responseFactory, $options['debug']))
                    ->validator(static function ($value) {
                        return is_callable($value);
                    }),
            ]
        ));
        $this->options = $resolver->resolve($options);
    }

    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        if (!$exception instanceof HttpExceptionInterface) {
            $exception = new HttpException(500, $exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($request->getHeaderLine('accept') === 'application/json') {
            return $this->renderJsonResponse($exception);
        }
        return $this->renderHtmlResponse($exception);
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
