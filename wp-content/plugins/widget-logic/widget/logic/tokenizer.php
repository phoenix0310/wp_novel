<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once 'array/tokenize_array.php';

/**
 * Tokenize a logic expression string into an array of tokens
 */
function widget_logic_tokenize($logic)
{
    $tokens = [];
    $i = 0;
    $length = strlen($logic);

    while ($i < $length) {
        // Skip whitespace
        if (ctype_space($logic[$i])) {
            $i++;
            continue;
        }

        // Handle arrays as direct tokens
        if ('[' === $logic[$i] || (0 === strpos(substr($logic, $i), 'array(') && $i + 6 <= $length)) {
            $i = widget_logic_capture_array($logic, $i, $tokens);
            continue;
        }

        // Handle operators and parentheses
        if (in_array($logic[$i], ['(', ')', '!'], true)) {
            $tokens[] = $logic[$i];
            $i++;
        } elseif ($i + 1 < $length && ('&&' === $logic[$i] . $logic[$i + 1] || '||' === $logic[$i] . $logic[$i + 1])) {
            $tokens[] = $logic[$i] . $logic[$i + 1];
            $i += 2;
        } elseif (ctype_alpha($logic[$i]) || '_' === $logic[$i]) {
            $i = widget_logic_capture_word_or_function($logic, $i, $tokens);
        } else {
            // Handle other characters (this might be an error)
            throw new Exception(sprintf(
                /* translators: %s: The unexpected character in the logic expression */
                 esc_html__('Widget Logic: Unexpected character "%s" in logic expression.', 'widget-logic'),
                esc_html($logic[$i])
            ));
        }
    }

    return $tokens;
}

/**
 * Capture a word or function call token from the logic string
 */
function widget_logic_capture_word_or_function($logic, $i, &$tokens)
{
    $start = $i;
    $length = strlen($logic);

    // Capture word
    while ($i < $length && (ctype_alnum($logic[$i]) || $logic[$i] === '_')) {
        $i++;
    }

    $word = substr($logic, $start, $i - $start);

    // Skip any whitespace after the word
    $temp_i = $i;
    while ($temp_i < $length && ctype_space($logic[$temp_i])) {
        $temp_i++;
    }

    // Check if it's a function call
    if ($temp_i < $length && '(' === $logic[$temp_i]) {
        $i = $temp_i;
        $paren_level = 0;
        $args_start = $i + 1;

        do {
            if ('(' === $logic[$i]) {
                $paren_level++;
            } elseif (')' === $logic[$i]) {
                $paren_level--;
            }
            $i++;
        } while ($i < $length && $paren_level > 0);

        if (0 !== $paren_level) {
            throw new Exception( esc_html__('Widget Logic: Unbalanced parentheses in function call.', 'widget-logic'));
        }

        // Extract and clean the arguments string
        $args_length = $i - $args_start - 1;
        $args_str = trim(substr($logic, $args_start, $args_length));

        // Create a cleaned version of the function call
        $tokens[] = $word . '(' . $args_str . ')';
    } else {
        // Boolean constant or other word
        $tokens[] = $word;
    }

    return $i;
}
