<?php

/**
 * inane-fw
 *
 * Inane Framework
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\PROJECT
 * @category PROJECT
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 *
 */

declare(strict_types=1);

namespace Knot\Hash;

use Inane\Cli\Cli;
use Inane\Console\Command\{
    Argument,
    Command,
    Option};
use Inane\Stdlib\Exception\ValueError;
use Inane\Stdlib\Hash\HashUtility;

class HashCommands {
    // ee43c751e7f77a021ce4ee5726a06c5c3bff1702326f0e8d259d2055ca27d52e
    /**
     * Tries to identify the hash type of a given string.
     *
     * @param string $hash     The hash string to identify.
     * @param bool   $extended Show extended hash types.
     *
     * @return int 0 on success, otherwise an error code.
     *
     * @throws \RuntimeException If the hash cannot be processed or an internal error occurs.
     */
    #[Command('hash:identify', 'Tries to identify the hash type of a given string.')]
    public function identifyHashCommand(
        #[Argument('The hash string to identify.', required: true)]
        string $hash,
        #[Option('extended', 'e', 'Show extended hash types.', valueless: true)]
        bool $extended = false
    ): int {
        $types = HashUtility::identifyHash($hash, $extended);
        if (!empty($types)) foreach($types as $type) {
            Cli::line($type->description());
        } else Cli::line('Unknown hash type.');

        return 0;
    }

    /**
     * Tries to create the hash of a given string.
     *
     * @param string      $string The string to hash.
     * @param string|null $type   Hash type. Optional; if omitted the default hash type is used.
     *
     * @return int 0 on success, otherwise an error code returned by {@see ValueError::getCode()}.
     *
     * @throws \ValueError If the hash cannot be created due to an invalid type or other value error.
     */
    #[Command('hash:create', 'Tries to create the hash of a given string.')]
    public function createHashCommand(
        #[Argument('The string to hash.', required: true)]
        string $string,
        #[Option('type', 't', 'Hash type.', valueless: false)]
        ?string $type = null
    ): int {
        $args = ['data' => $string];
        if ($type) $args['hashType'] = $type;
        try {
            $hash = HashUtility::hash(...$args);
            Cli::line($hash);
        } catch (ValueError $e) {
            Cli::err($e->getMessage());
            return $e->getCode();
        }
        return 0;
    }

    /**
     * Lists all available hash algorithms.
     *
     * @return int 0 on success, otherwise an error code.
     */
    #[Command('hash:list', 'Lists all available hash algorithms.')]
    public function listHashesCommand(): int {
        $types = HashUtility::listAlgorythems();

        foreach($types as $type) Cli::line($type);

        return 0;
    }
}
