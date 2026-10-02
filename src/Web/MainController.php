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
    Stream};
use Inane\QR\QRObject;
use Inane\Routing\Route;
use Inane\Session\SessionManager;
use Inane\Stdlib\Exception\RuntimeException;
use Knot\Application\{
    AbstractController,
    ModelInterface,
    ViewModel,
    Web};
use Knot\Db\Entity\User;
use Knot\Db\Table\UsersTable;
use Knot\Session\UserSession;
use PDO;

use function file_exists;

class MainController extends AbstractController {
    #[Route(path: '/', name: 'home', extra: [
        'label' => 'Welcome',
        'title' => 'Home Page',
        'class' => 'text-red button button-grey'
    ])]
    public function home(): array|ModelInterface {
        $identity = null;
        if (UserSession::has('uid')) {
            $identity = UserSession::get('identity');
        }

        return new ViewModel([
            'developer' => $identity?->name ?: '',
        ]);
    }

    #[Route(path: '/view/{item}', name: 'item', extra: [
        'label' => 'Item: {item}',
        'class' => 'text-purple'
    ])]
    public function viewTask(array $params): array|ModelInterface {
        $b64 = new \Inane\Auth\TwoFactor\Token(token: 'TRIuzh6BCcWSNDQq', name: 'granny-git')->getImageBase64();

        if (file_exists('public/img/' . $params['item'] . '.png')) {
            $img = '<img width="300" src="/img/' . $params['item'] . '.png" alt="' . $params['item'] . '"/>';
        } else {
            $img = '';
        }

        $qrText = 'WIFI:S:Supersonic_WiFi_5G;P:yYTeheFY;T:WPA;;';
        $qr = new QRObject($qrText);

        $params['img'] = $img;
        $params['qrcode'] = $b64;
        $params['qrwifi'] = $qr->getImageBase64();

        return new ViewModel($params);
    }

    /**
     * Handles session-related tasks, including setting user roles and flash messages.
     *
     * @param array $params An array of parameters relevant to the session task.
     *
     * @return array|ModelInterface Returns an array or a model interface based on the operation result.
     *
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    #[Route(path: '/session', name: 'session', extra: [
        'label' => 'Session',
        'title' => 'Session'
    ])]
    public function sessionTask(array $params): array|ModelInterface {
        //        dd($_SESSION);

        if (!UserSession::has('role')) {
            // Store user data
            UserSession::set('role', 'admin');

            // Flash message
            UserSession::flash('notice', 'User role set!');
        }

        return [];
    }

    #[Route(path: '/login/{username}', name: 'login', extra: [
        'label' => 'Login',
        'title' => 'Login'
    ])]
    public function loginTask(array $params): array|ModelInterface {
        $v = new ViewModel(options: ['headers' => ['Location' => '/']]);
        $v->setOptions(['terminate' => true]);
        $v->template = 'Main/home';
        if (UserSession::has('identity')) return $v;

        // Login creates a cookie for session remember me
        $ut = new UsersTable();
        $query = $ut->queryBuilder()
            ->select('users')
            ->where('username', $params['username'])
        ;
        $u = $ut::$db->getDriver()
            ->query((string)$query, PDO::FETCH_CLASS, User::class, [
                null,
                $ut
            ])
            ->fetch()
        ;

        if ($u) {
            UserSession::set('uid', $u->id);
            UserSession::set('identity', $u);
            SessionManager::enableRememberMe();

            UserSession::flash('notice', "Welcome {$u->name}!");

            return $v;
        }

        return $v;
    }

    #[Route(path: '/logout', name: 'logout', extra: [
        'label' => 'Logout',
        'title' => 'Logout'
    ])]
    public function logoutTask(array $params): array|ModelInterface {
        // Logout clears everything (including persistent cookie)
        SessionManager::destroy();

        return [];
    }

    #[Route(path: '/store/osx', name: 'download', extra: [
        'label' => 'Download',
        'title' => 'OSX version'
    ])]
    public function downloadTask(array $params): array|ModelInterface {
        $limit = 0;
        if (($qs = $params['query-string']) && $limit = $qs['limit']) $limit = (int)$limit;

        //        $response = Application::getInstance()->response;
        //        $response->setFile('filesrv/some-file.dmg', true, $limit);
        $this->response->setFile('filesrv/some-file.dmg', true, $limit);

        Web::getInstance()->httpClient->send($this->response);

        return [];
    }

    #[Route(path: '/store/qsp', name: 'download-qsp', extra: [
        'label' => 'Download QSP',
        'title' => 'QSP'
    ])]
    public function qspTask(array $params): array|ModelInterface {
        $limit = 0;
        if (($qs = $params['query-string']) && $limit = $qs['limit']) $limit = (int)$limit;

        $response = Web::getInstance()->response;
        $response->setFile('filesrv/qsp.dmg', true, $limit);

        Web::getInstance()->httpClient->send($response);

        return [];
    }

    #[Route(path: '/request/create', name: 'new', extra: [
        'label' => 'New User',
        'title' => 'Some One'
    ])]
    public function newuserTask(array $params): array|ModelInterface {
        $body = '{"name":"Some One","email":"some@one.com","group":"users"}';

        $request = Request::fromUrl('http://blackbetty.local/api/user', []);
        // $request = Request::fromUrl('https://www.cathedral.co.za/api/user', []);
        $request = $request->withMethod('POST');
        $request = $request->withoutHeader('host');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody(new Stream($body));

        \Inane\Dumper\Dumper::$enabled = false;

        $response = Web::getInstance()->httpClient->sendRequest($request);
        Web::getInstance()->httpClient->send($response);

        return [];
    }

    /**
     * @return void
     */
    protected function initialise() {
        parent::initialise(); // TODO: Change the autogenerated stub
    }
}
