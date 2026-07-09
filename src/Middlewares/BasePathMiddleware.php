<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Middleware to strip a base path prefix from the request URI.
 * Useful when the application is hosted in a subdirectory (e.g. /myapp/).
 */

namespace Michel\Framework\Core\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Strips the configured base path prefix from the incoming request URI,
 * so the rest of the middleware stack and router see a clean path.
 *
 * Example: base path "/myapp", request "/myapp/about" → "/about"
 */
final class BasePathMiddleware implements MiddlewareInterface
{
    private string $basePath;

    /**
     * @param string $basePath The URL prefix to strip (e.g. "http://localhost/myapp" or "/myapp").
     *                         Only the path component is used; scheme/host are ignored.
     */
    public function __construct(string $basePath)
    {
        $path = parse_url($basePath, PHP_URL_PATH) ?? $basePath;
        $path = rtrim($path, '/');

        if ($path !== '' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        $this->basePath = $path;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->basePath === '' || $this->basePath === '/') {
            return $handler->handle($request);
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        // On ne retire le préfixe que si c'est une correspondance exacte (ex: /myapp)
        // ou si c'est suivi d'un slash (ex: /myapp/ ou /myapp/route)
        if ($path === $this->basePath) {
            $request = $request->withUri($uri->withPath('/'));
        } elseif (str_starts_with($path, $this->basePath . '/')) {
            $newPath = substr($path, strlen($this->basePath));
            $request = $request->withUri($uri->withPath($newPath));
        }

        return $handler->handle($request);
    }
}
