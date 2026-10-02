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

use Inane\Db\Adapter\Adapter;
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
use PDO;

use function is_file;

class MainController extends AbstractController {
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
        $identity = null;
        if (UserSession::has('uid')) {
            $identity = UserSession::get('identity');
        }

        return new HttpModel([
            'developer' => $identity?->name ?: '',
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

        if (!UserSession::has('role')) {
            // Store user data
            UserSession::set('role', 'admin');

            // Flash message
            UserSession::flash('notice', 'User role set!');
        }

        return new HttpModel(['role' => (string)UserSession::get('role', '')]);
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
        if (UserSession::has('identity')) return $v;

        if (!$this->config->get('db')) return new HttpModel(
            ['username' => (string)$params['username'], 'message' => 'Configure a database to use demo login.'],
            ['status' => 503],
        );

        // Login creates a cookie for session remember me
        UsersTable::$db = $this->serviceManager->get(Adapter::class);
        $ut = new UsersTable();
        $query = $ut::$db->getDriver()->prepare('SELECT * FROM users WHERE username = :username');
        $query->execute(['username' => (string)$params['username']]);
        $query->setFetchMode(PDO::FETCH_CLASS, User::class, [null, $ut]);
        $u = $query->fetch();

        if ($u) {
            UserSession::set('uid', $u->id);
            UserSession::set('identity', $u);
            SessionManager::enableRememberMe();

            UserSession::flash('notice', "Welcome {$u->name}!");

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

        return Web::getInstance()->httpClient->sendRequest($request);
    }
}
