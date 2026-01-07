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
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package playground\develop
 * @category develop
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

return [
	'db'   => [
		// 'dsn'   => 'mysql:host=127.0.0.1;dbname=myDB',
		// 'dsn'   => "sqlite:data/develop.db",
		'driver' => 'sqlite',
		'dbname' => 'data/develop.db',
		// 'host' => '127.0.0.1',
		// 'port' => '3306',
		// 'username' => 'peep@cathedral.co.za',
		// 'password' => 'password'
	],
];
