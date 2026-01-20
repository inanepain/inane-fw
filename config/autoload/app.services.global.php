<?php

/**
 * Skeleton: Inane-FW
 *
 * Web or console application framework using the inanepain libraries.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package skeleton\inane-fw
 * @category inane-fw
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

use Dev\Db\Table\DivinationsTable;
use Dev\Db\Table\FortunesTable;
use Inane\Datetime\Unit\Seconds;
use Inane\Db\Adapter\Adapter;
use Inane\IdForge\Generator\ULIDGenerator;
use Inane\IdForge\IdGeneratorFactory;

/**
 * ServiceManager configuration.
 *
 * Mainly the definitions for a vast majority of the services.
 */
return [
	'services' => [
		Redis::class => function (\Inane\Services\ServiceManager $sm) {
			$redis = new Redis();
			$redis->connect(...$sm->getConfig()->redis->connection);

			if ($redis->serverName() === false && $sm->getConfig()->redis->auth) $redis->auth($sm->getConfig()->redis->auth->values()->toArray());

			if ($sm->getConfig()->redis->db) $redis->select($sm->getConfig()->redis->db);

			$redis->lPush('last-login', time());
			$redis->ltrim('last-login', 0, 4);

			$redis->psetex('session', Seconds::seconds(10)->milliseconds, random_bytes(8));

			return $redis;
		},
        Adapter::class => function (\Inane\Services\ServiceManager $sm) {
            return new Adapter($sm->getConfig()->get('db'));
        },
        ULIDGenerator::class => static function (\Inane\Services\ServiceManager $sm) {
            return IdGeneratorFactory::createULID();
        }
	],
];
