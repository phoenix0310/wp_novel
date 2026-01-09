<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once __DIR__ . '/../is_array.php';
include_once __DIR__ . '/../main_array.php';
include_once __DIR__ . '/../../function/is_function.php';
include_once __DIR__ . '/../../function/main_function.php';
include_once 'string.php';



/**
 * Process array values, including functions and nested arrays
 */
function widget_logic_process_array_value($value, $allowed_functions)
{
    $value = trim($value);

    // Check for different value types and apply appropriate parsing
    if (widget_logic_is_array_syntax($value)) {
        return widget_logic_parse_array_string($value, $allowed_functions);
    }

    if (widget_logic_is_function_call($value, $out_matches)) {
        return widget_logic_handle_function_call($out_matches, $allowed_functions);
    }

    if (widget_logic_is_quoted_string($value, $out_string)) {
        return widget_logic_validate_string_value($out_string);
    }

    // Handle simple value types
    if (is_numeric($value)) {
        return $value + 0; // Convert to int or float
    }

    if ('true' === strtolower($value)) {
        return true;
    }

    if ('false' === strtolower($value)) {
        return false;
    }

    if ('null' === strtolower($value)) {
        return null;
    }

    // Default to returning as a string
    return $value;
}
