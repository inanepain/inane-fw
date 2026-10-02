<?php
declare(strict_types=1);

namespace Knot\Tests\Application;

use Inane\Http\Request;
use Inane\Routing\RouteMatch;
use Inane\Routing\Router;
use Inane\View\Model\HttpModel;
use Inane\View\Renderer\PhpRenderer;
use Inane\View\ViewManager;
use Knot\Application\SiteView;
use Knot\Web\MainController;
use PHPUnit\Framework\TestCase;

/**
 * Covers the site's nested view tree and HTTP metadata.
 */
final class SiteViewTest extends TestCase {
    /**
     * Renders the shared layout and escapes page and flash values.
     *
     * @throws \Throwable
     */
    public function testNestedHome(): void {
        $router = new Router([MainController::class]);
        $route = $this->match($router, '/');
        $model = new HttpModel(['developer' => '<script>bad</script>']);
        $view = $this->view();
        $response = $view->render(model: $model, route: $route, router: $router, notice: '<b>Welcome</b>');
        $html = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/html; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        self::assertStringContainsString('<!DOCTYPE html>', $html);
        self::assertStringContainsString('<header', $html);
        self::assertStringContainsString('<nav', $html);
        self::assertStringContainsString('<footer', $html);
        self::assertStringContainsString('aria-current="page"', $html);
        self::assertStringContainsString('href="/view/example"', $html);
        self::assertStringContainsString('href="/login/demo"', $html);
        self::assertStringContainsString('&lt;script&gt;bad&lt;/script&gt;', $html);
        self::assertStringContainsString('&lt;b&gt;Welcome&lt;/b&gt;', $html);
        self::assertStringNotContainsString('<script>bad</script>', $html);
        self::assertSame(['developer' => '<script>bad</script>'], $model->variables);
        self::assertSame($html, (string)$view->render(model: $model, route: $route, router: $router, notice: '<b>Welcome</b>')->getBody());
    }

    /**
     * Uses route-derived templates for item, session and logout pages.
     *
     * @throws \Throwable
     */
    public function testRoutePages(): void {
        $router = new Router([MainController::class]);
        foreach (['/view/example', '/session', '/logout'] as $path) {
            $route = $this->match($router, $path);
            $response = $this->view()->render(new HttpModel($route->params), $route, $router);
            self::assertStringContainsString('<main', (string)$response->getBody());
            self::assertStringContainsString('<h2>', (string)$response->getBody());
        }
    }

    /**
     * Renders the controller's QR data URI as a valid PNG source.
     *
     * @throws \Throwable
     */
    public function testItemQrImage(): void {
        $router = new Router([MainController::class]);
        $route = $this->match($router, '/view/example');
        $controller = new \ReflectionClass(MainController::class)->newInstanceWithoutConstructor();
        $response = $this->view()->render($controller->viewTask($route->params), $route, $router);
        self::assertSame(1, preg_match('/src="data:image\/png;base64,([A-Za-z0-9+\/=]+)"/', (string)$response->getBody(), $matches));
        self::assertStringStartsWith("\x89PNG\r\n\x1a\n", base64_decode($matches[1], true));
    }

    /**
     * Retains explicit status codes and headers whilst rendering HTML.
     *
     * @throws \Throwable
     */
    public function testResponseMetadata(): void {
        $router = new Router([MainController::class]);
        $model = new HttpModel(options: ['status' => 404, 'headers' => ['X-Test' => 'retained']]);
        $response = $this->view()->render($model, $this->match($router, '/'), $router);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('retained', $response->getHeaderLine('X-Test'));
        self::assertStringContainsString('<!DOCTYPE html>', (string)$response->getBody());
    }

    /**
     * Preserves options and named children for a standalone page.
     *
     * @throws \Throwable
     */
    public function testStandaloneModel(): void {
        $router = new Router([MainController::class]);
        $model = new HttpModel(options: ['template' => 'partials/header', 'useLayout' => false, 'headers' => ['X-Test' => 'retained']]);
        $model->addChild('navigation', new HttpModel(options: ['template' => 'partials/footer']));
        $response = $this->view()->render($model, $this->match($router, '/'), $router);

        self::assertStringContainsString('<header', (string)$response->getBody());
        self::assertStringContainsString('<footer', (string)$response->getBody());
        self::assertStringNotContainsString('<!DOCTYPE html>', (string)$response->getBody());
        self::assertSame('retained', $response->getHeaderLine('X-Test'));
    }

    /**
     * Redirects without resolving a template or consuming view content.
     *
     * @throws \Throwable
     */
    public function testRedirect(): void {
        $router = new Router([MainController::class]);
        $model = new HttpModel(options: ['template' => 'missing', 'headers' => ['Location' => '/'], 'useLayout' => false]);
        $response = $this->view()->render($model, $this->match($router, '/login/demo'), $router);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/', $response->getHeaderLine('Location'));
        self::assertSame('', (string)$response->getBody());
    }

    /**
     * Creates the configured site renderer.
     *
     * @return SiteView
     *
     * @throws \Throwable
     */
    private function view(): SiteView {
        return new SiteView(new ViewManager(new PhpRenderer(dirname(__DIR__, 2) . '/views')), 'layouts/site');
    }

    /**
     * Matches a controller route.
     *
     * @param Router $router Site router.
     * @param string $path Request path.
     *
     * @return RouteMatch
     *
     * @throws \Throwable
     */
    private function match(Router $router, string $path): RouteMatch {
        $route = $router->match(new Request(method: 'GET', uri: 'http://localhost' . $path));
        self::assertNotNull($route);

        return $route;
    }
}