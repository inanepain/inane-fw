<?php

/**
 * doc
 *
 * Description: doc
 *
 * PHP version 8.1
 *
 * @author Philip Michael Raab<peep@inane.co.za>
 *
 * @license UNLICENSE doc
 * @license https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Html\Helper;

use Stringable;

use function count;
use function implode;
use function preg_match_all;
use function str_ireplace;

class HtmlBuilder implements Stringable {
	private array $options = [
		'doctype' => 'html',
	];

	/**
	 * @var array stored html structure commands
	 */
	private array $elementsList = [];

	/**
	 * HtmlBuilder Constructor
	 *
	 * @param string $stylePos location where to add style sheets
	 */
	public function __construct(
		private string $stylePos = 'footer',
	) {
	}

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
	 * @param string $stylePos
	 *
	 * @return static HtmlBuilder
	 */
	public static function create(string $stylePos = 'footer'): static {
		return new static($stylePos);
	}

	/**
	 * Parses the method to determine the tag and action.
	 *
	 * @param string $calledMethod
	 *
	 * @return (string|\Inane\Html\Helper\Action)[]
	 */
	protected function parseMethodName(string $calledMethod): array {
		preg_match_all("/(?<tag>[a-z]+)(?<action>Start|End)?/", $calledMethod, $matches, PREG_SET_ORDER);
		@['tag' => $tag, 'action' => $action] = $matches[0];
		$action = $action ? Action::tryFromName($action, true) : Action::None;

		return [$tag, $action];
	}

	/**
	 * Render html string
	 *
	 * @return string html string
	 */
	public function render(): string {
		$stylesList = [];
		$output = '<!DOCTYPE html><html>';

		foreach ($this->elementsList as $currElement) {
			$parameters = isset($currElement[1][0]) ? $currElement[1][0] : '';
			$paramsList = [];
			$params = "";
			$currStyle = '';
			$currSelector = '';

			if (is_array($parameters)) {
				foreach ($parameters as $key => $val) {
					if ($key == 'styleSelector')
						$currSelector .= $val;
					else if ($key == 'style') {
						$styleValues = [];
						foreach ($val as $k => $v) $styleValues[] = "$k:$v";

						$currStyle = implode(';', $styleValues);
					} else if ($key == 'optimize'); //code to be done
					else $paramsList[] = "$key=\"$val\"";
				}
				$params = ' ' . implode(' ', $paramsList);
			}

			[$tag, $action] = $this->parseMethodName($currElement[0]);

			if ($currSelector == '' && $currStyle != '') $params .= " style=\"$currStyle\"";
			else if ($currSelector != '' && $currStyle != '') $stylesList[$currSelector] = $currStyle;

			if ($action != Action::None) {
				if ($action == Action::Start) {
					$output .= '<' . $tag . $params . '>';
					if ($tag == 'head') $haveHeader = true;
				} else if ($action == Action::End) {
					if ($tag == 'head' && $this->stylePos == 'head') $output .= '[__CssContentPlaceholder__]';
					else if ($tag == 'body' && $this->stylePos == 'footer') $output .= '[__CssContentPlaceholder__]';
					$output .= '</' . $tag . '>';
				}
			} else {
				if ($tag == 'contents') $output .= $currElement[1][0];
				else $output .= '<' . $tag . $params . ' />';
			}
		}
		$cssCode = '<style>';
		if (count($stylesList) > 0) {
			foreach ($stylesList as $sKey => $sVal) {
				if ($sKey == '' || $sVal == '') continue;
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
	 * @param string $name method name
	 * @param array  $arguments method arguments
	 *
	 * @return mixed $this
	 */
	public function __call(string $name, array $arguments): mixed {
		$this->elementsList[] = [$name, $arguments];
		return $this;
	}
}
