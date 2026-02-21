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
use Knot\Db\Table\UsersTable;

/**
 * User
 */
class User extends AbstractEntity {
    protected string $dataTableClass = UsersTable::class;

    /**
     * @var array An array to hold torrent properties.
     */
    protected array $data = [
        'id' => null,
        'iddepartment' => 1,
        'online' => 0,
        'username' => '',
        'name' => '',
        'email' => '',
        'groups' => '',
        'rank' => 5,
    ];

    /**
     * @var int|null The id of the user.
     */
    public int|null $id {
        get => $this->data['id'];
        set(int|null $value) {
            $this->data['id'] = $value;
        }
    }

    /**
     * @var int The id of the department.
     */
    public int $iddepartment {
        get => $this->data['iddepartment'];
        set(int $value) {
            $this->data['iddepartment'] = $value;
        }
    }

    /**
     * @var Department The department associated with the user.
     */
    public Department $department {
        get {
            $d = new Department();
            $d->fetch($this->iddepartment);
            return $d;
        }
        set(Department $value) {
            $this->data['iddepartment'] = $value->id;
        }
    }

    /**
     * @var string The username of the user.
     */
    public string $username {
        get => $this->data['username'];
        set(string $value) {
            $this->data['username'] = $value;
        }
    }

    /**
     * @var int If the user is online.
     */
    public int $online {
        get => $this->data['online'];
        set(int|bool $value) {
            $this->data['online'] = (int)$value;
        }
    }

    /**
     * @var string The name of the user.
     */
    public string $name {
        get => $this->data['name'];
        set(string $value) {
            $this->data['name'] = $value;
        }
    }

    /**
     * @var string The email of the user.
     */
    public string $email {
        get => $this->data['email'];
        set(string $value) {
            $this->data['email'] = $value;
        }
    }

    /**
     * @var string The groups of the user.
     */
    public string $groups {
        get => $this->data['groups'];
        set(string $value) {
            $this->data['groups'] = $value;
        }
    }

    /**
     * @var int The rank of the user.
     */
    public int $rank {
        get => $this->data['rank'];
        set(int $value) {
            $this->data['rank'] = $value;
        }
    }
}
