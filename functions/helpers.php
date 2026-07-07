<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Core helper functions for the framework
 */

use Composer\Autoload\ClassLoader;
use Michel\Framework\Core\App;
use Michel\RouterInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;


if (!function_exists('michel_composer_loader')) {

    /**
     * Finds and returns the magical Composer ClassLoader to load classes on the fly!
     *
     * @example $loader = michel_composer_loader();
     *
     * @return ClassLoader The composer class loader instance.
     * @throws LogicException If the MICHEL_COMPOSER_AUTOLOAD_FILE constant is missing.
     */
    function michel_composer_loader(): ClassLoader
    {
        if (!defined('MICHEL_COMPOSER_AUTOLOAD_FILE')) {
            throw new LogicException('MICHEL_COMPOSER_AUTOLOAD_FILE const must be defined!');
        }
        return require MICHEL_COMPOSER_AUTOLOAD_FILE;
    }
}

if (!function_exists('send_http_response')) {

    /**
     * Launches the HTTP response into the wild, sending headers and printing the body!
     *
     * @example send_http_response($response);
     *
     * @param ResponseInterface $response The HTTP response to emit.
     */
    function send_http_response(ResponseInterface $response)
    {
        $httpLine = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        if (!headers_sent()) {
            header($httpLine, true, $response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header("$name: $value", false);
                }
            }
        }

        echo $response->getBody();
    }
}

if (!function_exists('container')) {

    /**
     * Grabs the Dependency Injection Container (aka the box of all your services).
     *
     * @example $db = container()->get(DatabaseConnection::class);
     *
     * @return ContainerInterface The service container.
     */
    function container(): ContainerInterface
    {
        return App::getContainer();
    }
}

if (!function_exists('create_request')) {

    /**
     * Captures or creates a brand new HTTP ServerRequest object.
     *
     * @example $request = create_request();
     *
     * @return ServerRequestInterface The created server request.
     */
    function create_request(): ServerRequestInterface
    {
        return App::createServerRequest();
    }
}

if (!function_exists('request_factory')) {

    /**
     * Gets the factory responsible for crafting fresh ServerRequest instances.
     *
     * @example $factory = request_factory();
     *
     * @return ServerRequestFactoryInterface The request factory.
     */
    function request_factory(): ServerRequestFactoryInterface
    {
        return App::getServerRequestFactory();
    }
}

if (!function_exists('response_factory')) {

    /**
     * Retrieves the response factory to easily build custom HTTP responses.
     *
     * @example $factory = response_factory();
     *
     * @return ResponseFactoryInterface The response factory.
     */
    function response_factory(): ResponseFactoryInterface
    {
        return App::getResponseFactory();
    }
}

if (!function_exists('response')) {

    /**
     * Wraps your raw text/content into a shiny, standard HTTP Response object.
     *
     * @example return response('<h1>Hello World!</h1>', 200, 'text/html');
     *
     * @param string $content The body content of the response.
     * @param int $status The HTTP status code.
     * @param string $contentType The content mime-type header.
     * @return ResponseInterface The crafted HTTP response.
     */
    function response(string $content = '', int $status = 200, $contentType = 'text/html'): ResponseInterface
    {
        $response = response_factory()->createResponse($status);
        $response->getBody()->write($content);
        return $response->withHeader('Content-Type', $contentType);
    }
}

if (!function_exists('json_response')) {

    /**
     * Prepares a neat, machine-friendly JSON Response. Perfect for APIs!
     *
     * @example return json_response(['status' => 'success', 'data' => $userArray]);
     *
     * @param array|JsonSerializable $data The array or serializable object to encode.
     * @param int $status The HTTP status code.
     * @param int $flags Bitmask JSON options for json_encode.
     * @return ResponseInterface The JSON HTTP response.
     * @throws InvalidArgumentException If JSON encoding fails.
     */
    function json_response($data, int $status = 200, int $flags = JSON_HEX_TAG
    | JSON_HEX_APOS
    | JSON_HEX_AMP
    | JSON_HEX_QUOT
    | JSON_UNESCAPED_SLASHES): ResponseInterface
    {
        if (!is_array($data) && !is_subclass_of($data, JsonSerializable::class)) {
            throw new InvalidArgumentException(
                'Data must be an array or implement JsonSerializable interface'
            );
        }
        $response = response_factory()->createResponse($status);
        $response->getBody()->write(json_encode($data, $flags));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                sprintf('Unable to encode data to JSON: %s', json_last_error_msg())
            );
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}

if (!function_exists('redirect')) {

    /**
     * Sends visitors on a trip to a different URL via a Location header.
     *
     * @example return redirect('/login');
     *
     * @param string $url The target URL path.
     * @param int $status The redirection HTTP status code.
     * @return ResponseInterface The redirect HTTP response.
     */
    function redirect(string $url, int $status = 302): ResponseInterface
    {
        $response = response_factory()->createResponse($status);
        return $response->withHeader('Location', $url);
    }
}

if (!function_exists('redirect_to')) {

    /**
     * Builds a redirect response pointing to a registered named route.
     *
     * @example return redirect_to('profile_view', ['id' => 42]);
     *
     * @param string $routeName   The name of the route.
     * @param array  $parameters  Dynamic route arguments.
     * @param int    $status      The redirection HTTP status code.
     * @return ResponseInterface The configured redirect response.
     */
    function redirect_to(string $routeName, array $parameters = [], int $status = 302): ResponseInterface
    {
        /** @var RouterInterface $router */
        $router = container()->get(RouterInterface::class);
        return response_factory()
            ->createResponse($status)
            ->withHeader('Location', $router->generateUri($routeName, $parameters));
    }
}

if (!function_exists('render_view')) {

    /**
     * Renders a view template, injecting variables to generate HTML text.
     *
     * @example $html = render_view('welcome.html.plate', ['name' => 'Michel']);
     *
     * @param string $view The name/path of the view template.
     * @param array $context Dynamic variables to pass to the view.
     * @return string The raw rendered HTML/text content.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function render_view(string $view, array $context = []): string
    {
        if (!container()->has('render')) {
            throw new \LogicException('The "render_view" method requires the "michel/pure-plate" package. ' .
            'Try running "composer require michel/pure-plate".');
        }

        $renderer = container()->get('render');
        return $renderer->render($view, $context);
    }
}

if (!function_exists('render')) {

    /**
     * Renders a view template and directly packs it into a ready-to-go HTTP Response!
     *
     * @example return render('welcome.html.plate', ['name' => 'Michel']);
     *
     * @param string $view The view template name.
     * @param array $context Variables passed to the template.
     * @param int $status The HTTP status code.
     * @return ResponseInterface The rendered view HTTP response.
     */
    function render(string $view, array $context = [], int $status = 200): ResponseInterface
    {
        return response(render_view($view, $context), $status);
    }
}

if (!function_exists('url')) {

    /**
     * Generates an absolute URL link for a registered route name.
     *
     * @example $link = url('blog_show', ['slug' => 'hello-world']);
     *
     * @param string $name The route name.
     * @param array $parameters Dynamic route parameters.
     * @return string The fully generated URL.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function url(string $name, array $parameters = []): string
    {
        /**
         * @var RouterInterface $router
         */
        $router =  container()->get(RouterInterface::class);
        return $router->generateUri($name, $parameters, true);
    }
}

if (!function_exists('asset')) {

    /**
     * Generates a web-accessible URL path for static assets like CSS, images, and JS.
     *
     * @example $url = asset('css/main.css'); // => '/css/main.css'
     *
     * @param string $path The relative path to the asset.
     * @return string The absolute web path.
     */
    function asset(string $path): string
    {
        return '/'.ltrim($path, '/');
    }
}
