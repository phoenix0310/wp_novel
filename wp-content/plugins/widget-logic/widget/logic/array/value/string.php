<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly




/**
 * Check if value is a quoted string
 */
function widget_logic_is_quoted_string($value, &$out_string)
{
    if (('"' === substr($value, 0, 1) && '"' === substr($value, -1)) ||
        ("'" === substr($value, 0, 1) && "'" === substr($value, -1))) {
        $out_string = substr($value, 1, -1);
        return true;
    }
    return false;
}

/**
 * Validate string values for security issues
 */
function widget_logic_validate_string_value($string_value)
{
    // Check for PHP stream wrappers in strings
    $dangerous_wrappers = array('php://', 'file://', 'expect://', 'data://', 'zip://', 'glob://', 'phar://');
    foreach ($dangerous_wrappers as $wrapper) {
        if (false !== stripos($string_value, $wrapper)) {
            throw new Exception(sprintf(
                /* translators: %s: The type of dangerous wrapper detected in string */
                esc_html__('Widget Logic: Dangerous string in %s.', 'widget-logic'),
                esc_html($wrapper)
            ));
        }
    }

    // Check for directory traversal attempts
    if (preg_match('/(\.\.\/|\.\.\\\\)/', $string_value)) {
        throw new Exception( esc_html__('Widget Logic: Directory traversal attempt detected in string.', 'widget-logic'));
    }

    return $string_value;
}
