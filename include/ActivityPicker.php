<?php

declare(strict_types=1);

use Inane\Stdlib\Merge\MergeInterface;
use Inane\Stdlib\Merge\MergeTrait;

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
    private array $activities = [
        'Breast caressing',
        'Grinding while clothed',
        '69 position oral',
        'Anal rimming (him on her)',
        'Clitoral fingering',
        'Handjob techniques',
        'Shallow penetration',
    ];

    /**
     * Configuration options for the application behavior.
     */
    private array $options = [
        'rangeSize' => 9,
        'step' => 5,
        'retryDuplicates' => 3,
        'logLevel' => 'WARN',
        'debug' => [
            'pick' => false,
            'lastActivity' => false,
            'options' => false,
            'duplicate' => false,
            'numberOfPicks' => false,
        ]
    ];

    private array $cache = [
        'minIndex' => 0,
    ];

    private ?int $currentIndex = null;
    private bool $isComplete = false;

    /**
     * Minimum index with property hooks.
     */
    private int $minIndex {
        get => $this->cache['minIndex'];
        set(int $value) {
            $this->cache['minIndex'] = $this->clamp($value, 0, $this->lastActivity);
            if ($this->cache['minIndex'] === $this->lastActivity) {
                $this->isComplete = true;
            }
        }
    }

    /**
     * Maximum index.
     */
    private int $maxIndex {
        get {
            $maxIndex = $this->minIndex + $this->options['rangeSize'];
            return $maxIndex > $this->lastActivity ? $this->lastActivity : $maxIndex;
        }
    }

    /**
     * Index of the last activity.
     */
    private int $lastActivity {
        get => count($this->activities) - 1;
    }

    /**
     * Determines whether the starting index is the minimum index.
     */
    public bool $start {
        get => $this->minIndex === 0;
    }

    /**
     * Retrieves the completion status.
     */
    public bool $end {
        get => $this->isComplete;
    }

    /**
     * Retrieves the current activity.
     */
    public string $activity {
        get => $this->activities[$this->currentIndex ?? 0];
    }

    /**
     * Constructs a new instance.
     *
     * @param array|null $activities
     * @param array $options
     */
    public function __construct(?array $activities = null, array $options = ['rangeSize' => 9, 'step' => 5]) {
        if ($activities !== null) {
            $this->activities = $activities;
        }

        $this->mergeOptions($this->options, $options);
        $this->reset();
    }

    /**
     * Logs debug messages (simplified for PHP version).
     */
    private function logDebug(string $context, ...$messages): void {
        if (($this->options['debug'][$context] ?? false)) {
            // In a real framework this would use a logger, here we just echo if debug is on
            // echo "[$context] " . implode(' ', array_map(fn($m) => is_scalar($m) ? $m : json_encode($m), $messages)) . "\n";
        }
    }

    /**
     * Generates a random integer.
     */
    private function randomNumber(int $min, int $max): int {
        return mt_rand($min, $max);
    }

    /**
     * Helper to clamp values.
     */
    private function clamp(int $value, int $min, int $max): int {
        return max($min, min($max, $value));
    }

    /**
     * Sets the number of picks.
     */
    public function setNumberOfPicks(int $numberOfPicks, bool|int $optimiseRange = false): self {
        $this->options['step'] = (int) ceil(($this->lastActivity + 1) / $numberOfPicks);

        if ($optimiseRange !== false) {
            if (is_int($optimiseRange)) {
                $this->options['rangeSize'] = $optimiseRange;
            } else {
                $this->options['rangeSize'] = (int) ceil(($this->lastActivity + 1) / $this->options['step']);
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
     */
    public function pick(): string {
        if ($this->isComplete) {
            $this->reset();
            return 'RESET';
        }

        $i = 0;
        do {
            $index = $this->randomNumber($this->minIndex, $this->maxIndex);
        } while ($index === $this->currentIndex && $this->options['retryDuplicates'] > $i++);

        $this->currentIndex = $index;
        $this->minIndex += $this->options['step'];

        return $this->activity;
    }
}
