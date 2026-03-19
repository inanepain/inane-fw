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

namespace Knot\Gitea;

use Inane\Console\Command\Command;
use Inane\Stdlib\Exception\RuntimeException;
use Inane\Stdlib\Options;
use ReflectionException;

/**
 * Class GiteaCommands
 *
 * Provides a set of commands for interacting with the Gitea server, including
 * functionalities such as uploading release packages. This class handles the
 * configuration, execution, and management of these Gitea-related commands.
 */
class GiteaCommands {
    /**
     * Executes the command to upload a release package to the Gitea server.
     *
     * This method configures the necessary options for connecting to the Gitea server,
     * sets up additional tweaking options, and manages upload behaviors, such as
     * enabling automatic upload and deletion. It then utilizes the configured uploader
     * to perform the upload process.
     *
     * @return int Returns 0 upon successful execution of the upload command.
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    #[Command('gitea:upload', 'Upload release package', ['gu'])]
    public function uploadCommand(): int {
        $config = new Options([
            'server' => [
                'protocol' => 'http',
                'host' => 'blackbetty.local',
                'username' => 'philip',
                'password' => 'Esoter1c!@',
            ],
            'tweaks' => [
                'autoUpload' => true,
                'autoDelete' => true,
            ],
            'develop' => [
                'dryRun' => !true,
                'verbose' => true,
            ],
        ]);

        $uploader = new GiteaPackageUploader($config);
        $uploader->run();

        return 0;
    }
}
