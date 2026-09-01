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

use Exception;
use Inane\{
    Console\Command\Command,
    Console\Command\Option,
    Stdlib\Thing\Step};
use Inane\Cli\{
    Cli,
    Pencil};
use Knot\Application;
use Knot\Lotto\Lottery\Lotto;
use Knot\Lotto\Lottery\Ticket;
use RuntimeException;

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
     * Executes the command to provide an overview of lottery tickets.
     *
     * @param bool $current Indicates whether to display current (active) tickets.
     * @param bool $expired Indicates whether to display expired tickets.
     * @param bool $details Indicates whether to include detailed ticket draw information.
     *
     * @return int Returns the execution status code of the command.
     *
     * @throws RuntimeException If there is an issue with the lottery configuration.
     * @throws \Exception If there is a general error executing the command.
     */
    #[Command('lotto:filter', 'Overview of lottery', ['lo'])]
    public function filterCommand(
        #[Option('current', 'c', 'Current tickets', valueless: true)]
        bool $current = false,

        #[Option('expired', 'x', 'Expired tickets', valueless: true)]
        bool $expired = false,

        #[Option('details', 'd', 'Ticket draw details', valueless: true)]
        bool $details = false,
    ): int {
        $display = Lotto::NONE;
        if ($current) $display |= Lotto::ACTIVE;
        if ($expired) $display |= Lotto::EXPIRED;

        $lotto = Lotto::fromArray(Application::app()->config->getConfig(Lotto::class) ?? []);
        $lotto->display = $display;

        $this->pencil->line(Pencil\Colour::Blue->text('## Overview', Pencil\Style::Bold));
        Cli::line((string)$lotto);

        $expiredHeader = new Step();
        $currentHeader = new Step();

        /** @var Ticket $ticket */
        if ($details) foreach($lotto->getTickets($display) as $ticket) {
            if ($ticket->expired && $expiredHeader()) {
                $this->pencil->divider();
                $this->pencil->line(Pencil\Colour::Red->text('## Expired', Pencil\Style::Bold));
                $this->pencil->divider('-');
            }
            if (!$ticket->expired && $currentHeader()) {
                $this->pencil->divider();
                $this->pencil->line(Pencil\Colour::Green->text('## Active', Pencil\Style::Bold));
                $this->pencil->divider('-');
            }
            Cli::line((string)$ticket);
            Cli::line('  ' . implode("\n  ", array_map('strval', $ticket->getDraws())));
        }

        return 0;
    }

    /**
     * Executes the command to view the current tickets.
     *
     * @param bool $details Indicates whether to include ticket draw details.
     *
     * @return int Returns the execution status code of the command.
     *
     * @throws RuntimeException|Exception If there is an error executing the command.
     */
    #[Command('lotto:current', 'View CurrentTickets', ['lc'])]
    public function currentTicketsCommand(
        #[Option('details', 'd', 'Ticket draw details', valueless: true)]
        bool $details = false,
    ): int {
        return $this->filterCommand(current: true, details: $details);
    }
}
