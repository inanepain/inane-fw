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
	'appId' => 'inane-fw',
	'config' => [
		'glob_pattern' => realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php',
		'allow_modifications' => false,
	],
	'view'   => [
		'path'   => 'View',
		// 'layout' => 'layout/layout',
		'layout' => 'layout/lo2',
	],
	'router' => [
		'splitQuerystring' => true,
		'controller' => [
			'glob' => 'src/*/*Controller.php',
			'glob_ignore' => '/(Abstract)/',
			'default' => [
				\Dev\Controller\Test::class,
			],
		],
	],
	/**
	 * Default services
	 */
	'services' => [],
	/**
	 * Server
	 */
	'server' => [
		'host' => '0.0.0.0',
		'port' => 9502,
	],
	'components' => [],
];
