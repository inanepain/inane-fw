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

use Inane\Cli\Cli;
use Inane\Cli\Pencil;
use Inane\Console\Command\Command;
use Inane\Stdlib\Thing\Step;
use Knot\Application;
use Knot\Lotto\Lottery\Lotto;
use function str_repeat;

class LottoCommands {
    public function divider(string $divider = '=', ?Pencil $pencil = null): Pencil {
        $text = str_repeat($divider, \Inane\Cli\Shell::columns());
        if ($pencil === null) {
            $pencil = new Pencil();
        }
        return $pencil->line($text);
    }

    #[Command('lotto:view', 'Overview of lottery', ['lo'])]
    public function lottoCommand(): int {
        $lotto = Lotto::fromArray(Application::app()->config->getConfig(Lotto::class));

        Cli::line('## Expired');
        Cli::line((string)$lotto);

        $this->divider('*');
        Cli::line('## Current');
        $once = new Step();

        foreach ($lotto->getTickets($lotto::ACTIVE) as $ticket) {
            if ($once()) $this->divider('-');
            Cli::line((string)$ticket);
            Cli::line('  ' . implode("\n  ", array_map('strval', $ticket->getDraws())));
        }

        return 0;
    }
}
