<?php

declare(strict_types=1);

namespace Michel\Framework\Core\Middlewares;

use BadMethodCallException;
use LogicException;
use Michel\Framework\Core\Debug\DebugDataCollector;
use Michel\Route;
use Michel\RouterMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Michel\Framework\Core\Controller\Controller;
use Michel\Framework\Core\Handler\RequestHandler;
use function array_merge;
use function array_values;
use function get_class;
use function is_callable;
use function method_exists;
use function sprintf;

/**
 * @author Michel.F 
 */
final class ControllerMiddleware implements MiddlewareInterface
{

    private ContainerInterface $container;

    /**
     * RouterMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $controller = $this->resolveController($request);
        if ($controller instanceof Controller) {
            $controller->setContainer($this->container);
            $requestHandler = new RequestHandler(
                $this->container,
                $controller->getMiddlewares(),
                static function (ServerRequestInterface $request) use ($controller) {
                    return self::callController($request, $controller);
                }
            );
            return $requestHandler->handle($request);
        }

        return self::callController($request, $controller);
    }

    private function resolveController(ServerRequestInterface $request): callable
    {
        $route = $request->getAttribute(RouterMiddleware::ATTRIBUTE_KEY);
        if (!$route instanceof Route) {
            throw new LogicException('Route not found in request., Maybe you forgot to use Michel\RouterMiddleware?');
        }

        $handler = $route->getHandler();
        $controller = $handler[0];
        $action = $handler[1] ?? null;

        if ($controller instanceof \Closure) {
            throw new LogicException('Closures are not supported as controllers. Route name: '.$route->getName());
        }

        if (is_string($controller)) {
            $controller = $this->container->get($controller);
        }

        $debugCollector = $request->getAttribute('debug_collector');
        if ($debugCollector instanceof DebugDataCollector) {
            $debugCollector->add('route_name', $route->getName());
            $debugCollector->add('controller', sprintf('%s::%s', get_class($controller), $action ?? '__invoke'));
        }

        if (is_callable($controller) && $action === null) {
            return $controller;
        }

        if (method_exists($controller, $action ?? '') === false) {
            throw new BadMethodCallException(
                $action === null
                    ? sprintf('Please use a Method on class %s.', get_class($controller))
                    : sprintf('Method "%s" on class %s does not exist.', $action, get_class($controller))
            );
        }
        return [$controller, $action];
    }

    private static function getArguments(ServerRequestInterface $request): array
    {
        $route = $request->getAttribute(RouterMiddleware::ATTRIBUTE_KEY);
        if (!$route instanceof Route) {
            throw new LogicException('Route not found in request., Maybe you forgot to use Michel\RouterMiddleware?');
        }
        return array_values($route->getAttributes());
    }

    private static function callController(ServerRequestInterface $request, $controller): ResponseInterface
    {
        $arguments = array_merge([$request], self::getArguments($request));
        /**
         * @var ResponseInterface $response
         */
        $response = $controller(...$arguments);
        if (!$response instanceof ResponseInterface) {
            throw new LogicException(
                'The controller must return an instance of Psr\Http\Message\ResponseInterface.'
            );
        }
        return $response;
    }
}
