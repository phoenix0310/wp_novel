<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once 'is_function.php';
include_once __DIR__ . '/../array/main_array.php';
include_once __DIR__ . '/../parser.php';
include_once __DIR__ . '/../array/is_array.php';

/**
 * Handle function calls with security checks
 * @param array $matches Matches from the function call regex
 * @param array $allowed_functions List of allowed functions
 * @return mixed Result of the function call
 * @throws Exception If the function is not allowed or does not exist
 */
function widget_logic_handle_function_call($matches, $allowed_functions)
{
    $function_name = $matches[1];
    $args_str = $matches[2];

    // Check if the function is allowed and exists
    if ('array' !== $function_name && !in_array($function_name, $allowed_functions, true)) {
        /* translators: %s: Function name that is not allowed */
        throw new Exception(sprintf(esc_html__('Widget Logic: Function "%s" is not allowed.', 'widget-logic'), esc_html($function_name)));
    }

    // Skip function_exists check for special language constructs
    if ('array' !== $function_name && !function_exists($function_name)) {
        /* translators: %s: Function name that does not exist */
        throw new Exception(sprintf(esc_html__('Widget Logic: Function "%s" does not exist.', 'widget-logic'), esc_html($function_name)));
    }

    // Check for trailing comma in arguments
    if ('' !== trim($args_str) && ',' === substr(trim($args_str), -1)) {
        /* translators: %s: Function name with trailing comma in arguments */
        throw new Exception(sprintf(esc_html__('Widget Logic: Trailing comma in function "%s" arguments.', 'widget-logic'), esc_html($function_name)));
    }

    // Parse function arguments
    $args = [];
    if ('array' !== $function_name && !empty($args_str)) {
        $current_arg = '';
        $in_quotes = false;
        $quote_char = '';
        $escape_next = false;
        $paren_level = 0;
        $bracket_level = 0; // Add tracking for square brackets

        // Tokenize the arguments string
        for ($i = 0; $i < strlen($args_str); $i++) {
            $char = $args_str[$i];

            if ($escape_next) {
                $current_arg .= $char;
                $escape_next = false;
                continue;
            }

            if ($char === '\\') {
                $escape_next = true;
                continue;
            }

            if ($in_quotes) {
                if ($char === $quote_char) {
                    $in_quotes = false;
                }
                $current_arg .= $char;
            } elseif ($char === '"' || $char === "'") {
                $in_quotes = true;
                $quote_char = $char;
                $current_arg .= $char;
            } elseif ($char === '(') {
                $paren_level++;
                $current_arg .= $char;
            } elseif ($char === ')') {
                $paren_level--;
                $current_arg .= $char;
            } elseif ($char === '[') {
                $bracket_level++; // Track opening brackets
                $current_arg .= $char;
            } elseif ($char === ']') {
                $bracket_level--; // Track closing brackets
                $current_arg .= $char;
            } elseif ($char === ',' && $paren_level === 0 && $bracket_level === 0) {
                // Only split at commas that are not inside parentheses or brackets
                $args[] = trim($current_arg);
                $current_arg = '';
            } else {
                $current_arg .= $char;
            }
        }

        if (!empty($current_arg)) {
            $args[] = trim($current_arg);
        }

        // Process arguments (remove quotes, convert numbers)
        foreach ($args as &$arg) {
            if (('"' === substr($arg, 0, 1) && '"' === substr($arg, -1)) ||
                ("'" === substr($arg, 0, 1) && "'" === substr($arg, -1))) {
                $arg = substr($arg, 1, -1);
            } elseif (is_numeric($arg)) {
                $arg = $arg + 0; // Convert to int or float
            } elseif (widget_logic_is_array_syntax($arg)) {
                // This is an array definition, parse it
                $arg = widget_logic_parse_array_string($arg, $allowed_functions);
            } elseif (widget_logic_is_function_call($arg, $nested_matches)) {
                // Call widget_logic_handle_function_call recursively for nested function calls
                $arg = widget_logic_handle_function_call($nested_matches, $allowed_functions);
            } elseif (!defined($arg) && !in_array(strtolower($arg), ['true', 'false', 'null'])) {
                // This is likely an undefined variable or constant
                /* translators: %s: Undefined argument in function call */
                throw new Exception(sprintf(esc_html__('Widget Logic: Undefined argument "%s" in function call.', 'widget-logic'), esc_html($arg)));
            }
        }
    }

    if ('array' !== $function_name) {
        widget_logic_validate_function_argument($function_name, $args);
    }

    try {
        // Call the function with the parsed arguments with error suppression
        $previous_error_reporting = error_reporting(E_ALL); // @codingStandardsIgnoreLine - we need this for error handling
        $previous_display_errors = ini_get('display_errors');
        ini_set('display_errors', 0); // @codingStandardsIgnoreLine - we need this for error handling

        // Use set_error_handler to catch any errors during function call
        set_error_handler(function($errno, $errstr) use ($function_name) {  // @codingStandardsIgnoreLine - we need this for error handling
            /* translators: 1: Function name, 2: Error message */
            throw new Exception(sprintf(esc_html__('Widget Logic: Error in function "%1$s": %2$s', 'widget-logic'), esc_html($function_name), esc_html($errstr)));
        });

        // Special handling for array() language construct (when recursion)
        $result = ('array' === $function_name)
            ? widget_logic_parse_array_string("array($args_str)", $allowed_functions)
            : call_user_func_array($function_name, $args)
        ;

        restore_error_handler();
        error_reporting($previous_error_reporting);  // @codingStandardsIgnoreLine - we need this for error handling
        ini_set('display_errors', $previous_display_errors);  // @codingStandardsIgnoreLine - we need this for error handling

        return $result;
    } catch (Exception $e) {
        // Re-throw the exception
        throw $e;
    } catch (Throwable $t) {
        // Convert PHP7+ Throwable to Exception
        throw new Exception(esc_html($t->getMessage()));
    }
}

function widget_logic_validate_function_argument($function_name, $args)
{
    // Validate argument count using reflection
    $reflection = new ReflectionFunction($function_name);
    $param_count = $reflection->getNumberOfParameters();
    $required_param_count = $reflection->getNumberOfRequiredParameters();

    if (count($args) > $param_count) {
        throw new Exception(sprintf(
            /* translators: 1: Function name, 2: Expected number of parameters, 3: Actual number of parameters */
            esc_html__('Widget Logic: Too many arguments for function "%1$s". Expected at most %2$d, got %3$d.', 'widget-logic'),
            esc_html($function_name),
            intval($param_count),
            intval(count($args))
        ));
    }

    if (count($args) < $required_param_count) {
        throw new Exception(sprintf(
            /* translators: 1: Function name, 2: Expected number of parameters, 3: Actual number of parameters */
            esc_html__('Widget Logic: Not enough arguments for function "%1$s". Expected at least %2$d, got %3$d.', 'widget-logic'),
            esc_html($function_name),
            intval($required_param_count),
            intval(count($args))
        ));
    }
}
