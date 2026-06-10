<?php

/**
 * Select
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\select
 * @category select
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Console;

/**
 * Escape sequence for moving the cursor up in the terminal.
 *
 * @var string
 */
class Select {
    /**
     * Escape sequence for moving the cursor up in the terminal.
     *
     * @var string
     */
    public const string UP = "\033[A";

    /**
     * Represents the down arrow escape sequence for terminal control.
     */
    public const string DOWN = "\033[B";

    /**
     * Represents the newline character for line breaks.
     */
    public const string NEW_LINE = "\n";

    /**
     * Represents the carriage return escape sequence, which moves the cursor to the beginning of the current line.
     */
    public const string CARRIAGE_RETURN = "\r";

    /**
     * Holds the current value, initialized to zero.
     */
    protected int $current = 0;

    /**
     * key
     *
     * @${CARET}
     *
     * @var false|string
     */
    private string|false $key;

    /**
     * screen
     *
     * @${CARET}
     *
     * @var Screen
     */
    private Screen $screen;

    protected array $options = [];

    /**
     * Constructor for the class.
     *
     * @param array       $items  An array of items, defaulting to ['Item 1'].
     * @param string      $prompt A string prompt for user interaction, defaulting to 'Use ↑/↓ to navigate, Enter to select'.
     * @param Screen|null $screen An optional instance of Screen. If null, a new Screen instance will be created.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the provided screen object is not a valid instance of Screen.
     */
    public function __construct(
        protected(set) array  $items = ['Item 1'] {
            get => $this->items;
            set {
                $this->current = 0;
                $this->items = $value;
                $this->setOptions();
            }
        },
        protected(set) string $prompt = 'Use ↑/↓ to navigate, Enter to select',
        ?Screen               $screen = null,
    ) {
        if ($screen === null) {
            $this->screen = new Screen();
        }
    }

    /**
     * Populates the options array with SelectOption instances created from the items array.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the items array is not iterable or contains invalid elements.
     */
    protected function setOptions(): void {
        foreach($this->items as $index => $item) {
            $this->options[] = new SelectOption($index, $item);
        }
    }

    /**
     * Renders the current prompt and list of items, highlighting the currently selected item.
     *
     * @return string The rendered output as a string.
     *
     * @throws \RuntimeException If the rendering process fails.
     */
    protected function render(): string {
        echo $this->prompt . PHP_EOL . PHP_EOL;
        $output = '';
        foreach($this->options as $index => $item) {
            if ($index === $this->current) {
                $output .= "\033[7m> {$item}\033[0m\n"; // Reverse video
            } else {
                $output .= "  {$item}\n";
            }
        }

        return $output;
    }

    /**
     * Reads a key input from the standard input (STDIN) and stores it for further processing.
     *
     * @return void
     *
     * @throws \RuntimeException If reading from STDIN fails.
     */
    protected function readKey(): void {
        $this->key = fread(STDIN, 3);
    }

    /**
     * Handles key inputs to navigate or select items in a list.
     *
     * Updates the current position based on the key pressed (UP or DOWN)
     * or triggers actions on certain keys (NEW_LINE or CARRIAGE_RETURN).
     * Clears the screen and outputs the selected item when a selection is confirmed.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the provided key is not recognized.
     */
    protected function handleNavigation(): void {
        switch ($this->key) {
            case self::UP:
                $this->current = ($this->current - 1 + count($this->options)) % count($this->options);
                break;

            case self::DOWN:
                $this->current = ($this->current + 1) % count($this->options);
                break;

            case self::NEW_LINE:
            case self::CARRIAGE_RETURN:
                $this->screen->clear();
                echo "Selected: {$this->options[$this->current]}\n";
            //                exit(0);
        }
    }

    /**
     * Handles the selection process based on key input.
     *
     * Confirms the selection if the first key matches NEW_LINE or CARRIAGE_RETURN
     * and clears the screen upon selection.
     *
     * @return bool Returns true if a selection is confirmed, false otherwise.
     *
     * @throws \RuntimeException If the screen object is not properly configured or unavailable.
     */
    protected function handleSelect(): bool {
        if ($this->key[0] === self::NEW_LINE || $this->key[0] === self::CARRIAGE_RETURN) {
            $this->screen->clear();

            return true;
        }

        return false;
    }

    /**
     * Retrieves the currently selected item from the list.
     *
     * Returns the item at the current position in the list of items.
     *
     * @return string|SelectOption The currently selected item.
     *
     */
    protected function selectedItem(): string|SelectOption {
        return $this->options[$this->current];
    }

    /**
     * Displays the interactive screen for the user to navigate and select an item.
     *
     * Manages the screen rendering, input handling, and selection process in a loop
     * until an item is selected. Restores terminal settings upon completion.
     *
     * @return string|SelectOption The selected item.
     *
     */
    public function display(): string|SelectOption {
        $this->screen->escapeANSI();
        while(true) {
            $this->screen->clear();
            echo $this->render();
            $this->readKey();
            $this->handleNavigation();
            if ($this->handleSelect()) break;
        }
        $this->screen->escape();

        return $this->selectedItem();
    }

    /**
     * Invokes the object as a callable to display the content.
     *
     * @return string|SelectOption The output generated by the display method.
     *
     */
    public function __invoke(): string|SelectOption {
        return $this->display();
    }
}
