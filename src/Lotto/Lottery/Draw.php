<?php

/**
 * Inane: Lotto
 *
 * Lotto.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
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

use Exception;
use Inane\Cli\Pencil;
use Inane\Stdlib\Exception\RuntimeException;
use Stringable;

use function array_key_exists;
use function str_replace;

/**
 * Draw
 *
 * Represents a draw result for a specific lottery type.
 *
 * This class encapsulates details of a draw, including the draw number, date, day,
 * placement, winnings, and associated lottery type.
 *
 * @version 0.1.0
 */
class Draw implements Stringable {
    /**
     * Retrieves the pencil instance.
     *
     * @return Pencil The pencil instance.
     *
     * @throws RuntimeException If the pencil cannot be instantiated.
     */
    protected Pencil $pencil {
        get => $this->pencil ??= new Pencil();
    }

    /**
     * @var string $pending The pending status formatted in yellow.
     *
     * @throws Exception If there is an error during the formatting process.
     */
    protected string $pending {
        get => $this->pending ??= $this->pencil->format(Pencil\Colour::Yellow->text('pending'));
    }

    /**
     * @var int $number The draw number.
     */
    private(set) int $number;
    /**
     * @var string $date The draw date.
     */
    private(set) string $date;
    /**
     * @var string $day The day of the week the draw occurred.
     */
    private(set) string $day;
    /**
     * @var string $placement The placement of the draw result.
     */
    private(set) string $placement;
    /**
     * @var float $won The winnings for the draw result.
     */
    private(set) float $won = -1;

    /**
     * Create a new Draw instance from an array of data.
     *
     * @param LottoType $type The lottery type associated with the draw.
     * @param array $data An associative array containing draw details.
     *
     * @return static A new Draw instance with the provided data.
     */
    public static function fromArray(LottoType $type, array $data): static {
        $static = new static($type);
        foreach (['number', 'date', 'day', 'placement', 'won'] as $prop) {
            if (array_key_exists($prop, $data)) $static->$prop = $data[$prop];
        }

        return $static;
    }

    /**
     * Create a new Draw instance.
     *
     * @param LottoType $type The lottery type associated with the draw.
     */
    public function __construct(private(set) readonly LottoType $type) {
    }

    /**
     * Get the draw as a string.
     *
     *
     * @return string The draw as a string.
     * @throws RuntimeException
     */
    public function __toString(): string {
        $fmt = Lotto::getNumberFormatter('r2', ['pattern' => '¤ #00.00']);

        /*
        Wednesday theme
        This works when list starts on a wednesday
        last is also 1 letter shorter
        */
        if ($this->type === LottoType::Lotto)
            $gap = $this->day === 'saturday' ? ' ' : '';
        elseif ($this->type === LottoType::PowerBall)
            $gap = $this->day === 'friday' ? ' ' : '';

        $won = $this->won < 0 ? $this->pending : str_replace('R 0', 'R  ', $fmt->format($this->won));
        return "$this->number: $this->placement $this->date ($this->day)$gap won: $won";
    }
}
