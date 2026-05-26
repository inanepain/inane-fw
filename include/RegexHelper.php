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
 * @package  inanepain\inane-fw
 * @category inane-fw
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

/**
 * Helper class to parse and describe regular expressions in plain English.
 */
class RegexHelper {
    #region MAIN PUBLIC METHOD
    /**
     * Converts a regular expression string into a human-readable English description.
     *
     * @param string $regex The regular expression to describe.
     *
     * @return string The English description of the regular expression.
     */
    public static function describeRegex(string $regex): string {
        // Parse the regex into a structured array and then convert it to words
        return static::regexToWords(static::describeRegexStructured($regex));
    }
    #endregion MAIN PUBLIC METHOD

    #region SECONDARY PUBLIC METHODS
    /**
     * Converts a structured regex node (or array of nodes) into a human-readable string.
     *
     * @param array $node The structured regex node to convert.
     *
     * @return string The descriptive text for the node.
     */
    public static function regexToWords(array $node): string {
        // Handle OR nodes by converting each option and joining with "or"
        if (isset($node['type']) && $node['type'] === 'OR') {
            $parts = [];

            foreach($node['options'] as $option) {
                $parts[] = trim(static::sequenceToWords($option));
            }

            return 'either ' . implode(' or ', $parts);
        }

        // If the node is a sequence (list of nodes), convert the sequence
        if (array_keys($node) === range(0, count($node) - 1)) {
            return static::sequenceToWords($node);
        }

        // Default: treat as a single-element sequence
        return static::sequenceToWords([$node]);
    }

    /**
     * Parses a regular expression string into a structured hierarchical array representation.
     *
     * @param string $pattern The regular expression pattern.
     *
     * @return array The structured array representing the parsed regex.
     */
    public static function describeRegexStructured(string $pattern): array {
        $pattern = trim($pattern);

        // Remove leading and trailing forward slashes if present
        $pattern = preg_replace('#^/#', '', $pattern);
        $pattern = preg_replace('#/$#', '', $pattern);

        $i = 0;
        $len = strlen($pattern);

        return static::parseSequence($pattern, $i, $len);
    }
    #endregion SECONDARY PUBLIC METHODS

    #region PROTECTED METHODS
    /**
     * Parses a sequence of regex characters into an array of structured nodes.
     *
     * @param string $p   The regex pattern string.
     * @param int    $i   The current index in the string (passed by reference).
     * @param int    $len The total length of the pattern string.
     *
     * @return array The parsed nodes for this sequence.
     */
    protected static function parseSequence(string $p, int &$i, int $len): array {
        $nodes = [];
        $alternatives = [];
        $current = [];

        while($i < $len) {
            $char = $p[$i];

            // Handle the end of a group
            if ($char === ')') {
                $i++;
                break;
            }

            // Handle alternation (OR)
            if ($char === '|') {
                $alternatives[] = $current;
                $current = [];
                $i++;
                continue;
            }

            // Handle the start of a group (capturing or non-capturing)
            if ($char === '(') {
                $i++;

                $type = 'capturing group';
                // Check if it is a non-capturing group
                if (substr($p, $i, 2) === '?:') {
                    $type = 'non-capturing group';
                    $i += 2;
                }

                // Recursively parse the group's contents
                $group = static::parseSequence($p, $i, $len);

                $current[] = [
                    'type'     => $type,
                    'children' => $group,
                ];
                continue;
            }

            // Handle character classes (e.g., [a-z])
            if ($char === '[') {
                $end = strpos($p, ']', $i);
                $expr = substr($p, $i + 1, $end - $i - 1);
                $i = $end + 1;

                $current[] = [
                    'type'  => 'character class',
                    'value' => $expr,
                ];
                continue;
            }

            // Handle escaped characters
            if ($char === '\\') {
                $next = $p[$i + 1] ?? '';
                $i += 2;

                $current[] = [
                    'type'  => 'literal',
                    'value' => $next,
                ];
                continue;
            }

            // Handle standard quantifiers (*, +, ?)
            if (in_array($char, [
                '*',
                '+',
                '?',
            ], true)) {
                $last = array_pop($current);

                if ($last) {
                    $last['quantifier'] = $char;
                    $current[] = $last;
                }

                $i++;
                continue;
            }

            // Handle explicit brace quantifiers (e.g., {m,n})
            if ($char === '{') {
                $end = strpos($p, '}', $i);
                $expr = substr($p, $i + 1, $end - $i - 1);
                $i = $end + 1;

                $last = array_pop($current);

                if ($last) {
                    $last['quantifier'] = $expr;
                    $current[] = $last;
                }

                continue;
            }

            // Default to treating the character as a literal
            $current[] = [
                'type'  => 'literal',
                'value' => $char,
            ];

            $i++;
        }

        // Add any remaining items in the current sequence to the alternatives
        if (!empty($current)) {
            $alternatives[] = $current;
        }

        // If only one branch exists, return it as a normal sequence
        if (count($alternatives) === 1) {
            return $alternatives[0];
        }

        // Otherwise, wrap the alternatives in an OR node
        return [
            'type'    => 'OR',
            'options' => $alternatives,
        ];
    }

    /**
     * Converts a sequence of parsed regex nodes into a descriptive string.
     *
     * @param array $nodes The list of nodes to convert.
     *
     * @return string The combined description for the sequence.
     */
    protected static function sequenceToWords(array $nodes): string {
        $out = [];

        foreach($nodes as $n) {
            // Process capturing or non-capturing groups
            if (isset($n['type']) && in_array($n['type'], [
                    'capturing group',
                    'non-capturing group',
                ])) {
                $inner = static::regexToWords($n['children']);

                $desc = $n['type'] === 'capturing group'
                    ? 'a group capturing ' . $inner
                    : 'a non-capturing group matching ' . $inner;

                if (isset($n['quantifier'])) {
                    $desc .= ', repeated ' . static::quantifierToWords($n['quantifier']);
                }

                $out[] = $desc;
                continue;
            }

            // Process character classes
            if (isset($n['type']) && $n['type'] === 'character class') {
                $values = $n['values'] ?? [$n['value'] ?? ''];

                $desc = 'one character of [' . implode(', ', $values) . ']';

                // Check if the character class is negated
                if (!empty($n['negated'])) {
                    $desc = 'any character except [' . implode(', ', $values) . ']';
                }

                if (isset($n['quantifier'])) {
                    $desc .= ', ' . static::quantifierToWords($n['quantifier']);
                }

                $out[] = $desc;
                continue;
            }

            // Process literal characters
            if (isset($n['type']) && $n['type'] === 'literal') {
                $desc = 'the character "' . $n['value'] . '"';

                if (isset($n['quantifier'])) {
                    $desc .= ', ' . static::quantifierToWords($n['quantifier']);
                }

                $out[] = $desc;
                continue;
            }
        }

        // Join multiple nodes in the sequence
        return implode(', then ', $out);
    }

    /**
     * Converts a regex quantifier into plain English text.
     *
     * @param string $q The quantifier string (e.g., *, +, ?, or {m,n}).
     *
     * @return string The description of the quantifier.
     */
    protected static function quantifierToWords(string $q): string {
        if ($q === '*') return 'zero or more times';
        if ($q === '+') return 'one or more times';
        if ($q === '?') return 'optional (zero or one time)';

        // Match {m,n} quantifiers
        if (preg_match('/^(\d+),(\d+)$/', $q, $m)) {
            return "between {$m[1]} and {$m[2]} times";
        }

        // Match {m,} quantifiers
        if (preg_match('/^(\d+),$/', $q, $m)) {
            return "at least {$m[1]} times";
        }

        // Match {m} exact quantifiers
        if (preg_match('/^(\d+)$/', $q, $m)) {
            return "exactly {$m[1]} times";
        }

        // Fallback for unrecognized quantifiers
        return "repeated ({$q})";
    }
    #endregion PROTECTED METHODS
}
