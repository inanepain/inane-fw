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
    Pencil};
use Knot\Application;
use Knot\Lotto\Lottery\Lotto;

use function array_map;
use function implode;

/**
 * Lottery console commands.
 */
class LottoCommands {
    protected Pencil $pencil {
        get => $this->pencil ??= new Pencil();
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

        $this->pencil->line(Pencil\Colour::Red->text('## Expired', Pencil\Style::Bold));
        Cli::line((string)$lotto);

        $this->pencil->divider();
        $this->pencil->line(Pencil\Colour::Blue->text('## Current', Pencil\Style::Bold));
        $once = new Step();

        foreach ($lotto->getTickets($lotto::ACTIVE) as $ticket) {
            // Draw a divider between current ticket entries.
            if ($once()) $this->pencil->divider('-');
            Cli::line((string)$ticket);
            Cli::line('  ' . implode("\n  ", array_map('strval', $ticket->getDraws())));
        }

        return 0;
    }
}
