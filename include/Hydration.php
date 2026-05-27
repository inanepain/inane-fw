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
 * @package  inanepain\inane-fw
 * @category inane-fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

use Inane\Db\Hydrator\AbstractHydratable;
use Inane\Db\Hydrator\FieldType;
use Inane\Db\Hydrator\HydrateField;

if (file_exists(getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    $autoload = getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} elseif (!$autoload = getEnv('ENV_PHP_VENDOR')) {
    echo ('No autoload.php file found at ' . getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' . PHP_EOL);
    echo ('AND' . PHP_EOL);
    echo ('Missing environment variable `ENV_PHP_VENDOR` which should point to the php vendor autoload.php file.' . PHP_EOL);
    exit(10);
}

require_once $autoload;

\Inane\Cli\Cli::line('Autoload: Loaded: ' . $autoload);

#region EXAMPLE

/**
 * Data Transfer Object (DTO) representing a user entity.
 *
 * This class encapsulates user-related data, making it transferable
 * across application layers. The class includes user-specific information
 * such as ID, full name, email address, creation date, and an optional role.
 *
 * The constructor initializes all properties, allowing for concise and
 * straightforward instantiation.
 *
 * @param string            $id        The unique identifier of the user.
 * @param string            $fullName  The full name of the user.
 * @param string            $email     The email address of the user.
 * @param string            $password  The password of the user.
 * @param string            $score     The score of the user.
 * @param array             $details   The details of the user.
 * @param DateTimeImmutable $createdAt The date and time when the user was created.
 * @param string|null       $role      The role of the user, optional and defaults to null.
 */
class UserDTO extends AbstractHydratable {
    public function __construct(
        public string            $id,
        public string            $fullName,
        public string            $email,
        #[HydrateField(alias: 'secret')]
        public string            $password,
        public int               $score,
        #[HydrateField()]
        public array             $details,
        #[HydrateField(type: FieldType::Datetime, format: 'Y-m-d H:i:s')]
        public DateTimeImmutable $createdAt,
        #[HydrateField(type: FieldType::Timestamp)]
        public DateTimeImmutable $updatedAt,
        #[HydrateField(type: FieldType::Datetime, format: 'Y-M-d H:i:s')]
        public DateTimeImmutable $removedAt,
        public ?string           $role = null,
    ) {}
}

\Inane\Dumper\Dumper::$showRunkit7SupportMessage = false;

// Hydrate from database row (snake_case keys)
$user = UserDTO::hydrate([
    'id'         => 'usr_123',
    'full_name'  => 'Jane Doe',
    'email'      => 'jane@example.com',
    'secret'     => 'iLogIn',
    'score'      => 69,
    'created_at' => '2024-06-15 10:30:00',
    'updated_at' => 1779905411,
    'removed_at' => '2024-Jun-15 10:30:00',
    'details'    => '{"age": 30, "location": "New York"}',
    'role'       => 'admin',
]);

echo $user->fullName . PHP_EOL;  // Jane Doe
echo get_class($user) . PHP_EOL; // UserDTO

// Reverse hydration
$array = $user->dehydrate();
dd($array);
// ['id' => 'usr_123', 'full_name' => 'Jane Doe', ...]
#endregion EXAMPLE
