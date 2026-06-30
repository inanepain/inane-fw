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

declare(strict_types = 1);

namespace Knot\Db\Entity;

use Inane\Db\Entity\{
    AbstractEntity,
    EntityBeforeSaveMethod};
use Inane\Stdlib\{
    Array\OptionsInterface,
    Exception\JsonException,
    Json,
    Options};
use Knot\Db\Table\FortunesTable;

use function array_map;
use function count;
use function in_array;
use function is_array;
use function preg_match;
use function preg_match_all;
use function preg_split;
use function soundex;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function substr;
use function time;
use function trim;

use const false;
use const JSON_NUMERIC_CHECK;
use const JSON_PARTIAL_OUTPUT_ON_ERROR;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_UNESCAPED_LINE_TERMINATORS;
use const JSON_UNESCAPED_SLASHES;
use const null;
use const PHP_EOL;
use const true;

/**
 * Fortune
 */
class Fortune extends AbstractEntity {
    protected string $dataTableClass = FortunesTable::class;
    /**
     * @var array An array to hold entity properties.
     */
    protected array $data = [
        'id'        => null,
        'favourite' => 0,
        'fortune'   => '',
        'details'   => '{}',
        'views'     => 1,
        'created'   => null,
        'viewed'    => null,
    ];
    #region Prepare Properties
    /**
     * An array containing the list of star signs.
     *
     * @var array
     */
    protected static array $starSigns = [
        'ARIES',
        'TAURUS',
        'GEMINI',
        'CANCER',
        'LEO',
        'VIRGO',
        'LIBRA',
        'SCORPIO',
        'SAGITTARIUS',
        'CAPRICORN',
        'AQUARIUS',
        'PISCES'
    ];
    #endregion Prepare Properties

    #region columns
    /**
     * @var int|null The id of the fortune.
     */
    public int|null $id {
        get => $this->data[__PROPERTY__];
        set(int|null $value) {
            $this->data[__PROPERTY__] = $value;
        }
    }
    /**
     * @var bool If the fortune is a favourite.
     */
    public bool $favourite {
        get => (bool)$this->data[__PROPERTY__];
        set(bool|int|null $value) {
            $this->data[__PROPERTY__] = (int)$value;
        }
    }
    /**
     * @var string The fortune of the fortune.
     */
    public string $fortune {
        get => $this->data[__PROPERTY__];
        set(string $value) {
            $this->data[__PROPERTY__] = $value;
        }
    }
    /**
     * @var OptionsInterface|Options Any extra properties of a fortune.
     */
    public OptionsInterface $details {
        get => new Options($this->data[__PROPERTY__]);
        set(array|string|OptionsInterface $value) {
            $flags = JSON_NUMERIC_CHECK | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES;
            $this->data[__PROPERTY__] = is_array($value) ? Json::encode($value, ['flags' => $flags]) : ($value instanceof OptionsInterface ? $value->toJSON($flags) : $value);
        }
    }
    /**
     * @var int The views of the fortune.
     */
    public int $views {
        get => $this->data[__PROPERTY__];
        set(int $value) {
            $this->data[__PROPERTY__] = $value;
        }
    }
    /**
     * @var int|string The created of the fortune.
     */
    public int|string $created {
        get => $this->data[__PROPERTY__];
        set(int|string $value) {
            $this->data[__PROPERTY__] = $value;
        }
    }
    /**
     * @var int The viewed of the fortune.
     */
    public int $viewed {
        get => $this->data[__PROPERTY__] ?: ($this->data[__PROPERTY__] = time());
        set => $this->data[__PROPERTY__] = $value;
    }
    #endregion columns

    #region event functions
    /**
     * Checks if the given poem is a limerick.
     *
     * @param string       $poem       The poem text to check.
     * @param int          $rhymeCheck The number of rhyme checks to perform (default is 3).
     * @param string|null &$message    Optional. Reference to a string that will contain a message about the check result.
     *
     * @return bool Returns true if the poem is a limerick, false otherwise.
     */
    private function checkLimerick(string $poem, int $rhymeCheck = 3, ?string &$message = ''): bool {
        // Split into lines
        $lines = preg_split('/\r\n|\r|\n/', trim($poem));
        if (count($lines) !== 5) {
            $message = '❌ Not 5 lines (found ' . count($lines) . ')';

            return false;
        }

        // Get last words of each line
        $lastWords = array_map(function($line) {
            if (preg_match('/\b(\w+)\W*$/u', trim($line), $m)) {
                return strtolower($m[1]);
            }

            return '';
        }, $lines);

        // Rough rhyme check: last 3 letters
        $rhyme = function($a, $b) use ($rhymeCheck) {
            return soundex(substr($a, -1 * $rhymeCheck)) == soundex(substr($b, -1 * $rhymeCheck));
        };

        // Check rhyme scheme AABBA
        $aGroup = $rhyme($lastWords[0], $lastWords[1]) && $rhyme($lastWords[0], $lastWords[4]);
        $bGroup = $rhyme($lastWords[2], $lastWords[3]);
        $cGroup = $rhyme($lastWords[0], $lastWords[4]);
        $dGroup = $rhyme($lastWords[0], $lastWords[1]);
        if (!($aGroup && $bGroup)) {
            if (!($aGroup && $cGroup)) {
                if (!($bGroup && $cGroup)) {
                    if (!($bGroup && $dGroup)) {
                        if (!($bGroup)) {
                            $message = '❌ Rhyme scheme is not AABBA';

                            return false;
                        }
                    }
                }
            }
        }

        // Approximate syllables by vowel groups
        $syllableCount = array_map(function($line) {
            return preg_match_all('/[aeiouy]+/i', trim($line));
        }, $lines);

        // Check relative length: 1,2,5 longer than 3,4
        $longOk = ($syllableCount[0] >= 7 && $syllableCount[1] >= 7 && $syllableCount[4] >= 7);
        $shortOk = ($syllableCount[2] <= 7 && $syllableCount[3] <= 7) || ($syllableCount[3] == $syllableCount[2]);

        if (!$longOk) {
            $message = '❌ Lines 1, 2, and 5 should be longer (7+ syllables)';

            return false;
        }
        if (!$shortOk) {
            $message = '❌ Lines 3 and 4 should be shorter (≤7 syllables)';

            return false;
        }

        $message = '✅ Looks like a limerick!';

        return true;
    }

    /**
     * Prepares the entity for further operations.
     *
     * @use EntityBeforeSaveMethod to set this as a pre-save method.
     *
     * @return void
     * @throws JsonException
     */
    #[EntityBeforeSaveMethod]
    protected function prepare(): void {
        $save = false;
        $details = $this->details;
        $category = $details->get('category', new Options())
            ->toArray()
        ;

        if (!in_array('limerick', $category, true)) {
            if ($this->checkLimerick($this->fortune)) {
                $details->merge(['category' => ['limerick']]);
                $save = true;
            }
        }
        if (!in_array('Q&A', $category, true)) {
            if (str_starts_with($this->fortune, 'Q:')) {
                $details->merge(['category' => ['Q&A']]);
                $save = true;
            }
        }
        if (!in_array('confucius', $category, true)) {
            if (str_starts_with($this->fortune, 'Confuci')) {
                $details->merge(['category' => ['confucius']]);
                $save = true;
            }
        }
        if (!in_array('starSign', $category, true)) {
            foreach(self::$starSigns as $starSign) {
                if (str_starts_with($this->fortune, $starSign)) {
                    $details->merge([
                        'category' => ['starSign'],
                        'starSign' => strtolower($starSign)
                    ]);
                    $save = true;
                    break;
                }
            }
        }
        if (!in_array('quote', $category, true)) {
            preg_match('/(\n\s+-- \D+$)/', $this->fortune, $matches);
            if (!empty($matches)) {
                $details->merge(['category' => ['quote']]);
                $save = true;
            }
        }
        if (!in_array('1liner', $category, true)) {
            if (!str_contains($this->fortune, PHP_EOL)) {
                $details->merge(['category' => ['1liner']]);
                $save = true;
            }
        }

        if ($save) $this->details = $details;
    }
    #endregion event functions

    /**
     * Converts the entity to its string representation.
     *
     * @return string The string representation of the entity.
     */
    public function __toString(): string {
        return $this->fortune;
    }
}
