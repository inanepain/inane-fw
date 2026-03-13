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

use function array_merge;
use function extract;
use function ob_get_clean;
use function ob_start;

/**
 * HTML view that renders PHP templates and optional layouts.
 *
 * Supports partials and components via helper closures exposed to templates,
 * and can nest other `View` instances, which will be rendered first.
 */
class HtmlView extends View {
    /** @var string HTTP content type produced by this view */
    protected string $contentType = 'text/html';

    /** @var string|null Optional layout name to wrap the page */
    protected ?string $layout = null;

    /**
     * Named sections captured during template rendering.
     *
     * @var array<string, string>
     */
    protected array $sections = [];

    /**
     * @param string                 $template Template identifier
     * @param array<string, mixed>   $data     Initial data
     */
    public function __construct(string $template, array $data = []) {
        parent::__construct($data);
        $this->template = $template;
    }

    /**
     * Sets the layout to wrap the rendered content.
     *
     * @return $this
     */
    public function setLayout(string $layout): self {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Defines a named section content to be used by a layout.
     *
     * @return $this
     */
    public function section(string $name, string $content): self {
        $this->sections[$name] = $content;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(): string {
        // Process nested views first
        $processedData = [];
        foreach ($this->data as $key => $value) {
            $processedData[$key] = $value instanceof View
                ? $value->render()
                : $value;
        }

        // Extract data for template
        extract($processedData);

        // Make helper functions available
        $partial = function(string $name, array $data = []) {
            return $this->renderPartial($name, $data);
        };

        $component = function(string $name, array $data = []) {
            return $this->renderComponent($name, $data);
        };

        // Render main template
        ob_start();
        include TemplateResolver::resolve($this->template);
        $content = ob_get_clean();

        // Wrap in layout if specified
        if ($this->layout) {
            ob_start();
            include TemplateResolver::resolve("layouts:{$this->layout}");
            return ob_get_clean();
        }

        return $content;
    }

    /**
     * Renders a partial template with merged view and local data.
     */
    private function renderPartial(string $name, array $data = []): string {
        extract(array_merge($this->data, $data));
        ob_start();
        include TemplateResolver::resolve("partials:{$name}");
        return ob_get_clean();
    }

    /**
     * Renders a component template with local data only.
     */
    private function renderComponent(string $name, array $data = []): string {
        extract($data);
        ob_start();
        include TemplateResolver::resolve("components:{$name}");
        return ob_get_clean();
    }
}
