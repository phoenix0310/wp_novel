<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once 'function/is_function.php';
include_once 'function/main_function.php';

/**
 * Helper function to parse expressions
 */
function widget_logic_parse_expression(&$tokens, &$pos, $allowed_functions)
{
    // Parse first term or AND expression
    $result = widget_logic_parse_and_expression($tokens, $pos, $allowed_functions);

    while ($pos < count($tokens)) {
        $token = $tokens[$pos];

        if (')' === $token) {
            // This closing parenthesis should be handled by the calling function
            break;
        }

        // Handle OR operator (lower precedence than AND)
        if ('||' === $token) {
            $pos++;

            // Check for empty parentheses in boolean expression
            if ($pos < count($tokens) && '(' === $tokens[$pos] &&
                $pos + 1 < count($tokens) && ')' === $tokens[$pos + 1]) {
                throw new Exception( esc_html__('Widget Logic: Empty parentheses not allowed in boolean expressions.', 'widget-logic'));
            }

            // Parse the right side as an AND expression
            $right = widget_logic_parse_and_expression($tokens, $pos, $allowed_functions);
            $result = $result || $right;
        } else {
            break;
        }
    }

    return $result;
}

/**
 * Helper function to parse AND expressions (terms connected by &&)
 */
function widget_logic_parse_and_expression(&$tokens, &$pos, $allowed_functions)
{
    $result = widget_logic_parse_term($tokens, $pos, $allowed_functions);

    while ($pos < count($tokens)) {
        $token = $tokens[$pos];

        if (')' === $token || '||' === $token) {
            // These should be handled by the calling function
            break;
        }

        if ('&&' === $token) {
            $pos++;

            // Check for empty parentheses in boolean expression
            if ($pos < count($tokens) && '(' === $tokens[$pos] &&
                $pos + 1 < count($tokens) && ')' === $tokens[$pos + 1]) {
                throw new Exception( esc_html__('Widget Logic: Empty parentheses not allowed in boolean expressions.', 'widget-logic'));
            }

            $term = widget_logic_parse_term($tokens, $pos, $allowed_functions);
            $result = $result && $term;
        } else {
            break;
        }
    }

    return $result;
}

/**
 * Helper function to parse terms
 */
function widget_logic_parse_term(&$tokens, &$pos, $allowed_functions)
{
    if ($pos >= count($tokens)) {
        throw new Exception(esc_html__('Widget Logic: Unexpected end of expression.', 'widget-logic'));
    }

    $token = $tokens[$pos++];

    // Handle negation
    if ('!' === $token) {
        return !widget_logic_parse_term($tokens, $pos, $allowed_functions);
    }

    // Handle parenthesized expressions
    if ('(' === $token) {
        // Check for empty parentheses case
        if ($pos < count($tokens) && ')' === $tokens[$pos]) {
            $pos++; // Skip the closing parenthesis
            return true; // Empty parentheses treated like empty string
        }

        $result = widget_logic_parse_expression($tokens, $pos, $allowed_functions);

        if ($pos >= count($tokens) || ')' !== $tokens[$pos]) {
            throw new Exception(esc_html__('Widget Logic: Missing closing parenthesis.', 'widget-logic'));
        }

        $pos++; // Skip the closing parenthesis
        return $result;
    }

    // Handle closing parenthesis without matching opening parenthesis
    if (')' === $token) {
        throw new Exception( esc_html__('Widget Logic: Unbalanced closing parenthesis.', 'widget-logic'));
    }

    // Reject direct array usage
    if (strpos($token, 'array(') === 0 || strpos($token, '[') === 0) {
        throw new Exception( esc_html__('Widget Logic: Direct array usage is not allowed in expressions.', 'widget-logic'));
    }

    // Handle boolean constants
    if (0 === strcasecmp('true', $token)) {
        return true;
    }

    if (0 === strcasecmp('false', $token)) {
        return false;
    }

    if (0 === strcasecmp('null', $token)) {
        return false;
    }

    // Handle function calls
    if (widget_logic_is_function_call( $token, $matches)) {
        return widget_logic_handle_function_call($matches, $allowed_functions);
    }

    /* translators: %s: The unexpected token found in the expression */
    throw new Exception(sprintf(esc_html__('Widget Logic: Unexpected token: %s', 'widget-logic'), esc_html($token)));
}
