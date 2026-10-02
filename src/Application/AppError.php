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

namespace Knot\Application;

/**
 * AppError
 *
 * @version 1.0.0
 */
enum AppError: int {
	case InvalidRoute = 400;

	/**
	 * Get the user-friendly message
	 *
	 * @return string Error Message
	 */
	public function message(): string {
		return match ($this) {
			self::InvalidRoute => 'Invalid Route: not found.',
		};
	}
}
