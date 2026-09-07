<?php

/**
 * Font
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\font
 * @category font
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Knot\Font;

use Inane\Stdlib\Converters\Arrayable;
use Inane\Stdlib\Exception\JsonException;
use Inane\Stdlib\Options;
use InvalidArgumentException;

use function is_string;

/**
 * Font
 *
 * inane-fw
 *
 * @version 0.1.0
 */
class Font implements Arrayable {
    /**
     * Font name
     */
    protected(set) string $name;

    /**
     * Font version
     */
    protected(set) string $version;

    /**
     * Platforms the font is available on
     */
    protected(set) Options $platforms;

    /**
     * Font weights
     */
    protected(set) Options $weights;

    /**
     * Constructor for initialising the class with name, version, platforms, and weights.
     *
     * @param string             $name      The name of the instance.
     * @param string             $version   The version of the instance.
     * @param null|array|Options $platforms The platforms configuration, can be null, array, or an instance of Options.
     * @param null|array|Options $weights   The weights configuration, can be null, array, or an instance of Options.
     *
     * @return void
     *
     * @throws InvalidArgumentException|JsonException If the provided parameters are not valid.
     */
    public function __construct(string $name, string $version, null|array|Options $platforms = null, null|array|Options $weights = null) {
        $this->name = $name;
        $this->version = $version;
        $this->platforms = new Options($platforms);
        $this->weights = new Options($weights);

        $this->bootstrap();
        $this->initialise();
    }

    /**
     * Create the required dependencies
     *
     * @return void
     */
    protected function bootstrap(): void {
    }

    /**
     * Post-bootstrap configuration
     *
     * @return void
     */
    protected function initialise(): void {

    }

    /**
     * Add one or more weights to the font
     *
     * @param string|array $weight weight or weights to add
     *
     * @return self for chaining
     *
     * @throws JsonException
     */
    public function addWeight(string|array $weight): self {
        // a single weight is wrapped so both signatures merge the same way
        if (is_string($weight)) $weight = [$weight];
        // duplicates are dropped, a weight is only listed once
        $this->weights->merge($weight)->unique();
        return $this;
    }

    /**
     * Return Array representation of data
     *
     * @return array as Array
     */
    public function toArray(): array {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'platforms' => $this->platforms->toArray(),
            'weights' => $this->weights->toArray(),
        ];
    }
}
