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

return [
	'db'   => [
		// 'dsn'   => 'mysql:host=127.0.0.1;dbname=myDB',
		// 'dsn'   => "sqlite:data/develop.db",
		'driver' => 'sqlite',
//		'dbname' => 'data/develop.db',
		'dbname' => 'data/inane-fw.sqlite',
		// 'host' => '127.0.0.1',
		// 'port' => '3306',
		// 'username' => 'peep@cathedral.co.za',
		// 'password' => 'password'
	],
];
