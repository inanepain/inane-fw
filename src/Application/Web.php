<?php

/**
 * Web
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\web
 * @category web
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Knot\Application;

use Exception;
use Inane\Config\Config;
use Inane\Config\ConfigAware\ConfigAwareAttribute;
use Inane\Config\ConfigAware\ConfigAwareInterface;
use Inane\Config\ConfigInterface;
use Inane\Config\ConfigManager;
use Inane\Db\Adapter\Adapter;
use Inane\Db\Table\AbstractTable;
use Inane\Dumper\Dumper;
use Inane\File\File;
use Inane\File\Path;
use Inane\Http\Client as HttpClient;
use Inane\Http\HttpStatus;
use Inane\Http\Request;
use Inane\Http\Response;
use Inane\Routing\Exception\InvalidRouteException;
use Inane\Routing\RouteMatch;
use Inane\Routing\Router;
use Inane\ServiceManager\ServiceManager;
use Inane\Session\SessionManager;
use Inane\Stdlib\Array\OptionsInterface;
use Inane\Stdlib\Exception\BadMethodCallException;
use Inane\Stdlib\Exception\UnexpectedValueException;
use Inane\Stdlib\Options;
use Inane\View\Exception\RuntimeException;
use Inane\View\Model\HttpModel;
use Inane\View\Model\ModelInterface;
use Inane\View\ViewManager;
use ReflectionObject;

use function count;
use function getcwd;
use function is_array;
use function is_null;
use function method_exists;
use function preg_match;
use function token_get_all;

use const GLOB_BRACE;
use const GLOB_NOSORT;
use const PREG_OFFSET_CAPTURE;
use const T_CLASS;
use const T_NAMESPACE;

/**
 * Web
 *
 * inane-fw
 *
 * @version 0.1.0
 */
/**
 * The application class
 *
 * This class is the main entry point of the application. It is responsible for setting up the application, routing the request to the controller, rendering the view and sending the response to the client.
 *
 * @version 0.1.0
 */
final class Web {
    //#region Properties
    /**
     * The instance of the application
     *
     * @var Web The instance of the application
     */
    private static Web $instance;

    protected(set) ServiceManager $services;

    /**
     * @var ViewManager The view object
     */
    protected ViewManager $view;
    protected Path $base;

    protected ConfigManager $configManager;
    /**
     * The application configuration
     *
     * @var OptionsInterface|Options|Config The application configuration
     */
    public Config|OptionsInterface $config {
        get => $this->configManager->getConfig();
    }
    /**
     * The router object
     *
     * @var \Inane\Routing\Router The router object
     */
    protected(set) Router $router;
    /**
     * The matched route
     *
     * @var \Inane\Routing\RouteMatch The matched route
     */
    public protected(set) ?RouteMatch $routeMatch;
    /**
     * @var \Inane\Http\Request The request object read from View
     */
    protected(set) Request $request {
        get => $this->view->request;
        set {
        }
    }
    /**
     * @var Response The response object read from View
     */
    protected(set) Response $response {
        /**
         * @return Response
         */
        get => $this->response ??= $this->request->getResponse();
        set => $this->response = $value;
    }
    /**
     * @var \Inane\Http\Client The HTTP client object
     */
    protected(set) HttpClient $httpClient;
    //#endregion Properties

    /**
     * The constructor
     *
     * The constructor is private to prevent creating multiple instances of the application.
     *
     * @return void
     */
    private function __construct(ConfigInterface $config) {
        $this->configManager = ConfigManager::instance()
            ->setConfig($config)
        ;

        $this->bootstrap();
    }

    /**
     * Gets the instance of the application
     *
     * @return Web
     */
    public static function getInstance(): Web {
        if (!isset(self::$instance)) self::$instance = new static(Config::fromConfigFile());

        return self::$instance;
    }

    private function getClassFromFile(File $file): ?string {
        if (!$file->isValid()) {
            throw new Exception("File not found: $file");
        }

        $src = $file->read();
        $tokens = token_get_all($src);

        $namespace = '';
        $class = '';
        $i = 0;

        while ($i < count($tokens)) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $i += 2; // skip namespace keyword and whitespace
                $namespace .= $tokens[$i][1];
                $i++;
            }

            if ($tokens[$i][0] === T_CLASS) {
                $i += 2; // skip class keyword and whitespace
                $class = $tokens[$i][1];
                break;
            }
            $i++;
        }

        if (!$class) {
            return null; // no class found
        }

        return $namespace ? $namespace . "\\" . $class : $class;
    }

    /**
     * Sets up the application
     *
     * Creates required objects and configuration them so that everything is ready to run.
     *
     * @return void
     */
    protected function bootstrap(): void {
        $this->base = new Path(getcwd());

        Dumper::$enabled = $this->config->dumper->enabled;

        $this->services = ServiceManager::createServiceManager($this->config->services);
        $this->bootstrapObject($this->services);
        AbstractTable::$db = $this->services->get(Adapter::class);

        $this->configureSession();
        $this->configureRouter();

        $this->view = new ViewManager($this->config->view->path);
        $this->httpClient = new HttpClient();
    }

    /**
     * Configures the session settings for the application.
     *
     * This method is responsible for setting up session parameters,
     * such as session lifetime, storage handlers, and other related
     * configurations required for proper session management.
     *
     * @return void
     */
    protected function configureSession(): void {
        if (!isset($_SESSION)) {
            // $cfg = $this->config->getConfig(SessionManager::class);
            // SessionManager::init($cfg->toArray());
            SessionManager::init([
                'name'            => $this->config->appId,
                'cookie_samesite' => 'Strict',
                'remember_me'     => true,  // Enables persistence
            ]);
        }
    }

    /**
     * Configures the router
     *
     * Creates the router using the configuration.
     *
     * @return void
     */
    protected function configureRouter(): void {
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
                foreach ($this->base->getFiles($glob, GLOB_BRACE | GLOB_NOSORT) as $file) {
                    if ($ignore = $controller->glob_ignore) {
                        preg_match($ignore, $file->getFilename(), $matches, PREG_OFFSET_CAPTURE);
                        if (!empty($matches)) continue;
                    }
                    if ($ns = $this->getClassFromFile($file)) $controllers[] = $ns;
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

    protected function bootstrapObject(object $object): void {
        if ($object instanceof ConfigAwareInterface) $object->setConfig($this->config);

        $reflection = new ReflectionObject($object);

        foreach ($reflection->getAttributes() as $classAttribute) {
            if ($classAttribute->getName() === ConfigAwareAttribute::class) {
                $this->configManager->setConfigFor($object);
            }

            if ($classAttribute->getName() === RequestAwareAttribute::class) {
            }
        }
    }

    /**
     * Sets the routing for a given request.
     *
     * This method attempts to match an incoming HTTP request against predefined routes. If no route is matched,
     * it throws an exception indicating that there was either 'Unmatched file' or 'Unmatched route'.
     *
     * @return void
     *
     * @throws BadMethodCallException
     * @throws InvalidRouteException When there's no matching `file` or `route`
     * @throws UnexpectedValueException
     */
    protected function routing(): void {
        $this->routeMatch = $this->router->match($this->request);

        if (is_null($this->routeMatch)) {
            $status = HttpStatus::NotFound;
            throw new InvalidRouteException($status->message(), $status->value);
        }
    }

    /**
     * Generates the view content
     *
     * @return void
     *
     * @throws \Inane\View\Exception\RuntimeException
     */
    protected function rendering(): void {
        // TODO: Check for REST controller and structure method calls based on command.
        $controller = new $this->routeMatch->class();
        $this->bootstrapObject($controller);

        $modelOrArray = $controller->{$this->routeMatch->method}($this->routeMatch->params);
        $model = is_array($modelOrArray) ? new ViewModel($modelOrArray) : $modelOrArray;

        if ($model instanceof ModelInterface) {
            $model = new HttpModel($model->variables->toArray());

            if (!$model->useLayout) {
                // render model
                // set response
                // return
            }
            // render http view
        } elseif ($model instanceof AppModelInterface) {
            method_exists($model, 'setProperties') && $model->setProperties($this->config->site);

            if (!$model->terminate) $model->setOptions([
                'template' => $this->routeMatch->template,
                'layout'   => $this->config->view->layout,
            ]);
        }

        // method_exists($model, 'setRequest') && $model->setRequest($this->request);

        // if (empty($model->template)) {
        // 	$model->setOptions(['layout' => $this->routeMatch->template]);
        // }

        // if ($model->useLayout) {
        // 	if (empty($model->layout)) {
        // 		$model->setOptions(['layout' => $this->config->view->layout]);
        // 	}
        // }

        $this->view->render($model);
    }

    /**
     * Sends the response to client
     *
     * @return never
     */
    protected function responding(): void {
        $this->httpClient->send($this->response);
    }

    /**
     * Runs the application
     *
     * @return int
     *
     * @throws BadMethodCallException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws \ReflectionException|InvalidRouteException
     */
    public function run(): int {
        $this->routing();
        $this->rendering();
        $this->responding();

        return 0;
    }
}
