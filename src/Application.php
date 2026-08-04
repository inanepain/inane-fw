<?php

/**
 * inane-fw
 *
 * Inane Framework
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\PROJECT
 * @category PROJECT
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 *
 */

declare(strict_types = 1);

namespace Knot;

use Exception;
use Inane\App\ApplicationInterface;
use Inane\Cli\Cli;
use Inane\Config\{
    Config,
    ConfigAware\ConfigAwareAttribute,
    ConfigAware\ConfigAwareInterface,
    ConfigInterface,
    ConfigManager};
use Inane\Console\Router\ConsoleRouter;
use Inane\Db\Adapter\Adapter;
use Inane\Db\Table\AbstractTable;
use Inane\Dumper\Dumper;
use Inane\File\Path;
use Inane\Routing\Exception\InvalidRouteException;
use Inane\Routing\RouteMatch;
use Inane\Routing\Router;
use Inane\ServiceManager\Exception\NotFoundException;
use Inane\ServiceManager\ServiceManager;
use Inane\Session\SessionManager;
use Inane\Stdlib\{
    Array\OptionsInterface,
    Exception\BadMethodCallException,
    Exception\JsonException,
    Exception\RuntimeException,
    Exception\UnexpectedValueException,
    Options,
    Utility\ClassUtility};
use ReflectionException;
use ReflectionObject;

use function getcwd;
use function is_null;
use function preg_match;

use const GLOB_BRACE;
use const GLOB_NOSORT;
use const PHP_SAPI;
use const PREG_OFFSET_CAPTURE;

class Application implements ApplicationInterface {
    /**
     * Private constructor method for initialising the class with configuration.
     *
     * @param ConfigInterface $config The application configuration.
     *
     * @return void
     *
     * @throws Exception
     */
    private function __construct(ConfigInterface $config) {
        $this->configManager = ConfigManager::instance()
            ->setConfig($config)
        ;

        $this->bootstrap();
        $this->initialise();
    }

    /**
     * The instance of the application
     *
     * @var Application The instance of the application
     */
    private static Application $instance;

    /**
     * @var ConsoleRouter|Router   ConsoleRouter | Router
     */
    private ConsoleRouter|Router $router;

    /**
     * The configuration manager
     *
     * @var ConfigManager The configuration manager responsible for handling application configuration.
     */
    protected ConfigManager $configManager;

    /**
     * The application configuration
     *
     * @var Config|OptionsInterface The application configuration object.
     */
    public Config|OptionsInterface $config {
        get => $this->configManager->getConfig();
    }

    /**
     * The service manager
     *
     * @var ServiceManager The service manager responsible for managing and providing access to services.
     */
    protected(set) ServiceManager $serviceManager;

    /**
     * @var Path  The Path class represents a file system path and provides methods for manipulating and working with paths.
     */
    protected Path $base;

    /**
     * @var bool Returns true if the application is running in console mode, false otherwise.
     */
    private(set) bool $isConsole = PHP_SAPI === 'cli';

    /**
     * The matched route
     *
     * @var null|RouteMatch The matched route
     */
    protected(set) ?RouteMatch $routeMatch;

    /**
     * Returns the singleton instance of the application.
     *
     * @return Application The application instance
     *
     * @throws Exception If an error occurs while creating the application instance
     */
    public static function app(): self {
        if (!isset(self::$instance)) self::$instance = new static(Config::fromConfigFile());

        return self::$instance;
    }

    /**
     * Bootstraps an object by injecting configuration based on its attributes or implemented interfaces.
     *
     * @param object $object The object to be bootstrapped.
     *
     * @return void
     */
    protected function bootstrapObject(object $object): void {
        if ($object instanceof ConfigAwareInterface) $object->setConfig($this->config);

        $reflection = new ReflectionObject($object);

        foreach($reflection->getAttributes(ConfigAwareAttribute::class) as $classAttribute) {
            $this->configManager->setConfigFor($object);
        }
    }

    /**
     * Sets up the application
     *
     * Creates required objects and configuration them so that everything's ready to run.
     *
     * @return void
     */
    protected function bootstrap(): void {
        $this->isConsole = Cli::isCli();
    }

    /**
     * Initialises the application components
     *
     * Sets up the dumper configuration, base path, service manager,
     * and configures session and router.
     *
     * @return void
     * @throws NotFoundException
     * @throws RuntimeException
     */
    protected function initialise(): void {
        Dumper::$enabled = $this?->config?->dumper?->enabled ?? false;
        Dumper::$bufferOutput = $this->isConsole ? false : ($this?->config?->dumper?->bufferOutput ?? true);

        $this->base = new Path(getcwd());

        $this->serviceManager = ServiceManager::createServiceManager($this->config->services);
        $this->bootstrapObject($this->serviceManager);
        AbstractTable::$db = $this->serviceManager->get(Adapter::class);

        $this->configureSession();
        $this->configureRouter();
    }

    /**
     * Configures the session settings for the application.
     *
     * This method is responsible for setting up session parameters,
     * such as session lifetime, storage handlers, and other related
     * configurations required for proper session management.
     *
     * @return void
     *
     * @throws RuntimeException
     */
    protected function configureSession(): void {
        if (!isset($_SESSION)) {
            SessionManager::init([
                'name'            => $this->config->appId,
                'cookie_samesite' => 'Strict',
                'remember_me'     => true,
                // Enables persistence
            ]);
        }
    }

    /**
     * Configures the router based on whether the application is running in console mode.
     *
     * If running in console mode, it configures the console router; otherwise,
     * it configures the HTTP router.
     *
     * @return void
     * @throws Exception
     */
    protected function configureRouter(): void {
        if ($this->isConsole) $this->configureRouterConsole();
        else $this->configureRouterHTTP();
    }

    /**
     * Configures the HTTP router with the defined options and controllers.
     *
     * The method initialises the router with predefined configurations such as
     * query string handling, controller glob patterns, and default controllers.
     * It also merges additional configurations from an external source, processes
     * controller files, and adds the resulting routes to the router.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function configureRouterHTTP(): void {
        $routerConfig = new Options([
            'splitQuerystring' => false,
            'controller'       => [
                'glob'        => 'src/*/*Controller.php',
                'glob_ignore' => '/(Abstract)/',
                'default'     => [],
            ],
        ]);
        $routerConfig->merge($this->config->router);

        $this->router = new Router(splitQuerystring: $routerConfig->splitQuerystring);
        $controllers = new Options();

        if ($controller = $routerConfig->controller) {
            if ($glob = $controller->glob) {
                foreach($this->base->getFiles($glob, GLOB_BRACE | GLOB_NOSORT) ?: [] as $file) {
                    if ($ignore = $controller->glob_ignore) {
                        preg_match($ignore, $file->getFilename(), $matches, PREG_OFFSET_CAPTURE);
                        if (!empty($matches)) continue;
                    }
                    if ($ns = ClassUtility::getClassFromFile($file)) $controllers[] = $ns;
                }
            }

            if ($default = $controller->default) {
                $controllers->merge($default)
                    ->unique()
                ;
            }
        }

        // dd($controllers);
        $this->router->addRoutes($controllers);
    }

    /**
     * Configures the console router with available commands.
     *
     * This method sets up command routing by discovering command classes using glob patterns,
     * excluding abstract classes, and registering them with the console router.
     *
     * @return void
     * @throws \Inane\Stdlib\Exception\Exception
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function configureRouterConsole(): void {
        global $argv;

        $routerConfig = $this->configManager->getConfig(ConsoleRouter::class)->modify([
            'arguments' => $argv,
            'commands'  => [
                'path'        => $this->base
            ],
        ]);

        $this->router = new ConsoleRouter($argv, $routerConfig);
        $this->router->buildCommands();
    }

    /**
     * Routes the request to the controller
     *
     * @return void
     *
     * @throws BadMethodCallException
     * @throws InvalidRouteException
     * @throws UnexpectedValueException
     */
    protected function routing(): void {
        $this->routeMatch = $this->router->match($this->request);

        if (is_null($this->routeMatch)) throw new InvalidRouteException('Request Error: Unmatched `file` or `route`!',
            AppError::InvalidRoute->value);
    }

    /**
     * Runs the application
     *
     * @return bool|int Return status
     *
     * @throws Exception
     */
    public function run(): bool|int {
        return $this->router->run();
    }
}
