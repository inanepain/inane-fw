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
 */

declare(strict_types = 1);

namespace Knot\Lotto;

use Inane\{
    Console\Command\Command,
    Stdlib\Thing\Step};
use Inane\Cli\{
    Cli,
    Pencil,
    Shell};
use Knot\Application;
use Knot\Lotto\Lottery\Lotto;

use function array_map;
use function implode;
use function str_repeat;

/**
 * Lottery console commands.
 */
class LottoCommands {
    /**
     * Print a horizontal divider using the current terminal width.
     *
     * @param string       $divider Divider character to repeat.
     * @param Pencil|null  $pencil Optional pencil instance to write with.
     *
     * @return Pencil
     */
    public function divider(string $divider = '=', ?Pencil $pencil = null): Pencil {
        // Build the divider line to match the active shell width.
        $text = str_repeat($divider, Shell::columns());
        if ($pencil === null) {
            $pencil = new Pencil();
        }

        return $pencil->line($text);
    }

    /**
     * Displays an overview of the lottery, listing both expired and current tickets.
     *
     * @return int The exit status code (0 on success).
     *
     * @throws \Exception If the configuration for {@see Lotto} cannot be retrieved or parsed.
     */
    #[Command('lotto:view', 'Overview of lottery', ['lo'])]
    public function lottoCommand(): int {
        $lotto = Lotto::fromArray(Application::app()->config->getConfig(Lotto::class));

        Cli::line('## Expired');
        Cli::line((string)$lotto);

        $this->divider('*');
        Cli::line('## Current');
        $once = new Step();

        foreach ($lotto->getTickets($lotto::ACTIVE) as $ticket) {
            // Draw a divider between current ticket entries.
            if ($once()) $this->divider('-');
            Cli::line((string)$ticket);
            Cli::line('  ' . implode("\n  ", array_map('strval', $ticket->getDraws())));
        }

        return 0;
    }
}
