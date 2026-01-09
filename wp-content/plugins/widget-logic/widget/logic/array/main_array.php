<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once 'key.php';
include_once 'value/main_value.php';


/**
 * Parse array strings into actual PHP arrays with security checks
 */
function widget_logic_parse_array_string($array_str, $allowed_functions)
{
    $array_str = trim($array_str);

    // Handle empty arrays
    if ('array()' === $array_str || '[]' === $array_str) {
        return array();
    }

    // Extract inner string by removing array wrappers
    $inner_str = widget_logic_extract_array_inner($array_str);
    if ('' === $inner_str) {
        return array();
    }

    // Parse the array elements
    $result = array();
    $elements = widget_logic_split_array_elements($inner_str);

    foreach ($elements as $element) {
        widget_logic_process_array_element($element, $result, $allowed_functions);
    }

    return $result;
}

/**
 * Extract the inner string from array syntax
 */
function widget_logic_extract_array_inner($array_str)
{
    if (0 === strpos($array_str, 'array(') && ')' === substr($array_str, -1)) {
        return trim(substr($array_str, 6, -1));
    }
    if ('[' === substr($array_str, 0, 1) && ']' === substr($array_str, -1)) {
        return trim(substr($array_str, 1, -1));
    }
    throw new Exception( esc_html__('Widget Logic: Invalid array format.', 'widget-logic'));
}

/**
 * Process a single array element and add it to the result array
 */
function widget_logic_process_array_element($element, &$result, $allowed_functions)
{
    // Check if it's a key => value pair
    if (preg_match('/^(.+?)=>(.+)$/s', $element, $matches)) {
        $key = trim($matches[1]);
        $value = trim($matches[2]);

        // Process the key
        $processed_key = widget_logic_process_array_key($key, $allowed_functions);

        // Process the value with security checks
        $processed_value = widget_logic_process_array_value($value, $allowed_functions);
        $result[$processed_key] = $processed_value;
    } else {
        // It's just a value, process with security checks
        $processed_value = widget_logic_process_array_value($element, $allowed_functions);
        $result[] = $processed_value;
    }
}

/**
 * Helper function to split array elements, handling nested structures and quotes
 */
function widget_logic_split_array_elements($str)
{
    $elements = array();
    $current = '';
    $in_quotes = false;
    $quote_char = '';
    $escape_next = false;
    $paren_level = 0;
    $bracket_level = 0;

    for ($i = 0; $i < strlen($str); $i++) {
        $char = $str[$i];

        if ($escape_next) {
            $current .= $char;
            $escape_next = false;
            continue;
        }

        if ('\\' === $char) {
            $escape_next = true;
            $current .= $char;
            continue;
        }

        if ($in_quotes) {
            if ($char === $quote_char) {
                $in_quotes = false;
            }
            $current .= $char;
        } elseif ('"' === $char || "'" === $char) {
            $in_quotes = true;
            $quote_char = $char;
            $current .= $char;
        } elseif ('(' === $char) {
            $paren_level++;
            $current .= $char;
        } elseif (')' === $char) {
            $paren_level--;
            $current .= $char;
        } elseif ('[' === $char) {
            $bracket_level++;
            $current .= $char;
        } elseif (']' === $char) {
            $bracket_level--;
            $current .= $char;
        } elseif (',' === $char && 0 === $paren_level && 0 === $bracket_level) {
            $elements[] = trim($current);
            $current = '';
        } else {
            $current .= $char;
        }
    }

    if ('' !== trim($current)) {
        $elements[] = trim($current);
    }

    return $elements;
}
