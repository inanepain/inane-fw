<?php

/**
 * Playground: develop
 *
 * Rough environment for testing, developing and playing around with PHP odds and ends.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  playground\develop
 * @category develop
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Knot\Mvc;

use Inane\Config\ConfigAware\ConfigAwareInterface;
use Inane\Config\ConfigAware\ConfigAwareTrait;
use Inane\Http\Request;
use Inane\Http\Response;
use Inane\Routing\RouteMatch;
use Inane\ServiceManager\ServiceManager;
use Knot\Application;

/**
 * AbstractController
 *
 * @package Develop\Tinker
 */
abstract class AbstractController implements ConfigAwareInterface {
    use ConfigAwareTrait;

//#region Properties
    protected RouteMatch $routeMatch;
    protected Request $request;
    protected Response $response;
    protected ServiceManager $serviceManager;
//#endregion Properties

    /**
     * Constructor method to initialise the class with the required application components.
     *
     * @return void
     *
     * @throws \RuntimeException If the application instance could not be retrieved.
     */
    public function __construct() {
        $app = Application::app();

        $this->routeMatch = $app->routeMatch;
        $this->request = $app->request;
        $this->response = $app->response;
        $this->serviceManager = $app->serviceManager;

        $this->initialise();
    }

    /**
     * Initialises the necessary settings or configurations required for the method's operation.
     *
     * Override this method in child classes to initialise the controller with custom settings.
     *
     * @return void
     */
    protected function initialise() {}
}
