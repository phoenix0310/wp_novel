<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Check if value is a function call
 */
function widget_logic_is_function_call($value, &$matches)
{
    return preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\((.*)\)$/', $value, $matches);
}
