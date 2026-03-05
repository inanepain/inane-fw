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

use Inane\Db\Entity\{
    AbstractEntity};
use Knot\Brew\Brew;
use Knot\Db\Table\FormulasTable;

use function array_unique;
use function explode;
use function implode;
use function sort;

use const null;

/**
 * Formula
 */
class Formula extends AbstractEntity {
    public static Brew $brew;
    protected string $dataTableClass = FormulasTable::class;

    /**
     * @var array An array to hold entity properties.
     */
    protected array $data = [
        'name' => '',
        'desc' => '',
        'version' => '',
        'homepage' => '',
        'installed' => 0,
        'reviewed' => 0,
        'state' => 'update',
        'tags' => '',
        'flag' => 0,
        'updated' => null,
        'modified' => null,
    ];

    #region Prepare Properties
    #endregion Prepare Properties

    #region columns
    /**
     * @var string The name of the formula.
     */
    public string $name {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var string The desc of the formula.
     */
    public string $desc {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var string The version of the formula.
     */
    public string $version {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var string The homepage of the formula.
     */
    public string $homepage {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var bool If the formula is installed.
     */
    public bool $installed {
        get => (bool)$this->data[__PROPERTY__];
        set(bool|int|null $value) {
            $this->data[__PROPERTY__] = (int)$value;
        }
    }

    public bool $reviewed {
        get => (bool)$this->data[__PROPERTY__];
        set(bool|int|null $value) {
            $this->data[__PROPERTY__] = (int)$value;
        }
    }

    public string $state {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    public string $tags {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    public array $tagArray {
        get => (static function(string $tags): array {
            $a = explode(',', $tags);
            sort($a);
            return $a;
        })($this->data['tags']);
        set {
            $this->data['tags'] = implode(',', array_filter(array_unique($value)));
        }
    }

    public bool $flag {
        get => (bool)$this->data[__PROPERTY__];
        set(bool|int|null $value) {
            $this->data[__PROPERTY__] = (int)$value;
        }
    }

    /**
     * @var int The updated timestamp of the formula.
     */
    public int $updated {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * @var string The modified date of the formula.
     */
    public string $modified {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }
    #endregion columns

    #region Actions
    public function install(): self {
        if (static::$brew->installAction($this)) {
            $this->installed = true;
        }

        return $this;
    }

    public function uninstall(): self {
        if (static::$brew->uninstallAction($this)) {
            $this->installed = false;
        }
        return $this;
    }
    #endregion Actions
}
