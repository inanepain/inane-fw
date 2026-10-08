<?php

/**
 * Playground: develop
 *
 * Rough environment for testing, developing and playing around with PHP odds and ends.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
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

namespace Knot\Web;

use Inane\Http\{
    Request,
    Response,
    Stream};
use Inane\QR\QRObject;
use Inane\Routing\Route;
use Inane\Session\SessionManager;
use Inane\Stdlib\Exception\RuntimeException;
use Inane\View\Model\HttpModel;
use Knot\Application\AbstractController;
use Knot\Application\Web;
use Knot\Db\Entity\User;
use Knot\Db\Table\UsersTable;
use Knot\Session\UserSession;

use function array_first;
use function dirname;
use function implode;
use function is_file;
use function max;
use function preg_match;
use function rawurlencode;

class MainController extends AbstractController {
    /**
     * Initialises the necessary settings or configurations required for the method's operation.
     *
     * Override this method in child classes to initialise the controller with custom settings.
     *
     * @return void
     */
    protected function initialise(): void {
//        UsersTable::$db = $this->serviceManager->get(Adapter::class);
    }

    protected function getIdentity(): ?User {
        return UserSession::has('uid') ? $this->serviceManager->get(UsersTable::class)->fetch(UserSession::get('uid')) : null;
    }

    /**
     * Shows the welcome page.
     *
     * @return HttpModel
     *
     * @throws RuntimeException
     */
    #[Route(path: '/', name: 'home', extra: [
        'label' => 'Welcome',
        'title' => 'Home Page',
        'class' => 'text-red button button-grey'
    ])]
    public function home(): HttpModel {
        return new HttpModel([
            'developer' => $this->getIdentity()?->name ?: '',
        ]);
    }

    /**
     * Shows an item and its QR code.
     *
     * @param array<string, mixed> $params Arbitrary matched route parameters.
     *
     * @return HttpModel
     *
     * @throws \Throwable If QR generation fails.
     */
    #[Route(path: '/view/{item}', name: 'item', extra: [
        'label' => 'Item: {item}',
        'class' => 'text-purple'
    ])]
    public function viewTask(array $params): HttpModel {
        $item = (string)$params['item'];
        $params['image'] = preg_match('/\A[a-zA-Z0-9_-]+\z/', $item) === 1
        && is_file(dirname(__DIR__, 2) . '/public/img/' . $item . '.png')
            ? '/img/' . rawurlencode($item) . '.png' : '';
        $params['qrcode'] = new QRObject($item)->getImageBase64();

        return new HttpModel($params);
    }

    /**
     * Handles session-related tasks, including setting user roles and flash messages.
     *
     * @param array<string, mixed> $params Arbitrary matched route parameters.
     *
     * @return HttpModel
     *
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Route(path: '/session', name: 'session', extra: [
        'label' => 'Session',
        'title' => 'Session'
    ])]
    public function sessionTask(array $params): HttpModel {
        if ($u = $this->getIdentity()) {
            // Flash message
            UserSession::flash('notice', 'User groups set!');
        }

        return new HttpModel(['admin' => $u?->isAdmin ?? false, 'groups' => implode(', ', $u?->groups ?? []), 'email' => $u?->email ?? '']);
    }

    /**
     * Looks up a demo user and redirects after login.
     *
     * @param array<string, mixed> $params Arbitrary matched route parameters.
     *
     * @return HttpModel|Response
     *
     * @throws \Throwable If the database or session operation fails.
     */

    #[Route(path: '/login/{username}', name: 'login', extra: [
        'label' => 'Login',
        'title' => 'Login'
    ])]
    public function loginTask(array $params): HttpModel|Response {
        $v = new Response(body: '', status: 302, headers: ['Location' => '/']);
        if (UserSession::has('uid')) return $v;

        if (!$this->config->get('db')) return new HttpModel(
            ['username' => (string)$params['username'], 'message' => 'Configure a database to use demo login.'],
            ['status' => 503],
        );

        // Login creates a cookie for session remember me
        if ($u = $this->serviceManager->get(UsersTable::class)->find(['username', $params['username']])) {
            $u = array_first($u);
            UserSession::set('uid', $u->id);
            SessionManager::enableRememberMe();

            UserSession::flash('notice', "Welcome $u->name!");

            return $v;
        }

        return new HttpModel(['username' => (string)$params['username'], 'message' => 'No matching demo user was found.'], ['status' => 404]);
    }

    /**
     * Ends the current session.
     *
     * @param array<string, mixed> $params Arbitrary matched route parameters.
     *
     * @return HttpModel
     *
     * @throws RuntimeException
     */

    #[Route(path: '/logout', name: 'logout', extra: [
        'label' => 'Logout',
        'title' => 'Logout'
    ])]
    public function logoutTask(array $params): HttpModel {
        // Logout clears everything (including persistent cookie)
        SessionManager::destroy();

        return new HttpModel();
    }

    /**
     * Returns the macOS download without an HTML layout.
     *
     * @param array<string, mixed> $params Arbitrary matched route parameters.
     *
     * @return Response
     *
     * @throws \Throwable If file preparation fails.
     */

    #[Route(path: '/store/osx', name: 'download', extra: [
        'label' => 'Download',
        'title' => 'OSX version'
    ])]
    public function downloadTask(array $params): Response {
        $limit = max(0, (int)($params['query-string']['limit'] ?? 0));

        return $this->response->setFile('filesrv/some-file.dmg', true, $limit);
    }

    /**
     * Returns the QSP download without an HTML layout.
     *
     * @param array<string, mixed> $params Arbitrary matched route parameters.
     *
     * @return Response
     *
     * @throws \Throwable If file preparation fails.
     */

    #[Route(path: '/store/qsp', name: 'download-qsp', extra: [
        'label' => 'Download QSP',
        'title' => 'QSP'
    ])]
    public function qspTask(array $params): Response {
        $limit = max(0, (int)($params['query-string']['limit'] ?? 0));

        return $this->response->setFile('filesrv/qsp.dmg', true, $limit);
    }

    /**
     * Returns the upstream user-creation response unchanged.
     *
     * @param array<string, mixed> $params Arbitrary matched route parameters.
     *
     * @return Response
     *
     * @throws \Throwable If the upstream request fails.
     */

    #[Route(path: '/request/create', name: 'new', extra: [
        'label' => 'New User',
        'title' => 'Some One'
    ])]
    public function newuserTask(array $params): Response {
        $body = '{"name":"Some One","email":"some@one.com","group":"users"}';

        $request = Request::fromUrl('http://blackbetty.local/api/user', []);
        $request = $request->withMethod('POST');
        $request = $request->withoutHeader('host');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody(new Stream($body));

        return Web::instance()->httpClient->sendRequest($request);
    }
}
