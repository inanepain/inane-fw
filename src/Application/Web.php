<?php

/**
 * Framework
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\fw
 * @category fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Knot\Application;

use Inane\Config\Config;
use Inane\Config\ConfigAware\ConfigAwareAttribute;
use Inane\Config\ConfigAware\ConfigAwareInterface;
use Inane\Config\ConfigInterface;
use Inane\Config\ConfigManager;
use Inane\Config\Exception\ConfigNotFoundException;
use Inane\Db\Adapter\Adapter;
use Inane\Db\Table\AbstractTable;
use Inane\Dumper\Dumper;
use Inane\Event\EventDispatcher;
use Inane\Event\Provider\PrioritisedListenerProvider;
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
use Inane\View\Renderer\PhpRenderer;
use Inane\View\ViewManager;
use InvalidArgumentException;
use Knot\Application\Event\RenderingEvent;
use Knot\Application\Event\RequestProcessingEvent;
use Knot\Application\Event\ResponseEvent;
use Knot\Application\Event\RoutingEvent;
use Knot\Session\UserSession;
use ReflectionException;
use ReflectionObject;
use Throwable;

use function array_keys;
use function count;
use function getcwd;
use function is_array;
use function is_null;
use function preg_match;
use function strcasecmp;
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
 * This class is the main entry point of the application. It's responsible for setting up the application, routing the request to the controller, rendering the view and sending the response to the client.
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

    /**
     * @var ServiceManager Application services.
     */
    protected(set) ServiceManager $services;

    /**
     * @var SiteView Application view renderer.
     */
    protected SiteView $view;

    /**
     * @var Path Base path for controller discovery.
     */
    protected Path $base;

    /**
     * @var ConfigManager Application configuration manager.
     */
    protected ConfigManager $configManager;

    /**
     * The application configuration
     *
     * @var OptionsInterface|Config The application configuration
     */
    public Config|OptionsInterface $config {
        /**
         * Gets the application configuration.
         *
         * @return Config|OptionsInterface
         *
         * @throws ConfigNotFoundException If no configuration is available.
         */
        get => $this->configManager->getConfig();
    }

    /**
     * The router object
     *
     * @var Router The router object
     */
    protected(set) Router $router;

    /**
     * The matched route
     *
     * @var null|RouteMatch The matched route
     */
    protected(set) ?RouteMatch $routeMatch;

    /**
     * @var Request The request object read from View
     */
    protected(set) Request $request;

    /**
     * @var Response The response object read from View
     */
    protected(set) Response $response {
        /**
         * Gets or initialises the response from the current request.
         *
         * @return Response
         * @throws \Inane\Http\Exception\RuntimeException
         */
        get => $this->response ??= $this->request->getResponse();
        /**
         * Stores the application response.
         *
         * @param Response $value Response to store.
         *
         * @return void
         */
        set => $this->response = $value;
    }

    /**
     * @var HttpClient The HTTP client object
     */
    protected(set) HttpClient $httpClient;

    /**
     * @var PrioritisedListenerProvider Ordered web lifecycle listeners.
     */
    private PrioritisedListenerProvider $eventProvider;

    /**
     * @var EventDispatcher Web lifecycle event dispatcher.
     */
    private EventDispatcher $eventDispatcher;

    /**
     * @var int Priority placing built-in handlers after default custom listeners.
     */
    private const int CORE_EVENT_PRIORITY = -100;

    //#endregion Properties

    /**
     * Web constructor
     *
     * The constructor is private to prevent creating multiple instances of the application.
     *
     * @param ConfigInterface $config Application configuration.
     *
     * @return void
     *
     * @throws Throwable If application initialisation fails.
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
     *
     * @throws Throwable If configuration loading or application initialisation fails.
     */
    public static function instance(): self {
        if (!isset(self::$instance)) self::$instance = new Web(Config::fromConfigFile());

        return self::$instance;
    }

    /**
     * Extracts the first-class name and its namespace from a PHP file.
     *
     * @param File $file Controller source file.
     *
     * @return string|null Class name, or null when no class is found.
     *
     * @throws \RuntimeException If the file is invalid.
     * @throws Throwable If reading the source file fails.
     */
    private function getClassFromFile(File $file): ?string {
        if (!$file->isValid()) {
            throw new \RuntimeException("File not found: $file");
        }

        $src = $file->read();
        $tokens = token_get_all($src);

        $namespace = '';
        $class = '';
        $i = 0;

        while($i < count($tokens)) {
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
     * Creates and configures the objects required to run the application.
     *
     * @return void
     *
     * @throws Throwable If configuration or service initialisation fails.
     */
    protected function bootstrap(): void {
        $this->base = new Path(getcwd());

        Dumper::$enabled = $this->config->dumper->enabled;

        $this->services = ServiceManager::createServiceManager($this->config->services);
        $this->bootstrapObject($this->services);
        AbstractTable::$db = $this->services->get(Adapter::class);

        $this->configureSession();
        $this->configureRouter();

        $this->request = new Request();
        $this->view = new SiteView(new ViewManager(new PhpRenderer($this->config->view->path)), $this->config->view->layout);
        $this->httpClient = new HttpClient();
        $this->configureEvents();
    }

    /**
     * Registers a listener for a web lifecycle event.
     *
     * Custom listeners run before the built-in stage handler by default.
     * Priorities below -100 run after the built-in handler.
     *
     * @param class-string $event    Event class name.
     * @param callable     $listener Listener callable.
     * @param int          $priority Listener priority.
     *
     * @return $this
     */
    public function addEventListener(string $event, callable $listener, int $priority = 100): self {
        $this->eventProvider->addListener($event, $listener, $priority);

        return $this;
    }

    /**
     * Registers methods marked with the event listener attribute.
     *
     * @param object $listener Listener object.
     *
     * @return $this
     *
     * @throws InvalidArgumentException When an attributed listener is invalid.
     */
    public function addEventListenerObject(object $listener): self {
        $this->eventProvider->addAttributedListener($listener);

        return $this;
    }

    /**
     * Initialises an event dispatching and registers built-in lifecycle handlers.
     *
     * @return void
     */
    private function configureEvents(): void {
        $this->eventProvider = new PrioritisedListenerProvider();
        $this->eventDispatcher = new EventDispatcher($this->eventProvider);
        $this->eventProvider
            ->addListener(RoutingEvent::class, $this->handleRoutingEvent(...), self::CORE_EVENT_PRIORITY)
            ->addListener(RenderingEvent::class, $this->handleRenderingEvent(...), self::CORE_EVENT_PRIORITY)
            ->addListener(ResponseEvent::class, $this->handleResponseEvent(...), self::CORE_EVENT_PRIORITY)
        ;
    }

    /**
     * Configures the session settings for the application.
     *
     * This method is responsible for setting up session parameters,
     * such as session lifetime, storage handlers and other related
     * configurations required for proper session management.
     *
     * @return void
     *
     * @throws Throwable If configuration lookup or session initialisation fails.
     */
    protected function configureSession(): void {
        if (!isset($_SESSION)) {
            // $cfg = $this->config->getConfig(SessionManager::class);
            // SessionManager::init($cfg->toArray());
            SessionManager::init([
                'name'            => $this->config->appId,
                'cookie_samesite' => 'Strict',
                'remember_me'     => true,
                // Enables persistence
            ]);
        }
    }

    /**
     * Configures the router
     *
     * Creates the router using the configuration.
     *
     * @return void
     *
     * @throws Throwable If configuration lookup, controller discovery or route registration fails.
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
                foreach($this->base->getFiles($glob, GLOB_BRACE | GLOB_NOSORT) as $file) {
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

        $this->router->addRoutes($controllers);
    }

    /**
     * Applies configuration to configuration-aware objects.
     *
     * @param object $object Object to initialise.
     *
     * @return void
     *
     * @throws Throwable If configuration lookup or injection fails.
     */
    protected function bootstrapObject(object $object): void {
        if ($object instanceof ConfigAwareInterface) $object->setConfig($this->config);

        $reflection = new ReflectionObject($object);
        foreach($reflection->getAttributes() as $classAttribute) {
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
     * @throws InvalidRouteException When there's no matching `file` or `route`
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
     * @throws RuntimeException
     * @throws Throwable If controller execution or template rendering fails.
     */
    protected function rendering(): void {
        $controller = new $this->routeMatch->class();
        $this->bootstrapObject($controller);

        $modelOrArray = $controller->{$this->routeMatch->method}($this->routeMatch->params);
        // Direct responses bypass model conversion and view rendering.
        if ($modelOrArray instanceof Response) {
            $this->response = $modelOrArray;

            return;
        }

        $model = is_array($modelOrArray) ? new HttpModel($modelOrArray) : $modelOrArray;
        if (!$model instanceof HttpModel) throw new RuntimeException('Web controllers must return an HTTP model, array or response.');

        $this->response = $this->view->render(
            model : $model,
            route : $this->routeMatch,
            router: $this->router,
            // Preserve flash notices for the destination of a redirect.
            notice: array_any(array_keys($model->headers), static fn(string $name): bool => strcasecmp($name, 'Location') === 0)
                ? '' : (string)UserSession::getFlash('notice', ''),
        );
    }

    /**
     * Sends the response to a client
     *
     * @return never
     */
    protected function responding(): never {
        $this->httpClient->send($this->response);
    }

    /**
     * Uses a listener-supplied route to match or performs routing.
     *
     * @param RoutingEvent $event Current routing stage.
     *
     * @return void
     *
     * @throws InvalidRouteException If no route matches the request.
     */
    private function handleRoutingEvent(RoutingEvent $event): void {
        $this->request = $event->request;
        if ($event->routeMatch !== null) {
            $this->routeMatch = $event->routeMatch;

            return;
        }

        $this->routing();
        $event->routeMatch = $this->routeMatch;
    }

    /**
     * Uses a listener-supplied response or renders the matched controller.
     *
     * @param RenderingEvent $event Current rendering stage.
     *
     * @return void
     *
     * @throws Throwable If controller initialisation, execution or rendering fails.
     */
    private function handleRenderingEvent(RenderingEvent $event): void {
        if ($event->response !== null) {
            $this->response = $event->response;

            return;
        }

        $this->request = $event->request;
        $this->routeMatch = $event->routeMatch;
        $this->rendering();
        $event->response = $this->response;
    }

    /**
     * Sends the response unless a listener has already handled it.
     *
     * @param ResponseEvent $event Current response stage.
     *
     * @return void
     *
     * @throws Throwable If sending the response fails.
     */
    private function handleResponseEvent(ResponseEvent $event): void {
        if ($event->isHandled()) return;

        $this->response = $event->response;
        // Sending terminates execution, so record handling before sending.
        $event->markHandled();
        $this->responding();
    }

    /**
     * Runs the application
     *
     * @return void
     *
     * @throws BadMethodCallException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws ReflectionException|InvalidRouteException|Throwable
     */
    public function run(): void {
        $requestEvent = new RequestProcessingEvent($this->request);
        $this->eventDispatcher->dispatch($requestEvent);
        $this->request = $requestEvent->request;

        $routingEvent = new RoutingEvent($this->request);
        $this->eventDispatcher->dispatch($routingEvent);
        if ($routingEvent->routeMatch === null) {
            $status = HttpStatus::NotFound;
            throw new InvalidRouteException($status->message(), $status->value);
        }
        $this->routeMatch = $routingEvent->routeMatch;

        $renderingEvent = new RenderingEvent($this->request, $this->routeMatch);
        $this->eventDispatcher->dispatch($renderingEvent);
        if ($renderingEvent->response === null) {
            throw new RuntimeException('The rendering event did not produce an HTTP response.');
        }
        $this->response = $renderingEvent->response;

        $responseEvent = new ResponseEvent($this->response);
        $this->eventDispatcher->dispatch($responseEvent);
        if (!$responseEvent->isHandled()) {
            throw new RuntimeException('The response event did not handle the HTTP response.');
        }
    }
}
