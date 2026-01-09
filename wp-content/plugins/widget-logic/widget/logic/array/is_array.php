<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Check if value uses array syntax
 */
function widget_logic_is_array_syntax($value)
{
    return (0 === strpos($value, 'array(') && ')' === substr($value, -1)) ||
           ('[' === substr($value, 0, 1) && ']' === substr($value, -1));
}
