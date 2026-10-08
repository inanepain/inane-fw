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

namespace Knot\Db\Entity;

use Inane\Db\Entity\AbstractEntity;
use Inane\Stdlib\Json;
use Knot\Db\Table\UsersTable;

use function is_array;
use function is_string;

/**
 * User
 */
class UserAbstract extends AbstractEntity {
    protected string $dataTableClass = UsersTable::class;

    /**
     * @var array An array to hold torrent properties.
     */
    protected array $data = [
        'id' => null,
        'iddepartment' => 1,
        'username' => '',
        'password' => '',
        'name' => '',
        'email' => '',
        'online' => 0,
        'groups' => '["users"]',
        'rank' => 5,
    ];

    /**
     * @var int|null The id of the user.
     */
    public int|null $id {
        get => $this->data[__PROPERTY__];
        set(int|null $value) {
            $this->data[__PROPERTY__] = $value;
        }
    }

    /**
     * @var int The id of the department.
     */
    public int $iddepartment {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var Department The department associated with the user.
     */
    public Department $department {
        get => new ($this->dataTableClass)()->fetch($this->iddepartment);
        set => $this->iddepartment = $value->id;
    }

    /**
     * @var string The username of the user.
     */
    public string $username {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var string The password of the user.
     */
    public string $password {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var int If the user is online.
     */
    public int $online {
        get => $this->data[__PROPERTY__];
        set(int|bool $value) {
            $this->data[__PROPERTY__] = (int)$value;
        }
    }

    /**
     * @var string The name of the user.
     */
    public string $name {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var string The email of the user.
     */
    public string $email {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var string|array The groups of the user.
     */
    public string|array $groups {
        get => is_string($this->data[__PROPERTY__]) ? Json::decode($this->data[__PROPERTY__]) : $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = is_array($value) ? Json::encode($value) : $value;
    }

    /**
     * @var int The rank of the user.
     */
    public int $rank {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }
}
