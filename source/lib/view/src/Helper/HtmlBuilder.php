<?php

/**
 * doc
 *
 * Description: doc
 *
 * PHP version 8.5
 *
 * @version $Id$
 * $Date$
 * @license UNLICENSE doc
 * @license https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @author  Philip Michael Raab<peep@inane.co.za>
 *
 */

declare(strict_types = 1);

namespace Inane\View\Helper;

use Stringable;

use function count;
use function implode;
use function is_array;
use function preg_match_all;
use function str_ireplace;

use const PREG_SET_ORDER;

class HtmlBuilder implements Stringable {
    private array $options = [
        'doctype' => 'HTML',
        'lang' => 'en',
    ];

    /**
     * @var array stored html structure commands
     */
    private array $elementsList = [];

    /**
     * HtmlBuilder Constructor
     *
     * @param string $stylesheetPosition location where to add style sheets
     */
    public function __construct(
        private readonly string $stylesheetPosition = 'footer',
    ) {}

    /**
     * Returns html string
     *
     * @return string the rendered html
     */
    public function __toString(): string {
        return $this->render();
    }

    /**
     * Kicks off a new build process
     *
     * @param string $stylesheetPosition
     *
     * @return static HtmlBuilder
     */
    public static function create(string $stylesheetPosition = 'footer'): static {
        return new static($stylesheetPosition);
    }

    /**
     * Parses the method to determine the tag and action.
     *
     * @param string $calledMethod
     *
     * @return (string|\Inane\View\Helper\Action)[]
     */
    protected function parseMethodName(string $calledMethod): array {
        preg_match_all('/(?<tag>[a-z]+)(?<action>Start|End)?/', $calledMethod, $matches, PREG_SET_ORDER);
        @[
            'tag'    => $tag,
            'action' => $action,
        ] = $matches[0] + ['action' => 'None'];
        $action = $action ? Action::tryFromName($action, true) : Action::None;

        return [
            $tag,
            $action,
        ];
    }

    /**
     * Render HTML string
     *
     * @return string html string
     */
    public function render(): string {
        $stylesList = [];
        $output = "<!DOCTYPE {$this->options['doctype']}><html lang=\"{$this->options['lang']}\">";

        foreach($this->elementsList as $currElement) {
            $parameters = $currElement[1][0] ?? '';
            $paramsList = [];
            $params = '';
            $currStyle = '';
            $currSelector = '';

            if (is_array($parameters)) {
                foreach($parameters as $key => $val) {
                    if ($key === 'styleSelector')
                        $currSelector .= $val;
                    elseif ($key === 'style') {
                        $styleValues = [];
                        foreach($val as $k => $v) $styleValues[] = "$k:$v";

                        $currStyle = implode(';', $styleValues);
                    } // elseif ($key === 'optimize') ; //code to be done
                    else $paramsList[] = "$key=\"$val\"";
                }
                $params = ' ' . implode(' ', $paramsList);
            }

            [
                $tag,
                $action,
            ] = $this->parseMethodName($currElement[0]);

            if ($currSelector === '' && $currStyle !== '') $params .= " style=\"$currStyle\"";
            elseif ($currSelector !== '' && $currStyle !== '') $stylesList[$currSelector] = $currStyle;

            if ($action !== Action::None) {
                if ($action === Action::Start) {
                    $output .= '<' . $tag . $params . '>';
                    if ($tag === 'head') $haveHeader = true;
                } elseif ($action === Action::End) {
                    if ($tag === 'head' && $this->stylesheetPosition === 'head') $output .= '[__CssContentPlaceholder__]';
                    elseif ($tag === 'body' && $this->stylesheetPosition === 'footer') $output .= '[__CssContentPlaceholder__]';
                    $output .= '</' . $tag . '>';
                }
            } else {
                if ($tag === 'contents') $output .= $currElement[1][0];
                else $output .= '<' . $tag . $params . ' />';
            }
        }
        $cssCode = '<style>';
        if (count($stylesList) > 0) {
            foreach($stylesList as $sKey => $sVal) {
                if ($sKey === '' || $sVal === '') continue;
                $cssCode .= $sKey . '{ ' . $sVal . ' }';
            }
        }
        $cssCode .= '</style>';

        // replace placeholder with actual css code
        $output = str_ireplace('[__CssContentPlaceholder__]', $cssCode, $output);

        $output .= '</html>';

        return $output;
    }

    /**
     * Method Handler
     *
     * @param string $name      method name
     * @param array  $arguments method arguments
     *
     * @return mixed $this
     */
    public function __call(string $name, array $arguments): mixed {
        $this->elementsList[] = [
            $name,
            $arguments,
        ];

        return $this;
    }
}
