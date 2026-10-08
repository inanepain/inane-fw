<?php

/**
 * Framework
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\fw
 * @category fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Knot\Application;

use Attribute;

/**
 * Class RequestAttribute
 *
 * Represents an attribute associated with an HTTP request.
 * Used for storing and retrieving custom data within the request lifecycle.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RequestAwareAttribute {}
