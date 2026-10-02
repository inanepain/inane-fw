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

use Attribute;

/**
 * Class RequestAttribute
*
* Represents an attribute associated with an HTTP request.
* Used for storing and retrieving custom data within the request lifecycle.
*/
#[Attribute(Attribute::TARGET_CLASS)]
class RequestAwareAttribute {

}
