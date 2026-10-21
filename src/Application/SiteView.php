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
use Inane\View\HtmlView;
use Inane\View\IterativeView;
use Inane\View\Model\HtmlModel;
use Inane\View\Model\ViewModelInterface;
use Inane\View\ViewManager;
use InvalidArgumentException;
use Knot\Session\UserSession;
use RuntimeException;
use Throwable;
use TypeError;

/**
 * Composes controller pages into the site's nested layout.
 */
final class SiteView {
    /**
     * Checks and retrieves the session status, initializing it if not already set.
     *
     * @return bool True if the session has a valid user ID, false otherwise.
     *
     * @throws RuntimeException If the UserSession::has method encounters any errors.
     */
    protected bool $isSession {
        get => $this->isSession ??= UserSession::has('uid');
    }

    /**
     * Configures the renderer and layout template.
     *
     * @param ViewManager $manager Nested model renderer.
     * @param string      $layout  Layout template.
     *
     * @throws TypeError
     */
    public function __construct(private ViewManager $manager, private string $layout) {

    }

    /**
     * Renders a page whilst retaining its HTTP options and children.
     *
     * @param ViewModelInterface $model Controller response model.
     * @param RouteMatch $route  Matched controller route.
     * @param Router     $router Router used to build navigation links.
     * @param string     $notice Flash notice.
     *
     * @return Response
     *
     * @throws Throwable If URL generation or template rendering fails.
     */
    public function render(ViewModelInterface $model, RouteMatch $route, Router $router, string $notice = ''): Response {
        $response = new Response(body: '', status: $model->status, headers: $model->headers);
        if ($response->hasHeader('Location')) {
            if ($response->getStatusCode() === 200) $response->setStatus(302);

            return $response;
        }

        if (!$model instanceof HtmlModel) {
            $view = $this->manager->createView($model);
            if (!$response->hasHeader('Content-Type')) $response->addHeader('Content-Type', $view->contentType());
            return $response->setBody($view->render());
        }

        $page = clone $model;
        if ($page->useLayout) {
            if ($page->template === '') $page->setOption('template', $route->template);

            $variables = [
                'siteName' => 'Inane Framework',
                'title'    => $route->routeProperty('title', $route->params) ?: $route->routeProperty('label', $route->params),
                'notice'   => $notice,
            ];

            if ($response->getRequest()
                ->isHTTPS()) {
                $variables['toggleIcon'] = '🔐';
                $variables['toggleTitle'] = 'Secure';
                $variables['toggleUrl'] = $response->getRequest()
                    ->getUri()
                    ->withScheme('http')
                ;
            } else {
                $variables['toggleIcon'] = '🔓';
                $variables['toggleTitle'] = 'Unsecure';
                $variables['toggleUrl'] = $response->getRequest()
                    ->getUri()
                    ->withScheme('https')
                ;
            }

            $root = $this->manager->createView(new HtmlModel($variables, ['template' => $this->layout]));
            $header = $this->manager->createView(new HtmlModel(options: ['template' => 'partials/header']));
            if (!$root instanceof HtmlView || !$header instanceof HtmlView) throw new RuntimeException('HTML models must resolve to HTML views.');
            $header->addChild('navigation', new IterativeView(
                $this->manager->renderer ?? throw new RuntimeException('HTML rendering requires a PHP renderer.'),
                new HtmlModel(options: ['template' => 'components/link']),
                $this->navigation($router, $route),
            ));
            $root->addChild('header', $header);
            $root->addChild('content', $this->manager->createView($page));
            $root->addChild('footer', $this->manager->createView(new HtmlModel(options: ['template' => 'partials/footer'])));
        } else {
            $root = $this->manager->createView($page);
        }

        if (!$response->hasHeader('Content-Type')) $response->addHeader('Content-Type', 'text/html; charset=UTF-8');
        $view = $root instanceof HtmlView ? $root : throw new RuntimeException('HTML models must resolve to HTML views.');
        if (!$response->hasHeader('Content-Type')) $response->addHeader('Content-Type', $view->contentType());
        $response->setBody($view->render());

        return $response;
    }

    /**
     * Recursively renders a menu structure from the provided pages and constructs menu items.
     *
     * @param array   $pages     An array of pages, each containing route and session information.
     * @param Router  $router    The router instance used for generating URLs and accessing route properties.
     * @param array  &$menuItems Reference to the resulting array where computed menu items are added.
     *
     * @return array The completed array of menu items with URLs, labels, and active statuses.
     *
     * @throws InvalidArgumentException|\Inane\Routing\Exception\InvalidArgumentException If a specified route name in the pages array is invalid or cannot be resolved.
     */
    protected function renderMenu(array $pages, Router $router, array &$menuItems = []): array {
        foreach($pages as $page) {
            if ($page['session'] === null || $page['session'] === $this->isSession) {
                if (!empty($children = $page['children'] ?? [])) {
                    $this->renderMenu($children, $router, $menuItems);
                } else {
                    $routeItem = $router->getRoute($page['route']);
                    if ($routeItem === null) {
                        throw new InvalidArgumentException("Invalid route name: {$page['route']}");
                    }

                    $menuItems[] = [
                        'url'    => $routeItem->getURL($page['params']),
                        'label'  => $routeItem->property('label', $page['params']),
                        'active' => $routeItem->active && $routeItem->hasParams() && Web::instance()->routeMatch->params === $page['params'],
                    ];
                }
            }
        }

        return $menuItems;
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
        $site = [
            [
                'name'    => 'Home',
                'route'   => 'home',
                'params'  => [],
                'session' => null,
            ],
            [
                'name'    => 'Download',
                'route'   => 'download',
                'params'  => [],
                'session' => true,
            ],
            [
                'name'    => 'Download QSP',
                'route'   => 'download-qsp',
                'params'  => [],
                'session' => true,
            ],
            [
                'name'     => 'View',
                'route'    => 'view',
                'params'   => [],
                'children' => [
                    [
                        'name'    => 'Example',
                        'route'   => 'view.item',
                        'params'  => ['item' => 'example'],
                        'session' => true,
                    ],
                    [
                        'name'    => 'Tiger',
                        'route'   => 'view.item',
                        'params'  => ['item' => 'tiger'],
                        'session' => true,
                    ],
                    [
                        'name'    => 'Panther',
                        'route'   => 'view.item',
                        'params'  => ['item' => 'panther'],
                        'session' => true,
                    ],
                ],
                'session'  => true,
            ],
            [
                'name'    => 'Logout',
                'route'   => 'logout',
                'params'  => [],
                'session' => true,
            ],
            [
                'name'    => 'New User',
                'route'   => 'new',
                'params'  => [],
                'session' => false,
            ],
            [
                'name'    => 'Login',
                'route'   => 'login',
                'params'  => ['username' => 'philip'],
                'session' => false,
            ],
        ];
        $links = [];

        return $this->renderMenu($site, $router, $links);
    }
}
