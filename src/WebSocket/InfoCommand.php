<?php
/*
 * *
 *  * Inane: PROJECT
 *  *
 *  * PROJECT_DESCRIPTION
 *  *
 *  * $Id$
 *  * $Date$
 *  *
 *  * PHP version 8.5
 *  *
 *  * @author   Philip Michael Raab <philip@cathedral.co.za>
 *  * @package  inanepain\PROJECT
 *  * @category PROJECT
 *  *
 *  * @license  UNLICENSE
 *  * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *  *
 *  * _version_ $version
 *
 */

namespace Knot\WebSocket;

use Inane\Rebecca\{
    Client,
    Command\Command,
    WebSocketServer};
use Inane\Stdlib\{
    Array\OptionsInterface,
    Exception\JsonException,
    Json,
    Options};
use Swoole\WebSocket\Server;

/**
 * Example: Info Command (Rank 0 - Available to all)
 */
class InfoCommand extends Command {
//#region Properties
    protected array $defaults = ['fd' => null];
//#endregion Properties

    /**
     * Constructs an instance of the class.
     *
     * This method initializes a new object by calling the constructor of its parent class with
     * default parameters (if any) and sets up the initial state as per subclass logic. It may also
     * perform additional initialization specific to this object's context or set properties if needed.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieves a constant name associated with an object instance as a string value. This method is typically used to collect
     * metadata or identification information about the current object's context in which it operates.
     *
     * @return string The retrieved name, represented by its corresponding identifier for this specific
     *             context of operation within the application domain.
     */
    public function getName(): string {
        return 'info';
    }

    /**
     * Retrieves a description of an entity as provided by its associated client's data source. This method is designed to fetch and format textual representation that summarizes key attributes or characteristics related to
     * specific details about clients based on the context it operates within.
     *
     * @return string A descriptive text representing client information obtained from the relevant sources
     */
    public function getDescription(): string {
        return 'Returns client information';
    }

    /**
     * Executes the command processing routine for a server-client interaction.
     *
     * This method is responsible for handling incoming data, initializing the necessary resources
     * based on provided arguments and executing predefined tasks. It uses various components such as
     * Server instances to manage connections or handle specific client requests through WebSocketServer routines.
     *
     * The execution involves setting up default options if none are supplied.
     * It then iterates over server connections to establish clients for each connection, handles additional data
     * passed via the 'data' parameter and finally pushes processed information back onto the server as a response.
     *
     * @param Server                      $server An instance of the Server class that manages active connections.
     * @param Client                      $client A client object representing an individual user's session or interaction with the server.
     * @param null|array|OptionsInterface $data   Optional data passed to customize options. If not provided, defaults will be used; if it's
     *                                            a non-OptionsInterface instance,
     *                                            it is converted into Options before proceeding.
     *
     * @return void This method does not return any value as its primary purpose is to process and handle tasks rather than
     *                  producing output directly returned from the function call itself. It may modify server state or client
     *                  session data accordingly by pushing responses back onto the Server instance managed connections.
     * @throws JsonException
     */
    public function execute(Server $server, Client $client, null|array|OptionsInterface $data = null): void {
        if (! $data instanceof OptionsInterface)
            $data = new Options($data);

        $data->complete($this->defaults);
        $info = $client;

        //        WebSocketServer::init()
        foreach($server->connections as $fd) {
            $cl = WebSocketServer::init()
                ->getClient($fd)
            ;
            //            var_dump($cl);
        }

        if ($data->fd) {
            $info = WebSocketServer::init()
                ->getClient($data->fd)
            ;
        }

        $server->push($client->fd, Json::encode([
            'command' => 'info',
            'data'    => [
                'fd'           => $info->fd,
                'rank'         => $info->rank,
                'connected_at' => $info->connectedAt,
                'uptime'       => microtime(true) - $info->connectedAt,
            ],
            'result'  => $client->data,
        ]));
    }
}
