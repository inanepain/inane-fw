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

use Exception;
use Inane\Db\Entity\{
    AbstractEntity};
use Knot\Brew\Brew;
use Knot\Db\Table\FormulasTable;

use function array_filter;
use function array_unique;
use function explode;
use function implode;
use function sort;

use const null;

/**
 * Formula
 */
class Formula extends AbstractEntity {
    /**
     * Represents the Homebrew package manager utility.
     *
     * This class provides methods to interact with Homebrew,
     * allowing for installation, updating, and managing software packages
     * on macOS systems.
     *
     * @throws \RuntimeException If an error occurs during Homebrew operations.
     */
    public static Brew $brew;

    /**
     * Represents the class name for the formulas table.
     *
     * This constant holds the fully qualified class name of the
     * table responsible for managing formulas data within the system.
     *
     * @var string
     */
    protected string $dataTableClass = FormulasTable::class;

    /**
     * Contains the initial data structure for a software package.
     *
     * This array defines the default values and types for various properties of a software package.
     * Each key represents a property with its corresponding default value.
     *
     * @var array<string, mixed> The data structure for a software package.
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
     * Provides access to the name property of an object.
     *
     * This property can be retrieved or updated using the getter and setter methods.
     *
     * @return mixed The value of the name property.
     */
    public string $name {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * Provides access to the description data.
     *
     * This property allows retrieval and modification of the description associated with the object.
     */
    public string $desc {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * Retrieves or sets the version of the software component.
     *
     * This property allows getting and setting the version string associated with the software component.
     *
     * @var string The version of the software component.
     */
    public string $version {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * Retrieves or sets the homepage URL.
     *
     * This property allows getting and setting the homepage URL for a website or application.
     *
     * @var string The URL of the homepage.
     */
    public string $homepage {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * Sets a formula's installed state.
     *
     * Marking a formula as installed also marks it as reviewed.
     *
     * @var bool If the formula is installed.
     */
    public bool $installed {
        get => (bool)$this->data[__PROPERTY__];
        set(bool|int|null $value) {
            $this->data[__PROPERTY__] = (int)$value;
            if ($value) {
                $this->reviewed = true;
            }
        }
    }

    /**
     * Indicates whether a formula has been reviewed.
     *
     * Setting this property to true marks the formula as reviewed.
     *
     * @var bool If the formula has been reviewed.
     */
    public bool $reviewed {
        get => (bool)$this->data[__PROPERTY__];
        set(bool|int|null $value) {
            $this->data[__PROPERTY__] = (int)$value;
        }
    }

    /**
     * Provides access to the state of an object.
     *
     * This property allows getting and setting the internal state data.
     *
     * @var mixed The current state of the object.
     */
    public string $state {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * Provides access to the data associated with a property.
     *
     * This accessor allows for getting and setting the value of a property stored in the object's data array.
     *
     * @var mixed The value of the property.
     */
    public string $tags {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * Provides access to the tags associated with an item.
     *
     * The getter returns a sorted, unique array of tags.
     * The setter accepts an array of tags, removes duplicates,
     * and sorts them before storing them as a comma-separated string.
     *
     * @var array An array of unique, sorted tags.
     */
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

    /**
     * Sets a flag's state.
     *
     * @var bool If the flag is enabled.
     */
    public bool $flag {
        get => (bool)$this->data[__PROPERTY__];
        set(bool|int|null $value) {
            $this->data[__PROPERTY__] = (int)$value;
        }
    }

    /**
     * Gets or sets the updated state of an item.
     *
     * This property indicates whether an item has been updated. Setting this property to a new value will update the internal state accordingly.
     *
     * @var mixed The updated state of the item.
     */
    public int $updated {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }

    /**
     * Retrieves or sets the modified state of an entity.
     *
     * The modified state indicates whether changes have been made to the entity.
     *
     * @var mixed The value indicating if the entity has been modified.
     */
    public string $modified {
        get => $this->data[__PROPERTY__];
        set => $this->data[__PROPERTY__] = $value;
    }
    #endregion columns

    #region Actions
    /**
     * Installs the current instance using the Brew installer.
     *
     * This method attempts to install the current instance by invoking the `installAction`
     * method of the static `$brew` object. If the installation is successful, it sets
     * the `installed` property to true.
     *
     * @return self The current instance after attempting installation.
     *
     * @throws Exception If there is an error during the installation process.
     */
    public function install(): self {
        if (static::$brew->installAction($this)) {
            $this->installed = true;
        }

        return $this;
    }

    /**
     * Uninstalls the current package.
     *
     * This method attempts to uninstall the package using the brew system. If successful,
     * it sets the installed flag to false and returns the instance of the class.
     *
     * @return self The current instance after attempting to uninstall.
     * @throws Exception If there is an issue during the uninstall process.
     */
    public function uninstall(): self {
        if (static::$brew->uninstallAction($this)) {
            $this->installed = false;
        }
        return $this;
    }
    #endregion Actions
}
