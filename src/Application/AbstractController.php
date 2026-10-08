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

use Inane\Config\ConfigAware\ConfigAwareInterface;
use Inane\Config\ConfigAware\ConfigAwareTrait;
use Inane\Http\Request;
use Inane\Http\Response;
use Inane\Routing\RouteMatch;
use Inane\ServiceManager\ServiceManager;
use Throwable;

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
     * Constructor for the abstract controller.
     *
     * This method is responsible for initialising various components such as RouteMatch, Request,
     * Response, ServiceManager. It also calls an initialisation function to set up any custom
     * settings required by child classes that extend this AbstractController.
     *
     * @throws Throwable
     */
    public function __construct() {
        $app = Web::instance();
        $this->routeMatch = $app->routeMatch;
        $this->request = $app->request;
        $this->response = $app->response;
        $this->serviceManager = $app->services;

        $this->initialise();
    }

    /**
     * Initialises the necessary settings or configurations required for the method's operation.
     *
     * Override this method in child classes to initialise the controller with custom settings.
     *
     * @return void
     */
    protected function initialise(): void {}
}
