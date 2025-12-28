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

namespace Inane\Lotto;

use DateTime;
use ReflectionMethod;
use Stringable;
use Inane\Stdlib\{
    Array\OptionsInterface,
    Exception\DateMalformedStringException,
    Exception\ReflectionException,
    Options
};

use function array_combine;
use function array_map;
use function array_sum;
use function count;
use function func_get_args;
use function in_array;
use function str_pad;
use function strtolower;

use const null;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;
use const true;

/**
 * Ticket
 *
 * Represents a lottery ticket and its associated information including draws, winnings, and type.
 *
 * @version 0.1.0
 */
class Ticket implements Stringable {
    /**
     * @var int $expireTime The timestamp when the ticket expires.
     */
    private static int $expireTime;
    /**
     * @var string $first The date of the ticket's first draw.
     */
    public readonly string $first;
    /**
     * @var string $last The date of the ticket's last draw.
     */
    public readonly string $last;
    /**
     * @var int $drawn The number of draws the ticket has had.
     */
    public readonly int $drawn;
    /**
     * @var int $remain The number of draws remaining for the ticket.
     */
    public int $remain {
        get => $this->draws - $this->drawn;
    }
    /**
     * @var bool $expired Whether the ticket has expired.
     */
    public bool $expired {
        get => $this->remain <= 0;
    }
    /**
     * @var array $days The days of the week the ticket is valid for.
     */
    public array $days {
        get => $this->type->days();
    }
    /**
     * @var array $ticketDraws An array of Draw objects representing the ticket's draws.'
     */
    private array $ticketDraws = [];

    /**
     * @var float|int $total The total winnings for the ticket.
     */
    private(set) float $total = 0;
    /**
     * @var bool $hasWon Whether the ticket has winnings.
     */
    public bool $hasWon {
        get => (bool) $this->total;
    }

    /**
     * @var float $profit The profit or loss made from the ticket.
     */
    public float $profit {
        get => $this->price === null ? -0 : $this->total - $this->price;
    }

    /**
     * @var OptionsInterface $storage An instance of OptionsInterface for storing ticket options.
     */
    private OptionsInterface $storage {
        get => isset($this->storage) ? $this->storage : ($this->storage = new Options());
        set => $this->storage = $value;
    }

    /**
     * Create a new Ticket instance.
     *
     * @param string $bought The date the ticket was bought.
     * @param LottoType $type The type of lottery the ticket is for.
     * @param int $draws The number of draws the ticket has.
     * @param int|null $lines The number of lines or numbers on the ticket.
     * @param float|null $price The price of the ticket.
     * @param array $winnings An array of winnings for each draw.
     */
    public function __construct(
        public readonly string $bought,
        public readonly LottoType $type,
        public readonly int $draws,
        public ?int $lines = null,
        public ?float $price = null {
            get => $this->price;
            set => $this->price = $value;
        },
        array $winnings = [],
    ) {
        $this->bootstrap($winnings);
        $this->parseTicket($winnings);
    }

    /**
     * Bootstrap the ticket with winnings.
     *
     * @param array $winnings Array of winnings for each draw.
     *
     * @return void
     */
    private function bootstrap(array $winnings): void {
        if (!isset($this::$expireTime)) $this::$expireTime = new DateTime()->setTime(Lotto::lottoTime, 0, 0)->getTimestamp();
        $this->total = array_sum($winnings);
    }

    /**
     * Create a draw object and add it to the ticket's draw array.
     * '
     * @param int $number The draw number.
     * @param string $date The draw date.
     * @param string $day The day of the week the draw occurred.
     * @param string $placement The placement of the draw result.
     * @param float $won The winnings for the draw result.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    private function createDraw(int $number, string $date, string $day, string $placement, float $won) {
        $values = func_get_args();
        $keys = array_map(fn($arg) => $arg->name, new ReflectionMethod($this::class, __FUNCTION__)->getParameters());
        $params = array_combine($keys, $values);

        $this->ticketDraws[$date] = Draw::fromArray($this->type, $params);
    }

    /**
     * Parse the ticket and create draws based on the winnings.
     *
     * @param array $winnings Array of winnings for each draw.
     *
     * @return void
     *
     * @throws DateMalformedStringException
     * @throws ReflectionException
     */
    private function parseTicket(array $winnings): void {
        if (!isset($this->last)) {
            $date = new DateTime($this->bought);
            $weekDays = array_map('strtolower', $this->type->days());
            $drawn = $count = 0;

            while (true) {
                $dayName = strtolower($date->format('l'));

                if (in_array($dayName, $weekDays, true)) {
                    if (!isset($this->first)) $this->first = $date->format('Y-m-d');
                    $pending = $date->getTimestamp() < $this::$expireTime;

                    $this->createDraw($count + 1, $date->format('Y-m-d'), $dayName, $count == 0 ? 'start' : ($count + 1 == $this->draws ? 'last' : 'midst'), $pending ? (count($winnings) > $count ? $winnings[$count] : 0) : -1);

                    $count++;
                    if ($pending) $drawn = $count;
                    if ($count === $this->draws) {
                        $this->last = $date->format('Y-m-d');
                        break;
                    }
                }

                $date->modify('+1 day');
            }

            $this->drawn = $drawn;
        }
    }

    /**
     * Get the draws for the ticket.
     *
     * @return array Array of Draw objects representing the ticket's draws.'
     */
    public function getDraws(): array {
        return $this->ticketDraws;
    }

    /**
     * Get the ticket as a string.
     *
     * @return string The ticket as a string.
     */
    public function __toString(): string {
        $fmt = Lotto::getNumberFormatter('r3', ['pattern' => '¤ #000.00']);
        $total = str_replace(['R 0'], ['R  '], $fmt->format($this->total));
        $profit = str_replace(['R 0'], ['R  '], $fmt->format($this->profit));
        // $total = $fmt->format($this->total);
        // $profit = $fmt->format($this->profit);
        
        $s = $this->type->name;
        $s .= ' (' . implode(', ', $this->type->days()) . ')';
        $s .= static::padLine(static::formatNumber($this->draws) . (isset($this->lines) ? '[' . static::formatNumber($this->lines) . ']' : ''));
        $s .= ': [' . $this->bought . ']';
        $s .= ' ' . $this->first;
        $s .= ' => ' . $this->last;
        $s .= ':' . static::padLine(($this->expired ? 'expired' : static::formatNumber($this->remain, 1) . ' draw' . ($this->remain == 1 ? '' : 's') . ' left'));
        $s .= '! Won so far: ' . $total . ($this->expired ? ("> Profit: {$profit}") : '');
        // $s .= '! Won so far: R' . static::formatNumber($this->total, 3, ' ', STR_PAD_RIGHT) . ($this->expired ? ("> Profit: {$this->profit}") : '');
        return $s;
    }

    /**
     * Format a number with padding and character.
     *
     * @param int|float $number The number to format.
     * @param int $length The length of the formatted number.
     * @param string $char The character to use for padding.
     * @param int $side The side of the string to pad.
     *
     * @return string The formatted number.
     */
    protected static function formatNumber(int|float $number, int $length = 2, string $char = '0', int $side = STR_PAD_LEFT): string {
        return str_pad((string)$number, $length, $char, $side);
    }

    /**
     * Pad a line with a space if it doesn't start with one.
     *
     * @param string $line The line to pad.
     *
     * @return string The padded line.
     */
    protected static function padLine(string $line): string {
        return ($line[0] === ' ' ? '' : ' ') . $line;
    }
}