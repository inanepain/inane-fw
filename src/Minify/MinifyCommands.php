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

namespace Knot\Minify;

use Inane\Cli\Cli;
use Inane\Cli\Pencil;
use Inane\Console\Command\Command;
use Inane\Console\Router\AbstractCommandController;
use Inane\File\File;
use Inane\File\Path;
use MatthiasMullie\Minify;

/**
 * Handles command-line tools for minifying JavaScript libraries.
 */
class MinifyCommands extends AbstractCommandController {
    /**
     * Executes the `minify:extend` command to process and minify JavaScript files in the specified directory.
     * This includes removing old minified files, combining and minifying multiple source files, and generating
     * new minified output.
     *
     * @return int Returns 0 to indicate successful execution of the command.
     */
    #[Command('minify:extend', 'Minify the extend library', ['em'])]
    public function extendCommand(): int {
        $default = new Pencil();
        $blue = new Pencil(Pencil\Colour::Blue);

        $blue->line('=== Inane Extend ===');
        $path = new Path('public/js/inane/extend');
        foreach ($path->getFiles('*.min.*js') as $file) {
            $file->remove();
            Cli::line('Remove: ' . (string)$file);
        }

        $default->divider();

        $blue->line('Minifying extend library...');
        $minifier = new Minify\JS();
        $extend = '';
        foreach ($path->getFiles('*.*js') as $file) {
            Cli::out('Minifying: ' . (string)$file . '...');

            if (!str_ends_with((string)$file, '.mjs') && !str_ends_with((string)$file, 'extend.js')) {
                $extend .= $file->read() . PHP_EOL;
                $minifier->add((string)$file);
            }
            new Minify\JS((string)$file)->minify(str_replace(['.js', '.mjs'], ['.min.js', '.min.mjs'], (string)$file));

            Cli::line(' done');
        }

        $default->divider();

        $blue->line('Creating: extend.js');
        $file = new File('public/js/inane/extend/extend.js');
        $file->remove();
        $file->write($extend);

        $blue->out('Minifying: extend.js...');
        $minifier->minify('public/js/inane/extend/extend.min.js');
        Cli::line(' done');

        return 0;
    }
}
