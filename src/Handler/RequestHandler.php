<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * PSR-15 request handler for dispatching requests through middleware
 */

namespace Michel\Framework\Core\Handler;

use LogicException;
use Michel\Framework\Core\Debug\DebugDataCollector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use function is_string;

final class RequestHandler implements RequestHandlerInterface
{
    private ContainerInterface $container;
    /**
     * @var array<MiddlewareInterface|string>
     */
    private array $middlewareCollection;
    private int $index = 0;
    private ?\Closure $then;

    public function __construct(ContainerInterface $container, array $middlewareCollection, ?\Closure $then = null)
    {
        $this->container = $container;
        $this->middlewareCollection = $middlewareCollection;
        $this->then = $then;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middlewareCollection[$this->index])) {
            $then = $this->then;
            if ($then instanceof \Closure) {
                return $then($request);
            }
            throw new LogicException('The Middleware must return an instance of Psr\Http\Message\ResponseInterface.');
        }

        $middleware = $this->middlewareCollection[$this->index++];

        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        if (!$middleware instanceof MiddlewareInterface) {
            throw new LogicException(
                sprintf(
                    'The Middleware must be an instance of Psr\Http\Server\MiddlewareInterface, "%s" given.',
                    is_object($middleware) ? get_class($middleware) : gettype($middleware)
                )
            );
        }
        $debugCollector = $request->getAttribute('debug_collector');
        if ($debugCollector instanceof DebugDataCollector) {
            $debugCollector->push('middlewares_executed', get_class($middleware));
        }

        return $middleware->process($request, $this);
    }
}
