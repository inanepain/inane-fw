<?php

namespace Knot\Hash;

use Inane\Cli\Cli;
use Inane\Console\Command\Argument;
use Inane\Console\Command\Command;
use Inane\Console\Command\Option;
use Inane\Stdlib\Exception\ValueError;
use Inane\Stdlib\Hash\HashUtility;

class HashCommands {
    // ee43c751e7f77a021ce4ee5726a06c5c3bff1702326f0e8d259d2055ca27d52e

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

    #[Command('hash:list', 'Lists all available hash algorithms.')]
    public function listHashesCommand(): int {
        $types = HashUtility::listAlgorythems();

        foreach($types as $type) Cli::line($type);

        return 0;
    }
}
