<?php

declare(strict_types=1);

namespace Knot;

use Inane\Cli\Cli;
use Inane\Config\Config;
use Inane\Config\ConfigAwareAttribute;
use Inane\Config\ConfigAwareInterface;
use Inane\Console\Router\ConsoleRouter;
use Inane\Db\Adapter\Adapter;
use Inane\Db\Table\AbstractTable;
use Inane\File\Path;
use Inane\Routing\Router;
use Inane\Services\ServiceManager;
use Inane\Session\SessionManager;
use Inane\Stdlib\Exception\Exception;
use Inane\Stdlib\Options;
use Inane\Stdlib\Utility\ClassUtility;
use ReflectionObject;
use function getcwd;
use function preg_match;
use const GLOB_BRACE;
use const GLOB_NOSORT;
use const PREG_OFFSET_CAPTURE;

class Application {
    /**
     * The instance of the application
     *
     * @var Application The instance of the application
     */
    private static Application $instance;
    private ConsoleRouter|Router $router;
    protected(set) ServiceManager $services;

    protected Path $base;

    private(set) bool $isConsole = PHP_SAPI === 'cli';

    /**
     * Initialise the application.
     *
     * @return Application
     */
    public static function init(Config $config): Application {
        if (!isset(self::$instance)) self::$instance = new static($config);

        return self::$instance;
    }

    /**
     * Gets the instance of the application
     *
     * @return Application
     * @throws Exception
     */
    public static function app(): Application {
        if (!isset(self::$instance)) throw new Exception('Application not initialised');

        return self::$instance;
    }

    /**
     * The constructor
     *
     * The constructor is private to prevent creating multiple instances of the application.
     *
     * @return void
     */
    private function __construct(protected(set) Config $config) {
        $this->initialise();
        $this->bootstrap();
    }

    protected function bootstrapObject(object $object): void {
        if ($object instanceof ConfigAwareInterface) $object->setConfig($this->config);

        $reflection = new ReflectionObject($object);

        foreach($reflection->getAttributes() as $classAttribute) {
            if ($classAttribute->getName() === ConfigAwareAttribute::class) {
                $object->setConfig($this->config->getConfig($object::class));
            }
        }
    }

    protected Options $objectCache;

    public function createObject(string $class): object {
        if ($this->objectCache->has($class)) {
            return $this->objectCache->get($class);
        }

        $object = new $class();
        $this->bootstrapObject($object);
        $this->objectCache->set($class, $object);

        return $object;
    }

    protected function initialise(): void {
        $this->objectCache = new Options();
        $this->isConsole = Cli::isCli();
    }

    /**
     * Sets up the application
     *
     * Creates required objects and configuration them so that everything is ready to run.
     *
     * @return void
     */
    protected function bootstrap(): void {
        \Inane\Dumper\Dumper::$enabled = $this->config->dumper->enabled;

        $this->base = new Path(getcwd());

        $this->services = ServiceManager::createServiceManager($this->config->services);
        $this->bootstrapObject($this->services);
        AbstractTable::$db = $this->services->get(Adapter::class);

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
     * @throws \Inane\Stdlib\Exception\RuntimeException
     */
    protected function configureSession(): void {
        if (!isset($_SESSION)) {
            SessionManager::init([
                'name'            => $this->config->appId,
                'cookie_samesite' => 'Strict',
                'remember_me'     => true,  // Enables persistence
            ]);
        }
    }

    protected function configureRouter(): void {
        if ($this->isConsole) $this->configureRouterConsole();
        else $this->configureRouterHTTP();
    }

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
                foreach($this->base->getFiles($glob, GLOB_BRACE | GLOB_NOSORT) as $file) {
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

    protected function configureRouterConsole(): void {
        global $argv;
        $routerConfig = new Options([
            'arguments' =>    $argv,
            'commands'      => [
                'glob'        => 'src/*/*Commands.php',
                'glob_ignore' => '/(Abstract)/',
                'default'     => [],
            ],
        ]);

        $this->router = new ConsoleRouter($routerConfig->arguments->toArray());

        $commands = new Options();
        if ($command = $routerConfig->commands) {
            if ($glob = $command->glob) {
                foreach($this->base->getFiles($glob, GLOB_BRACE | GLOB_NOSORT) as $file) {
                    if ($ignore = $command->glob_ignore) {
                        preg_match($ignore, $file->getFilename(), $matches, PREG_OFFSET_CAPTURE);
                        if (!empty($matches)) continue;
                    }
                    if ($ns = ClassUtility::getClassFromFile($file)) $commands[] = $ns;
                }
            }

            if ($default = $command->default) {
                $commands->merge($default)
                    ->unique()
                ;
            }
        }

        //         dd($commands, 'commands');
        $this->router->registerCommands($commands);
    }

    /**
     * Runs the application
     *
     * @return never
     *
     * @throws \Exception
     */
    public function run(): never {
        $code = $this->router->run();

        exit($code);
    }
}
