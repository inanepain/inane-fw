<?php

/**
 * Inane: Lotto
 *
 * Lotto.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\lotto
 * @category lotto
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Knot\Lotto\Lottery;

use Inane\Config\ConfigAwareTrait;
use Inane\Datetime\Timestamp;
use Inane\Stdlib\{
    Array\OptionsInterface,
    Exception\InvalidArgumentException,
    Exception\RuntimeException,
    Options};
use Inane\Stdlib\String\NumberFormatterTrait;
use NumberFormatter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Stringable;
use function array_map;
use function array_unshift;
use function count;
use function implode;
use function is_array;
use function is_string;
use const null;
use const PHP_EOL;

/**
 * Lotto
 *
 * The Lotto class is responsible for managing lotto tickets and their related operations.
 * It provides methods for adding tickets, filtering tickets, displaying active or expired tickets,
 * and converting the object state to a string representation.
 *
 * @version 0.1.0
 */
class Lotto implements Stringable {
    use ConfigAwareTrait;
    use NumberFormatterTrait;

    #region Constants
    /**
     * Represents a constant value for "NONE", typically used to designate a state or flag with no value or an initial/default state.
     *
     * @var int
     */
    public const int NONE = 0;
    /**
     * @var int ACTIVE Represents the active state with a value of 1.
     */
    public const int ACTIVE = 1;
    /**
     * Indicates the expired status with a value of 2.
     *
     * This constant can be used to represent a state or condition where something is no longer valid or active.
     * Commonly associated with entities that have a time-sensitive lifecycle.
     *
     * @var int
     */
    public const int EXPIRED = 2;
    #endregion Constants

    protected array $defaultConfig = [
        'display' => [
            'active' => true,
            'expired' => true,
        ],
        'tickets' => [],
    ];

    protected static NumberFormatter $numberFormatter;

    /**
     * Represents a collection of tickets.
     */
    private OptionsInterface $tickets;
    /**
     * Represents the time for the lottery draw, specified in hours (24-hour format).
     */
    public const int lottoTime = 21;
    /**
     * Retrieves the total count of tickets.
     */
    public int $total {
        get => $this->tickets->count();
    }
    /**
     * Holds the default value for the Lotto variable.
     */
    private int $Lotto = 0;
    /**
     * Initializes the PowerBall value to zero.
     */
    private int $PowerBall = 0;

    /**
     * Combines the ACTIVE and EXPIRED states for display purposes.
     */
    public int $display = self::ACTIVE | self::EXPIRED;

    /**
     * Creates an instance of the class and populates it with the given data.
     *
     * @param array|OptionsInterface $data Data to initialize the object with.
     *
     * @return static An instance of the class loaded with the provided data.
     */
    public static function fromArray(array|OptionsInterface $config = []): static {
        return new static($config);
    }

    /**
     * Constructor for initializing the class and setting up required properties.
     *
     * Initializes the tickets property with a new instance of Options.
     *
     * @return void
     */
    public function __construct(array|OptionsInterface $config = []) {
        $this->setConfig($config);
        $this->initialise();
    }

    /**
     * Initialize the ticket options and configuration settings.
     *
     * This method sets up the ticket options and checks for pre-configured
     * ticket data in the provided configuration. If ticket data is present
     * in the configuration, it will be added to the initialized options.
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function initialise(): void {
        $this->tickets = new Options();

        if ($this->config->has('tickets') && $this->config->tickets->count() > 0) {
            $this->addTickets($this->config->get('tickets'));
        }

        $this->display = 0;
        $this->display |= $this->config->display->active ? self::ACTIVE : 0;
        $this->display |= $this->config->display->expired ? self::EXPIRED : 0;
    }

    /**
     * Enables the display of expired items.
     *
     * @return self The current instance with expired display setting applied.
     */
    public function showExpired(): self {
        $this->display |= static::EXPIRED;
        return $this;
    }

    /**
     * Removes expired items from being displayed.
     *
     * Modifies the display property to exclude expired items.
     *
     * @return self The instance of the current object for method chaining.
     */
    public function hideExpired(): self {
        $this->display &= ~static::EXPIRED;
        return $this;
    }

    /**
     * Adds multiple tickets to the collection.
     *
     * @param array|OptionsInterface $tickets The tickets to add.
     *
     * @return self This instance, for chaining.
     */
    public function addTickets(array|OptionsInterface $tickets): self {
        if (!is_array($tickets)) {
            $tickets = $tickets->toArray();
        }

        array_map([$this, 'addTicket'], $tickets);
        return $this;
    }

    /**
     * Adds a ticket to the collection.
     *
     * This method accepts either an instance of a Ticket or an array of ticket details to create a new Ticket instance.
     * It ensures that each ticket is uniquely identified based on its purchase date and resolves conflicts by incrementing the ID.
     * Once added, it updates the count of tickets for the corresponding type.
     *
     * @param Ticket|array $ticket The ticket to add. If an array is provided, it should contain the necessary details to construct a Ticket instance.
     *
     * @return self The current instance, allowing for method chaining.
     *
     * @throws RuntimeException
     */
    public function addTicket(Ticket|array $ticket): self {
        if (is_array($ticket)) {
            if (is_string($ticket[1]))
                $ticket[1] = LottoType::tryFromName($ticket[1]);
            $ticket = new Ticket(...$ticket);
        }

        $id = Timestamp::createFromFormat('Y-m-d', $ticket->bought)->seconds;
        while ($this->tickets->offsetExists($id))
            $id++;

        $this->tickets->offsetSet($id, $ticket);

        $this->{$ticket->type->name}++;
        return $this;
    }

    /**
     * Get tickets
     *
     * @param null|int $display
     *
     * @return Options<\Ticket> Array of tickets.
     *
     * @throws InvalidArgumentException
     */
    public function getTickets(?int $display = null): OptionsInterface {
        $display = $display === null ? $this->display : $display;
        $showActive = ($display & static::ACTIVE) === static::ACTIVE;
        $showExpired = ($display & static::EXPIRED) === static::EXPIRED;

        $tickets = new Options();
        /** @var \Ticket $ticket */
        foreach (new Options($this->tickets) as $id => $ticket) {
            if ($showExpired && $ticket->expired) $tickets->set($id, $ticket);
            if ($showActive && !$ticket->expired) $tickets->set($id, $ticket);
        }
        return $tickets;
    }

    /**
     * Filter tickets to get the ones you want.
     *
     * terms:
     * - p.date: purchase date
     * - d.date: draw date
     * - type: PB or L
     * - won: true/false
     * - expired: true/false
     * - draws: number of draws
     * - lines: number of lines or numbers on ticket
     *
     * draws & lines:
     * - exact: give it a number (5)
     * - between: two numbers ([4, 7])
     * - less: (['<', 11])
     * - more: (['>', 5])
     *
     * @param array $query search terms, leave empty for all tickets.
     *
     * @return array filtered tickets.
     *
     * @throws InvalidArgumentException
     */
    public function filterTickets(array $query = []): array {
        /**
         * @var Options<string, null|bool|int|array>
         */
        $opts = new Options([
            'p.date' => null,
            'd.date' => null,
            'type' => null,
            'won' => null,
            'expired' => null,
            'draws' => null,
            'lines' => null,
        ]);
        $opts->defaults($query);

        $tickets = $this->getTickets($opts->expired);
        $opts->unset('expired');

        foreach ($tickets as $index => $ticket) {
            foreach ($opts as $cmd => $qry) {
                if ($qry === null) continue;

                if ($cmd === 'p.date')
                    if ($qry != $ticket->bought) {
                        $tickets->unset($index);
                        break;
                    }
                if ($cmd = 'd.date') {
                    // if draw date is set match.
                }
            }
        }

        return [];
    }

    /**
     * Converts the object to its string representation.
     *
     * The string representation includes a summary of the ticket details,
     * such as the total number of tickets, lotto count, and power ball count,
     * followed by the details of each ticket.
     *
     * @return string The string representation of the object.
     *
     * @throws InvalidArgumentException
     */
    public function __toString(): string {
        $s = array_map('strval', $this->getTickets()->toArray());
        $e = count($s);
        array_unshift($s, 'Lotto Tickets: ' . (string)$e . '/' . (string)$this->total . ' (lotto: ' . (string)$this->Lotto . ' / power ball: ' . (string)$this->PowerBall . ')');

        return implode(PHP_EOL, $s);
    }
}
