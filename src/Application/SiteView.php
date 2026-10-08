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

use Inane\Http\Response;
use Inane\Routing\RouteMatch;
use Inane\Routing\Router;
use Inane\View\Model\HttpModel;
use Inane\View\Model\IterativeModel;
use Inane\View\ViewManager;
use Knot\Session\UserSession;
use Throwable;
use TypeError;

use function array_is_list;

/**
 * Composes controller pages into the site's nested layout.
 */
final readonly class SiteView {
    /**
     * Configures the renderer and layout template.
     *
     * @param ViewManager $manager Nested model renderer.
     * @param string      $layout  Layout template.
     *
     * @throws TypeError
     */
    public function __construct(private ViewManager $manager, private string $layout) {}

    /**
     * Renders a page whilst retaining its HTTP options and children.
     *
     * @param HttpModel  $model  Controller page model.
     * @param RouteMatch $route  Matched controller route.
     * @param Router     $router Router used to build navigation links.
     * @param string     $notice Flash notice.
     *
     * @return Response
     *
     * @throws Throwable If URL generation or template rendering fails.
     */
    public function render(HttpModel $model, RouteMatch $route, Router $router, string $notice = ''): Response {
        $response = new Response(body: '', status: $model->status, headers: $model->headers);
        if ($response->hasHeader('Location')) {
            if ($response->getStatusCode() === 200) $response->setStatus(302);

            return $response;
        }

        $page = clone $model;
        if ($page->template === '') $page->setOption('template', $route->template);

        $variables = [
            'siteName' => 'Inane Framework',
            'title'    => $route->routeProperty('title', $route->params) ?: $route->routeProperty('label', $route->params),
            'notice'   => $notice,
        ];
        $root = new HttpModel($variables, ['template' => $this->layout]);
        if ($page->useLayout) {
            $header = new HttpModel(options: ['template' => 'partials/header']);
            $header->addChild('navigation', new IterativeModel(items: $this->navigation($router, $route), options: ['template' => 'components/link']));
            $root->addChild('header', $header);
            $root->addChild('content', $page);
            $root->addChild('footer', new HttpModel(options: ['template' => 'partials/footer']));
        } else {
            $root = $page;
        }

        if (!$response->hasHeader('Content-Type')) $response->addHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->setBody($this->manager->render($root));

        return $response;
    }

    /**
     * Builds concrete links for the controller's routes.
     *
     * @param Router     $router Site router.
     * @param RouteMatch $route  Active route.
     *
     * @return list<array{url: string, label: string, active: bool}>
     *
     * @throws Throwable If a route cannot be resolved.
     */
    private function navigation(Router $router, RouteMatch $route): array {
        $links = [];
        $pages = [
            'home'         => [],
            'item'         => [
                ['item' => 'example'],
                ['item' => 'tiger'],
                ['item' => 'panther'],
            ],
            'download'     => [],
            'download-qsp' => [],
            'session'      => [],
            'logout'       => [],
            'new'          => [],
            'login'        => ['username' => 'philip'],
        ];

        if (UserSession::has('uid')) unset($pages['login'], $pages['new']);
        else unset($pages['logout']);

        foreach($pages as $name => $params) {
            if (empty($params) || !array_is_list($params)) $params = [$params];
            foreach($params as $param) {
                $links[] = [
                    'url'    => $router->url($name, $param),
                    'label'  => $router->routeProperty($name, 'label', $param),
                    'active' => $route->route->getName() === $name,
                ];
            }
        }

        return $links;
    }
}
