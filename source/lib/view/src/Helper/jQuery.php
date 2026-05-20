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

use function implode;
use function is_callable;

class jQuery {
    private string $selector;

    private array $methodsList = [];

    public function __construct(string $selector) {
        $this->selector = $selector;
    }

    public function output(): string {
        $output = 'jQuery(' . $this->selector . ')';
        foreach($this->methodsList as $currMethod) {
            $parameters = $currMethod[1];
            $paramsList = [];
            foreach($parameters as $param) {
                if (is_callable($param)) {
                    $paramFunc = 'function(){';
                    $paramFunc .= $param();
                    $paramFunc .= '}';
                    $paramsList[] = $paramFunc;
                } else {
                    $paramsList[] = "'" . $param . "'";
                }
            }
            $params = implode(',', $paramsList);

            $output .= '.' . $currMethod[0] . '(' . $params . ')';
        }
        $output .= ';';

        return $output;
    }

    public function __call(string $name, array $arguments): jQuery {
        $this->methodsList[] = [
            $name,
            $arguments
        ];

        return $this;
    }
}
