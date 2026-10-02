<?php
declare(strict_types=1);

namespace Knot\Application;

use Inane\Http\Response;
use Inane\Routing\RouteMatch;
use Inane\Routing\Router;
use Inane\View\Model\HttpModel;
use Inane\View\Model\IterativeModel;
use Inane\View\ViewManager;

/**
 * Composes controller pages into the site's nested layout.
 */
final readonly class SiteView {
    /**
     * Configures the renderer and layout template.
     *
     * @param ViewManager $manager Nested model renderer.
     * @param string $layout Layout template.
     *
     * @throws \TypeError
     */
    public function __construct(private ViewManager $manager, private string $layout) {}

    /**
     * Renders a page whilst retaining its HTTP options and children.
     *
     * @param HttpModel $model Controller page model.
     * @param RouteMatch $route Matched controller route.
     * @param Router $router Router used to build navigation links.
     * @param string $notice Flash notice.
     *
     * @return Response
     *
     * @throws \Throwable If URL generation or template rendering fails.
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
            'title' => $route->routeProperty('title', $route->params) ?: $route->routeProperty('label', $route->params),
            'notice' => $notice,
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
     * @param Router $router Site router.
     * @param RouteMatch $route Active route.
     *
     * @return list<array{url: string, label: string, active: bool}>
     *
     * @throws \Throwable If a route cannot be resolved.
     */
    private function navigation(Router $router, RouteMatch $route): array {
        $links = [];
        foreach ([
            'home' => [],
            'item' => ['item' => 'example'],
            'session' => [],
            'login' => ['username' => 'demo'],
            'logout' => [],
            'download' => [],
            'download-qsp' => [],
            'new' => [],
        ] as $name => $params) {
            $links[] = [
                'url' => $router->url($name, $params),
                'label' => $router->routeProperty($name, 'label', $params),
                'active' => $route->route->getName() === $name,
            ];
        }

        return $links;
    }
}