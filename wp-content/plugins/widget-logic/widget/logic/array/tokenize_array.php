<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly




/**
 * Capture an array token from the logic string
 */
function widget_logic_capture_array($logic, $i, &$tokens)
{
    $start = $i;

    if ('[' === $logic[$i]) {
        $i++;
        // Find matching closing bracket
        $bracket_level = 1;
        $length = strlen($logic);

        while ($i < $length && $bracket_level > 0) {
            if ('[' === $logic[$i]) {
                $bracket_level++;
            } elseif (']' === $logic[$i]) {
                $bracket_level--;
            }
            $i++;
        }
    } else {
        // Handle array() syntax
        $i += 6; // Skip "array("
        // Find matching closing parenthesis
        $paren_level = 1;
        $length = strlen($logic);

        while ($i < $length && $paren_level > 0) {
            if ('(' === $logic[$i]) {
                $paren_level++;
            } elseif (')' === $logic[$i]) {
                $paren_level--;
            }
            $i++;
        }
    }

    $tokens[] = substr($logic, $start, $i - $start);
    return $i;
}
