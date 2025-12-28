<?php

/**
 * Inane: View
 *
 * View layer with models for the most common content types.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\view
 * @category view
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\View;

use function ob_get_clean;
use function ob_start;

/**
 * Layout-capable HTML view.
 *
 * Provides simple block capturing utilities (`startBlock`/`endBlock`) to allow
 * templates to define and later render named content regions within a layout.
 */
class LayoutView extends HtmlView {
    /**
     * Captured blocks keyed by their names.
     *
     * @var array<string, string|null>
     */
    protected array $blocks = [];
    
    /**
     * Starts capturing output for a named block.
     */
    public function startBlock(string $name): void {
        ob_start();
        $this->blocks[$name] = null;
    }
    
    /**
     * Ends capturing output for a named block.
     */
    public function endBlock(string $name): void {
        $this->blocks[$name] = ob_get_clean();
    }
    
    /**
     * Returns the content of a named block, or a default value if unset.
     */
    public function block(string $name, string $default = ''): string {
        return $this->blocks[$name] ?? $default;
    }
}