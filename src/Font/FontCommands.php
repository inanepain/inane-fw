<?php

/**
 * FontCommands
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\font
 * @category font
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Knot\Font;

use Inane\Cli\Cli;
use Inane\Console\Command\Command;

/**
 * FontCommands
 *
 * inane-fw
 *
 * @version 0.1.0
 */
class FontCommands {
    protected function testOne(): int {
        $fl = new FontLibrary();
        $fl->load();

        Cli::line("Libray: size: {$fl->size}");

        $results = $fl->search('Helvetica');
        Cli::line("Search: results: {$results->size}");
        //        dd($results->list());

        $f1 = $results->getFont('Helvetica');
        //        dd($f1->toArray());
        //        dd($results->toArray());

        $results->save('data/fonts-search.json');

        return 0;
    }

    protected function testTwo(): int {
        $fl = new FontLibrary();
        $fl->readFile('data/fonts-search.json');

        Cli::line("Libray: size: {$fl->size}");

        return 0;
    }

    /**
     * Executes the `font:library` command, which provides information about the font library.
     *
     * Retrieves the state of the font library, displays its size, and performs a search
     * operation for the font 'Helvetica', displaying the number of search results.
     *
     * @return int The exit status of the command.
     */
    #[Command('font:library', 'Font library states', ['fl'])]
    public function infoCommand(): int {
        return $this->testOne();
        //        return $this->testTwo();
    }
}
