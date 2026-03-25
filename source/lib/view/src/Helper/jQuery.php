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

class jQuery {
	private $selector;
	private $methodsList = [];

	public function __construct($selector) {
		$this->selector = $selector;
	}

	public function output() {
		$output = "jQuery(" . $this->selector . ")";
		foreach ($this->methodsList as $currMethod) {
			$parameters = $currMethod[1];
			$paramsList = [];
			foreach ($parameters as $param) {
				if (is_callable($param)) {
					$paramFunc = 'function(){';
					$paramFunc .= $param();
					$paramFunc .= '}';
					$paramsList[] = $paramFunc;
				} else {
					$paramsList[] = "'" . $param . "'";
				}
			}
			$params = implode(",", $paramsList);

			$output .= '.' . $currMethod[0] . '(' . $params . ')';
		}
		$output .= ';';
		return $output;
	}
	
	public function __call($name, $arguments) {
		$this->methodsList[] = [$name, $arguments];
		return $this;
	}
}
