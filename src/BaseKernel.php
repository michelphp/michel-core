<?php
declare(strict_types=1);

namespace Michel\Framework\Core;

use DateTimeImmutable;
use Michel\Attribute\AttributeRouteCollector;
use Michel\Env\DotEnv;
use Michel\Framework\Core\Debug\DebugDataCollector;
use Michel\Framework\Core\ErrorHandler\ErrorHandler;
use Michel\Framework\Core\ErrorHandler\ExceptionHandler;
use Michel\Framework\Core\Handler\RequestHandler;
use Michel\Framework\Core\Http\Exception\HttpException;
use Michel\Framework\Core\Http\Exception\HttpExceptionInterface;
use InvalidArgumentException;
use Michel\Framework\Core\Finder\ControllerFinder;
use Michel\Package\PackageInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Throwable;
use function array_filter;
use function array_keys;
use function array_merge;
use function date_default_timezone_set;
use function error_reporting;
use function getenv;
use function implode;
use function in_array;
use function json_encode;
use function sprintf;

/**
 * @package    Michel.F
 * @author    Michel 
 * @license    https://opensource.org/license/mpl-2-0 Mozilla Public License v2.0
 */
abstract class BaseKernel
{
    private const DEFAULT_ENV = 'prod';
    public const VERSION = '0.0.1-alpha';
    public const NAME = 'MICHEL';
    private const DEFAULT_ENVIRONMENTS = [
        'dev',
        'prod'
    ];
    private string $env = self::DEFAULT_ENV;
    private bool $debug = false;

    protected ContainerInterface $container;
    /**
     * @var array<MiddlewareInterface>|array<string>
     */
    private array $middlewareCollection = [];
    private ?DebugDataCollector $debugDataCollector = null;

    /**
     * BaseKernel constructor.
     */
    public function __construct()
    {
        App::init($this->loadConfigurationIfExists('framework.php'));
        $this->boot();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $request = $request->withAttribute('request_id', strtoupper(uniqid('REQ')));
            $request = $request->withAttribute('debug_collector', $this->debugDataCollector);

            $requestHandler = new RequestHandler($this->container, $this->middlewareCollection);
            $response =  $requestHandler->handle($request);
            if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 600) {
                throw new HttpException($response->getStatusCode(), $response->getReasonPhrase());
            }
            return $response;
        } catch (Throwable $exception) {
            if (!$exception instanceof HttpExceptionInterface) {
                $this->logException($exception, $request);
            }

            $exceptionHandler = $this->container->get(ExceptionHandler::class);
            return $exceptionHandler->render($request, $exception);
        }
    }

    final public function getEnv(): string
    {
        return $this->env;
    }

    final public function isDebug(): bool
    {
        return $this->debug;
    }

    final public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    abstract public function getProjectDir(): string;

    abstract public function getCacheDir(): string;

    abstract public function getLogDir(): string;

    abstract public function getConfigDir(): string;

    abstract public function getPublicDir(): string;

    abstract public function getEnvFile(): string;

    abstract protected function afterBoot(): void;

    protected function loadContainer(array $definitions): ContainerInterface
    {
        return App::createContainer($definitions, ['cache_dir' => $this->getCacheDir()]);
    }

    final protected function logException(Throwable $exception, ServerRequestInterface  $request): void
    {
        $this->log([
            '@timestamp' => (new DateTimeImmutable())->format('c'),
            'log.level' => 'error',
            'id' => $request->getAttribute('request_id'),
            'http.request' => [
                'method' => $request->getMethod(),
                'url' => $request->getUri()->__toString(),
            ],
            'message' => $exception->getMessage(),
            'error' => [
                'code' => $exception->getCode(),
                'stack_trace' => $exception->getTrace(),
                'class' => get_class($exception),
            ],
            'source' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        ]);
    }

    final protected function log(array $data, string $logFile = null): void
    {
        $logDir = $this->getLogDir();
        if (empty($logDir)) {
            throw new InvalidArgumentException('The log dir is empty, please set it in the Kernel.');
        }

        if (!is_dir($logDir) && !mkdir($logDir, 0777, true) && !is_dir($logDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $logDir));
        }
        if ($logFile === null) {
            $logFile = $this->getEnv() . '.log';
        }
        error_log(
            json_encode($data, JSON_UNESCAPED_SLASHES) . PHP_EOL,
            3,
            filepath_join( $logDir, $logFile)
        );
    }

    private function boot(): void
    {
        $this->initEnv();
        $this->configureErrorHandling();
        $this->configureTimezone();

        $middleware = $this->loadConfigurationIfExists('middleware.php');
        $middleware = array_filter($middleware, function ($environments) {
            return in_array($this->getEnv(), $environments);
        });
        $this->middlewareCollection = array_keys($middleware);

        $this->loadDependencies();
        $this->afterBoot();
    }

    private function initEnv(): void
    {
        (new DotEnv($this->getEnvFile()))->load();
        foreach (['APP_ENV' => self::DEFAULT_ENV, 'APP_TIMEZONE' => 'UTC', 'APP_LOCALE' => 'en', 'APP_DEBUG' => false] as $k => $value) {
            if (getenv($k) === false) {
                self::putEnv($k, $value);
            }
        }

        $environments = self::getAvailableEnvironments();
        if (!in_array(getenv('APP_ENV'), $environments)) {
            throw new InvalidArgumentException(sprintf(
                    'The env "%s" do not exist. Defined environments are: "%s".',
                    getenv('APP_ENV'),
                    implode('", "', $environments))
            );
        }
        $this->env =  strtolower($_ENV['APP_ENV']);
        $this->debug = $_ENV['APP_DEBUG'] ?: ($this->env === 'dev');
    }

    private function configureErrorHandling(): void
    {
        ini_set("log_errors", '1');
        ini_set("error_log", $this->getLogDir() . '/error_log.log');

        if ($this->getEnv() === 'dev') {
            ErrorHandler::register();
            return;
        }

        ini_set("display_startup_errors", '0');
        ini_set("display_errors", '0');
        ini_set("html_errors", '0');

        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    }

    private function configureTimezone(): void
    {
        $timezone = getenv('APP_TIMEZONE');
        if ($timezone === false) {
            throw new \RuntimeException('APP_TIMEZONE environment variable is not set.');
        }
        date_default_timezone_set($timezone);
    }

    final public function loadConfigurationIfExists(string $fileName): array
    {
        $filePath = filepath_join( $this->getConfigDir(), $fileName);
        if (file_exists($filePath)) {
            return require $filePath;
        }

        return [];
    }

    private function loadDependencies(): void
    {
        list($services, $parameters, $listeners, $routes, $commands, $packages, $controllers) = $this->loadDependenciesConfiguration();
        $definitions = array_merge(
            $parameters,
            $services,
            [
                'michel.packages' => $packages,
                'michel.commands' => $commands,
                'michel.listeners' => $listeners,
                'michel.middleware' => $this->middlewareCollection,
                BaseKernel::class => $this
            ]
        );
        $definitions['michel.services_ids'] = array_keys($definitions);
        $definitions['michel.controllers'] = static function (ContainerInterface $container) use ($controllers) {
            $scanner = new ControllerFinder($controllers, $container->get('michel.current_cache'));
            return $scanner->findControllerClasses();
        };
        $definitions['michel.routes'] = static function (ContainerInterface $container) use ($routes) {
            $collector = null;
            if (PHP_VERSION_ID >= 80000) {
                $controllers = $container->get('michel.controllers');
                $collector = new AttributeRouteCollector(
                    $controllers,
                    $container->get('michel.current_cache')
                );
            }
            return array_merge($routes, $collector ? $collector->collect() : []);
        };

        $this->container = $this->loadContainer($definitions);
        $this->debugDataCollector = $this->container->get(DebugDataCollector::class);
        unset($services, $parameters, $listeners, $routes, $commands, $packages, $controllers, $definitions);
    }

    private function loadDependenciesConfiguration(): array
    {
        $services = $this->loadConfigurationIfExists('services.php');
        $parameters = $this->loadParameters();
        $listeners = $this->loadConfigurationIfExists('listeners.php');
        $routes = $this->loadConfigurationIfExists('routes.php');
        $commands = $this->loadConfigurationIfExists('commands.php');
        $controllers = $this->loadConfigurationIfExists('controllers.php');
        $packages = $this->getPackages();
        foreach ($packages as $package) {
            $services = array_merge($package->getDefinitions(), $services);
            $parameters = array_merge($package->getParameters(), $parameters);
            $listeners = array_merge_recursive($package->getListeners(), $listeners);
            $routes = array_merge($package->getRoutes(), $routes);
            $commands = array_merge($package->getCommandSources(), $commands);
            $controllers = array_merge($package->getControllerSources(), $controllers);
        }

        return [$services, $parameters, $listeners, $routes, $commands, $packages, $controllers];
    }

    /**
     * @return array<PackageInterface>
     */
    private function getPackages(): array
    {
        $packagesName = $this->loadConfigurationIfExists('packages.php');
        $packages = [];
        foreach ($packagesName as $packageName => $envs) {
            if (!in_array($this->getEnv(), $envs)) {
                continue;
            }
            $packages[] = new $packageName();
        }
        return $packages;
    }

    private function loadParameters(): array
    {
        $parameters = $this->loadConfigurationIfExists('parameters.php');
        $parameters['michel.environment'] = $this->getEnv();
        $parameters['michel.debug'] = $this->isDebug();
        $parameters['michel.project_dir'] = $this->getProjectDir();
        $parameters['michel.cache_dir'] = $this->getCacheDir();
        $parameters['michel.logs_dir'] = $this->getLogDir();
        $parameters['michel.config_dir'] = $this->getConfigDir();
        $parameters['michel.public_dir'] = $this->getPublicDir();
        $parameters['michel.current_cache'] = $this->getEnv() === 'dev' ? null : $this->getCacheDir();

        return $parameters;
    }

    private static function getAvailableEnvironments(): array
    {
        return array_unique(array_merge(self::DEFAULT_ENVIRONMENTS, App::getCustomEnvironments()));
    }

    private static function putEnv(string $name, $value): void
    {
        putenv(sprintf('%s=%s', $name, is_bool($value) ? ($value ? '1' : '0') : $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
