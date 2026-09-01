<?php

/**
 * FontLibrary
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

use Inane\Dumper\Silence;
use Inane\File\File;
use Inane\Stdlib\Converters\Arrayable;
use Inane\Stdlib\Exception\JsonException;
use Inane\Stdlib\Exception\RuntimeException;
use Inane\Stdlib\Options;
use Inane\Stdlib\Output\ArrayOutput;

use function array_key_exists;
use function dd;
use function in_array;
use function str_ends_with;
use function str_replace;
use function stripos;
use function time;

/**
 * FontLibrary
 *
 * inane-fw
 *
 * @version 0.1.0
 */
class FontLibrary implements Arrayable {
    public const int AUTOSAVE_OFF    = 0;

    public const int AUTOSAVE_MASTER = 1;

    protected static int $id = 0;

    public readonly int $instance;

    protected bool $master = false;

    protected const string FONT_PATH = 'data/fonts.json';

    protected File $fontFile;

    protected static array $weights = [
        'UltraLight Italic',
        'ExtraLight Italic',
        'Extralight Italic',
        'Light Italic',
        'Medium Italic',
        'Regular Italic',
        'Semibold Italic',
        'SemiBold Italic',
        'Demibold Italic',
        'Bold Italic',
        'ExtraBold Italic',
        'Extrabold Italic',

        'Heavy Italic',
        'Black Italic',

        'Heavy Oblique',
        'Medium Oblique',
        'Light Oblique',
        'Bold Oblique',
        'Thin Italic',

        'Italic',
        'Demi',
        'Ultra',

        'Oblique',

        'UltraLight',
        'Ultralight',
        'ExtraLight',
        'Extralight',
        'Light',
        'Thin',
        'Plain',
        'Regular',
        'Medium',
        'Semibold',
        'SemiBold',
        'DemiBold',
        'Demibold',
        'Black',
        'Bold',
        'BoldOblique',
        'Extrabold',
        'ExtraBold',
        'UltraBold',
        'Heavy',

        'Condensed',
    ];

    protected Options $fonts;

    protected Options $filters;

    public int $size {
        get => $this->fonts->count();
    }

    /**
     * FontLibrary constructor
     *
     * @return void
     */
    public function __construct(
        public int $autoSave = self::AUTOSAVE_MASTER,
    ) {
        $this->bootstrap();
        $this->initialise();
    }

    /**
     * Destructor method that ensures data is saved automatically
     * if the instance meets specified conditions.
     *
     * @return void
     *
     * @throws RuntimeException|JsonException if automatic save fails.
     */
    #[Silence(true)]
    public function __destruct() {
        if ($this->instance === 1 && $this->autoSave === self::AUTOSAVE_MASTER) {
            dd("auto-save: {$this->instance}");
            $this->save();
        }
    }

    /**
     * Create the required dependencies
     *
     * @return void
     */
    protected function bootstrap(): void {
        $this->instance = ++static::$id;

        $this->fonts = new Options();
        $this->filters = new Options();
        $this->fontFile = new File(self::FONT_PATH);
    }

    /**
     * Post-bootstrap configuration
     *
     * @return void
     */
    protected function initialise(): void {}

    /**
     * Add a font to the library
     *
     * A weight suffix in the name is stripped and added to the font's weights,
     * grouping the variants of a font under a single entry.
     *
     * @param Options|array|Font $data font instance or font properties
     *
     * @return void
     * @throws JsonException
     * @throws RuntimeException
     */
    protected function addFont(Options|array|Font $data): void {
        // an existing font simply replaces any entry with a matching name
        if ($data instanceof Font) {
            $this->fonts->set($data->name, $data);

            return;
        }

        if (!array_key_exists('weights', $data)) $data['weights'] = [];

        foreach(static::$weights as $weight) {
            if (str_ends_with($data['name'], " $weight")) {
                // Fix for some fonts with funny names.
                if (in_array($weight, $data['weights'], true)) continue;
                $data['name'] = str_replace(" $weight", '', $data['name']);
                $data['weights'][] = $weight;
                break;
            }
        }

        $font = $this->fonts->get($data['name'], new Font(...$data), true);
        $font->addWeight($data['weights']);
    }

    /**
     * Reads a file from the specified path and loads its content.
     *
     * @param string $filePath The path to the file to be read.
     *
     * @return bool True if the file was successfully read and loaded, false otherwise.
     *
     * @throws RuntimeException If the file cannot be read or loaded.
     * @throws JsonException
     */
    public function readFile(string $filePath): bool {
        if ($filePath === self::FONT_PATH && $this->instance !== 1) return false;
        $this->fontFile = new File($filePath);

        $data = new ArrayOutput($this->fontFile->read())->output();
        $this->load($data);

        return true;
    }

    /**
     * Load fonts into the library
     *
     * @param null|Options|array $data fonts to load, `null` loads the library's font file
     *
     * @return void
     *
     * @throws JsonException|RuntimeException font file contains invalid json
     */
    #[Silence(true)]
    public function load(null|Options|array $data = null): void {
        // without data, the library's font file is used
        if ($data === null) {
            $this->master = true;
            $data = new ArrayOutput($this->fontFile->read())->output();
        }

        foreach($data as $font) {
            $this->addFont($font);
        }

        dd("load: {$this->instance}");
    }

    /**
     * Saves the current fonts to a specified file or the default path
     *
     * @param null|string $saveFile path to the file where fonts should be saved; if null, the default file is used
     *
     * @return bool true on successful save, false on failure
     *
     * @throws JsonException if fonts cannot be converted to JSON
     */
    #[Silence(true)]
    public function save(?string $saveFile = null): bool {
        if ($saveFile !== null) {
            if ($saveFile === self::FONT_PATH) return false;

            $this->fontFile = new File($saveFile);
        } elseif ($this->instance !== 1) {
            return false;
        }

        dd("save: {$this->instance}");
        $this->fontFile->write($this->fonts->toJSON());

        return true;
    }

    /**
     * Retrieve a list of all font keys.
     *
     * @return array list of font keys
     */
    public function list(): array {
        return $this->fonts->keys();
    }

    /**
     * Get a font by name
     *
     * @param string $name font name
     *
     * @return null|Font font or `null` if not found
     */
    public function getFont(string $name): ?Font {
        return $this->fonts->get($name);
    }

    /**
     * Search fonts by partial name, case-insensitive
     *
     * @param string $name partial font name
     *
     * @return FontLibrary library containing the matching fonts
     *
     * @throws JsonException
     * @throws RuntimeException
     */
    public function search(string $name): static {
        // matches are collected in a new library
        $results = new static();
        $results->filters->merge($this->filters);
        $results->filters->set(time(), [
            $this->instance,
            $name,
        ]);

        foreach($this->fonts as $key => $font)
            if (stripos((string)$key, $name) !== false) $results->addFont($font);

        return $results;
    }

    /**
     * Return Array representation of data
     *
     * @return array as Array
     */
    public function toArray(): array {
        return $this->fonts->toArray();
    }
}
