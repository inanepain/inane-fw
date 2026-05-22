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

namespace Knot\ActivityPicker;

use Inane\Log\Logger;
use Inane\Log\Writer\StderrorWriter;
use Inane\Log\Writer\StdoutWriter;
use Inane\Stdlib\Array\OptionsInterface;
use Inane\Stdlib\Exception\JsonException;
use Inane\Stdlib\Merge\MergeInterface;
use Inane\Stdlib\Merge\MergeTrait;
use Inane\Stdlib\Options;
use Psr\Log\LogLevel;
use Random\RandomException;

use function array_shift;
use function ceil;
use function clamp;
use function is_array;
use function is_int;
use function random_int;

/**
 * ActivityPicker - Randomly selects sexual activities from a predefined list.
 * Starts with range 0-9, expands max to 99, then contracts min until complete.
 * Returns "RESET" and restarts after finishing the full progression.
 *
 * PHP version 8.5
 */
class ActivityPicker implements MergeInterface {
    use MergeTrait;

    /**
     * A list of intimate activities.
     */
    private Options $activities {
        /**
         * @throws JsonException
         */
        get => $this->activities ?? $this->activities = new Options([
            'Couple activity: Cook a three-course meal together',
            'Team-building activity: Escape room challenge',
            'Outdoor activity: Hiking a nature trail',
            'Kids activity: Treasure hunt in the garden',
            'Date idea: Rooftop picnic at sunset',
            'Icebreaker: Two truths and a lie',
            'Fitness activity: HIIT circuit workout',
            'Party game: Charades',
            'Coding exercise: Build a CLI todo app in PHP',
            'Classroom activity: Debate on AI ethics',
        ]);
        /**
         * @throws JsonException
         */
        set (array|Options $value) => is_array($value) ? $this->activities = new Options($value) : $this->activities = $value;
    }

    /**
     * Manages configuration options and their initialization.
     *
     * The `$options` container includes settings such as range size, step size,
     * retry limits for duplicates, logging level, and detailed debugging options.
     * Provides lazy initialization of default values if not already defined.
     *
     * Type details:
     * - Container: `\Inane\Stdlib\Options`
     * - Keys:
     *   - `rangeSize` int
     *   - `step` int
     *   - `retryDuplicates` int
     *   - `logLevel` string
     *   - `debug` \Inane\Stdlib\Options  Debug options container (not a plain array)
     *
     * Hint for static analysers/IDEs:
     * - `debug` is an instance of `\Inane\Stdlib\Options`, so chained access like
     *   `$this->options->debug->get($context)` is valid and not a magic property misuse.
     *
     * @var Options&object{
     *     rangeSize: int,
     *     step: int,
     *     retryDuplicates: int,
     *     logLevel: string,
     *     debug: \Inane\Stdlib\Options
     * }
     *
     * @throws JsonException
     */
    private Options $options {
        /**
         * @throws JsonException
         */
        get => $this->options ?? $this->options = new Options([
            'rangeSize'       => 1,
            'step'            => 1,
            'retryDuplicates' => 3,
            'logLevel'        => 'WARN',
            'debug'           => [
                'pick'            => false,
                'totalActivities' => false,
                'options'         => false,
                'duplicate'       => false,
                'numberOfPicks'   => false,
                'rangeSize'       => false,
            ],
        ]);
        /**
         * @throws JsonException
         */
        set (array|Options $value) => is_array($value) ? $this->options = new Options($value) : $this->options = $value;
    }

    /**
     * Stores calculated values for various operations.
     */
    private Options $cache {
        /**
         * @throws JsonException
         */
        get => $this->cache ?? $this->cache = new Options([
            'minIndex' => 0,
        ]);
        /**
         * @throws JsonException
         */
        set (array|Options $value) => is_array($value) ? $this->cache = new Options($value) : $this->cache = $value;
    }

    /**
     * Current index.
     */
    private ?int $currentIndex = null;

    /**
     * Indicates whether the process or operation is complete.
     */
    private bool $isComplete = false;

    /**
     * Minimum index with property hooks.
     */
    private int $minIndex {
        get => $this->cache->minIndex;
        set(int $value) {
            if ($value > $this->lastActivity) {
                $this->isComplete = true;
            }

            $this->cache->minIndex = clamp($value, $this->activities->firstKey(), $this->lastActivity);
        }
    }

    /**
     * Maximum index.
     */
    private int $maxIndex {
        get {
            $maxIndex = $this->minIndex + $this->options->rangeSize - 1;

            return clamp($maxIndex, $this->activities->firstKey(), $this->lastActivity);
        }
    }

    /**
     * Total number of activities.
     */
    private int $totalActivities {
        get => $this->activities->count();
    }

    /**
     * Index of the last activity.
     */
    private int $lastActivity {
        get => $this->activities->lastKey();
    }

    /**
     * Determines whether the minimum index is the first index.
     */
    public bool $start {
        get => $this->minIndex === $this->activities->firstKey();
    }

    /**
     * Returns whether the current index is the last index, i.e. the end of the activity range (isComplete).
     */
    public bool $end {
        get => $this->isComplete;
    }

    /**
     * Retrieves the current activity.
     */
    public string $activity {
        get => $this->activities[$this->currentIndex ?? $this->activities->firstKey()];
    }

    /**
     * Manages logging operations and handles message output using configured writers.
     */
    protected Logger $logger {
        get => $this->logger ?? $this->logger = new Logger([
            new StderrorWriter()->setMinLevel(LogLevel::WARNING),
            new StdoutWriter()->setMaxLevel(LogLevel::INFO),
        ]);
    }

    /**
     * Constructor method for initializing the class with activities and options.
     *
     * @param null|array|Options $activities Optional activities to initialize the class with.
     * @param array|Options      $options    Optional configuration options.
     *
     * @return void
     */
    public function __construct(null|array|Options $activities = null, array|Options $options = []) {
        if ($activities !== null) {
            $this->activities = $activities instanceof OptionsInterface ? $activities->values() : array_values($activities);
        }

        $this->mergeOptions($this->options, $options);
        $this->log('debug', 'totalActivities', 'Activities', ['total' => $this->totalActivities]);
        $this->log('debug', 'options', 'Options', $this->options->toArray());
    }

    /**
     * Logs a message with a specified level and context if debugging is enabled for the context.
     *
     * @param string $level    The log level (e.g., 'debug', 'info', 'error').
     * @param string $context  The context under which the log is categorized.
     * @param mixed  $messages Additional message(s) to log.
     *
     * @return void
     */
    private function log(string $level, string $context, ...$messages): void {
        if ($this->options->debug->get($context)) {
            $this->logger->log($level, array_shift($messages), $messages);
        }
    }

    /**
     * Generates a random integer.
     *
     * @throws RandomException
     */
    private function randomNumber(int $min, int $max): int {
        return random_int($min, $max);
    }

    /**
     * Sets the number of picks.
     */
    public function setNumberOfPicks(int $numberOfPicks, bool|int $optimiseRange = false): self {
        $numberOfPicks = clamp($numberOfPicks, 1, $this->totalActivities);
        $this->options->step = (int)ceil($this->totalActivities / $numberOfPicks);

        if ($optimiseRange !== false) {
            if (is_int($optimiseRange)) {
                $this->options->rangeSize = $optimiseRange;
            } else {
                $this->options->rangeSize = $this->options->step;
                $this->log('debug', 'rangeSize', 'Scale Range Size', $this->options->rangeSize, 'Reach', $this->options->rangeSize * $numberOfPicks, 'Target', $this->totalActivities);

                while(($this->options->rangeSize * $numberOfPicks) < $this->totalActivities) {
                    $this->options->rangeSize++;
                    $this->log('debug', 'rangeSize', 'Scale Range Size', $this->options->rangeSize, 'Reach', $this->options->rangeSize * $numberOfPicks, 'Target', $this->totalActivities);
                }
            }
        }

        return $this;
    }

    /**
     * Resets the internal state.
     */
    public function reset(): self {
        $this->minIndex = 0;
        $this->isComplete = false;
        $this->currentIndex = null;

        return $this;
    }

    /**
     * Selects an item.
     *
     * @throws RandomException
     */
    public function pick(): string {
        if ($this->isComplete) {
            $this->reset();

            return 'RESET';
        }

        $i = 0;
        do {
            $index = $this->randomNumber($this->minIndex, $this->maxIndex);
        } while($index === $this->currentIndex && $this->options->retryDuplicates > $i++);

        $this->currentIndex = $index;
        $this->minIndex += $this->options->step;

        return $this->activity;
    }
}
