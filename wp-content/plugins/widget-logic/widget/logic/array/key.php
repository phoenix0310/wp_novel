<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once __DIR__ . '/../function/is_function.php';

/**
 * Process an array key with security checks
 */
function widget_logic_process_array_key($key, $allowed_functions)
{
    // Handle function calls in keys
    if (widget_logic_is_function_call(trim($key), $matches)) {
        $function_name = $matches[1];

        throw new Exception(sprintf(
            /* translators: %s: Function name that cannot be used as array key */
            esc_html__('Widget Logic: Cannot use function "%s" as array key.', 'widget-logic'),
            esc_html($function_name)
        ));
    }

    // Process normal keys (quoted strings, numbers, etc.)
    if (('"' === substr($key, 0, 1) && '"' === substr($key, -1)) ||
        ("'" === substr($key, 0, 1) && "'" === substr($key, -1))) {
        $key_value = substr($key, 1, -1);

        // Security check for PHP stream wrappers in keys
        $dangerous_wrappers = array('php://', 'file://', 'expect://', 'data://', 'zip://', 'glob://', 'phar://');
        foreach ($dangerous_wrappers as $wrapper) {
            if (false !== stripos($key_value, $wrapper)) {
                throw new Exception(sprintf(
                    /* translators: %s: The type of dangerous stream wrapper detected in array key */
                     esc_html__('Widget Logic: Potentially dangerous stream wrapper "%s" detected in array key.', 'widget-logic'),
                    esc_html($wrapper)
                ));
            }
        }

        return $key_value;
    }

    // Convert numeric keys
    if (is_numeric($key)) {
        return $key + 0;
    }

    return $key;
}
