<?php
/** uncomment this code for test widget logic core [here -> remove "_"] *_/

// * Test file for widget_logic_check_logic function
// *
// This file mocks WordPress functions and provides test cases for the widget_logic_check_logic function.
// *

// Define testing mode for error handler
define('WIDGET_LOGIC_TESTING', true);

// Track test failures
$test_failures = 0;

// Mock WordPress functions
if (!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__));

// Mock translation function
if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return $text;
    }
}

// Mock WordPress core conditional functions
if (!function_exists('is_home')) {
    function is_home($v = '') {
        return true; // Mocking is_home to always return true
    }
}

if (!function_exists('is_front_page')) {
    function is_front_page() {
        return false;
    }
}

if (!function_exists('is_single')) {
    function is_single($post = '') {
        if ($post === 'test-post') {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_page')) {
    function is_page($page = '') {
        if ($page === 'about') {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_category')) {
    function is_category($category = '') {
        if ($category === 'news') {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_tag')) {
    function is_tag($tag = '') {
        if ($tag === 'date') {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_archive')) {
    function is_archive() {
        return false;
    }
}

if (!function_exists('is_search')) {
    function is_search() {
        return false;
    }
}

if (!function_exists('is_404')) {
    function is_404() {
        return false;
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        if ($capability === 'edit_posts') {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_active_sidebar')) {
    function is_active_sidebar($sidebar_id) {
        if ($sidebar_id === 'sidebar-1') {
            return true;
        }
        return false;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

// Mock other WordPress functions
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) {
        if ($tag === 'widget_logic_allowed_functions') {
            // Allow some mock functions for testing
            return array_merge($value, array('my_custom_func'));
        }
        // For other tags, just return the value
        return $value;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('my_custom_func')) {
    function my_custom_func($inp) {
        if (is_array($inp)) {
            return false;
        }
        return true; // Mock custom function
    }
}

// Mock WordPress options for error handling
global $wl_options;
$wl_options = array(
    'widget_logic-options-show_errors' => true
);

// Include the file with the function to test
require_once('logic.php');

// *
// * Function to run a test case and output the result
// *
// * @param int|float $test_number The test case number
// * @param string $logic The widget logic expression to test
// * @param bool|null $expected The expected result (true/false)
// * @param bool $expect_error Whether an error message is expected (true = error expected, false = no error expected)

function run_test_case($test_number, $logic, $expected = null, $expect_error = null) {
    global $test_failures;

    echo "\n--- Test Case #{$test_number} ---\n";
    echo "Logic: " . ($logic === '' ? '(empty string)' : $logic) . "\n";

    // Capture output in case error handler prints something
    ob_start();
    $result = widget_logic_check_logic($logic);
    $output = ob_get_clean();
    $has_error = !empty($output);

    echo "Result: ";
    var_dump($result);

    if ($has_error) {
        echo "Error: " . trim($output) . "\n";
    } else if ($result === false) {
        echo "Error: No error message was displayed, but the function returned false\n";
    }

    // Check if error message matches expectation
    if ($expect_error !== null) {
        if ($expect_error && !$has_error) {
            echo "\n*** TEST FAILURE: Expected an error message but none was displayed! ***\n";
            $test_failures++;
            exit(1); // Exit with failure code
        }
        if (!$expect_error && $has_error) {
            echo "\n*** TEST FAILURE: Did not expect an error message but one was displayed! ***\n";
            $test_failures++;
            exit(1); // Exit with failure code
        }
        echo "Error expectation: " . ($expect_error ? "Expected error - Matched" : "No error expected - Matched") . "\n";
    }

    if ($expected !== null) {
        echo "Expected: ";
        var_dump($expected);
        $matches = $result === $expected;
        echo "Matches: " . ($matches ? "Yes" : "No") . "\n";

        // Track the failure and exit immediately if there's a mismatch
        if (!$matches) {
            $test_failures++;
            echo "\n*** TEST FAILURE: Result does not match expected value! ***\n";
            exit(1); // Exit with failure code
        }
    }
}

// Run test cases
echo "\n=== WIDGET LOGIC TEST CASES ===\n";

// Test Case 1: Empty string (should return true)
run_test_case(1, '', true, false);

// Test Case 2: Simple true
run_test_case(2, 'true', true, false);

// Test Case 3: Simple false
run_test_case(3, 'false', false, false);

// Test Case 4: Simple function call (is_home returns true in our mock)
run_test_case(4, 'is_home()', true, false);

// Test Case 5: Function call with argument
run_test_case(5, 'is_tag("date")', true, false);

// Test Case 6: Function call with argument that returns false
run_test_case(6, 'is_tag("not-a-tag")', false, false);

// Test Case 7: Simple AND operation (both true)
run_test_case(7, 'true && true', true, false);

// Test Case 8: Simple AND operation (one false)
run_test_case(8, 'true && false', false, false);

// Test Case 9: Simple OR operation (one true)
run_test_case(9, 'true || false', true, false);

// Test Case 10: Simple OR operation (both false)
run_test_case(10, 'false || false', false, false);

// Test Case 11: Simple negation
run_test_case(11, '!false', true, false);

// Test Case 12: Nested negation
run_test_case(12, '!!true', true, false);

// Test Case 13: Function with negation
run_test_case(13, '!is_archive()', true, false);

// Test Case 14: Simple parentheses
run_test_case(14, '(true)', true, false);

// Test Case 15: Complex expression with parentheses
run_test_case(15, '(true && false) || true', true, false);

// Test Case 16: Function calls with AND
run_test_case(16, 'is_home() && is_user_logged_in()', true, false);

// Test Case 17: Function calls with OR
run_test_case(17, 'is_archive() || is_home()', true, false);

// Test Case 18: Function with argument and AND
run_test_case(18, 'is_tag("date") && is_home()', true, false);

// Test Case 19: Complex expression with functions and operators
run_test_case(19, 'is_home() || (is_tag("date") && is_active_sidebar("sidebar-1"))', true, false);

// Test Case 20: Complex expression with functions, operators, and negation
run_test_case(20, '!is_admin() && (is_home() || is_page("about"))', true, false);

// Test Case 21: Multiple levels of parentheses
run_test_case(21, '(is_home() && (is_user_logged_in() || is_admin())) || false', true, false);

// Test Case 22: Function with invalid argument (should detect undefined variable)
run_test_case(22, 'is_tag(invalid_argument)', false, true);

// Add a specific test for unquoted string arguments
run_test_case(22.1, 'is_tag(date)', false, true);

// Test Case 23: Unbalanced parentheses
run_test_case(23, '(is_home() && is_user_logged_in()', false, true);

// Test Case 24: Non-allowed function
run_test_case(24, 'htmlspecialchars("test")', false, true);

// Test Case 25: Function with multiple arguments
run_test_case(25, 'current_user_can("edit_posts")', true, false);

echo "\n=== ERROR CONDITION TEST CASES ===\n";

// Test Case 26: Unbalanced opening parentheses
run_test_case(26, '(is_home() && (is_user_logged_in()', false, true);

// Test Case 27: Unbalanced closing parentheses
run_test_case(27, 'is_home() && is_user_logged_in())', false, true);

// Test Case 28: Invalid character in expression
run_test_case(28, 'is_home() # is_user_logged_in()', false, true);

// Test Case 29: Disallowed function
run_test_case(29, 'file_get_contents("wp-config.php")', false, true);

// Test Case 30: Non-existent function
run_test_case(30, 'function_does_not_exist()', false, true);

// Test Case 31: Unexpected end of expression after operator
run_test_case(31, 'is_home() &&', false, true);

// Test Case 32: Invalid function name format
run_test_case(32, '123is_home()', false, true);

// Test Case 33: Empty parentheses (should return true like empty string)
run_test_case(33, '()', true, false);

// Test Case 34: Unbalanced quotes in function argument
run_test_case(34, 'is_tag("unbalanced)', false, true);

// Test Case 35: Missing closing parenthesis in function call
run_test_case(35, 'is_tag("tag"', false, true);

// Test Case 36: Unexpected character after valid expression
run_test_case(36, 'is_home() is_tag()', false, true);

// Test Case 37: Invalid operator
run_test_case(37, 'is_home() & is_tag()', false, true);

// Test Case 38: Consecutive operators
run_test_case(38, 'is_home() && || is_tag()', false, true);

// Test Case 39: Unbalanced nested parentheses
run_test_case(39, '(is_home() && (is_tag() || (is_admin())', false, true);

// Test Case 40: Empty function call
run_test_case(40, 'is_home() && ()', false, true);

// Test Case 41: Function with too many arguments
run_test_case(41, 'is_home("param1", "param2", "param3")', false, true);

// Test Case 42: Unexpected end after negation
run_test_case(42, 'is_home() && !', false, true);

// Test Case 43: Invalid token inside parentheses
run_test_case(43, '(is_home() @ is_admin())', false, true);

// Test Case 44: Function call inside another function's arguments
run_test_case(44, 'is_page(is_admin())', false, false);

// Test Case 45: Function with malformed arguments
run_test_case(45, 'is_category(,)', false, true);

echo "\n=== ADDITIONAL EDGE CASES ===\n";

// Test Case 46: Function with null argument
run_test_case(46, 'is_tag(null)', false, false);

// Test Case 47: Function with boolean argument
run_test_case(47, 'is_page(true)', false, false);

// Test Case 48: Deeply nested parentheses
run_test_case(48, '(((((true)))))', true, false);

// Test Case 49: Long expression
run_test_case(49, 'true && true && true && true && true && true && true && true && true && true', true, false);

// Test Case 50: Expression with lots of whitespace
run_test_case(50, '  is_home  (  )   &&   is_user_logged_in  (  )  ', true, false);

// Test Case 51: Negating a complex expression
run_test_case(51, '!(is_home() && is_user_logged_in())', false, false);

// Test Case 52: Double negation of a complex expression
run_test_case(52, '!!(is_home() && is_user_logged_in())', true, false);

// Test Case 53: Alternating AND/OR operators
run_test_case(53, 'true && false || true && false || true', true, false);

// Test Case 54: Multiple negation operators
run_test_case(54, '!!!!true', true, false);

// Test Case 55: Complex nested expression
run_test_case(55, '((is_home() || is_tag("date")) && (is_user_logged_in() || is_admin())) || false', true, false);

// Test Case 56: Expression with mixed boolean literals and functions
run_test_case(56, 'true && is_home() || false && is_admin()', true, false);

// Test Case 57: Multiple opening parentheses but only one closing (should fail)
run_test_case(57, '((((true)', false, true);

// Test Case 58: Multiple closing parentheses but only one opening (should fail)
run_test_case(58, 'true))))', false, true);

// Test Case 59: Negation before parenthesis
run_test_case(59, '!(true || false)', false, false);

// Test Case 60: Function call with empty string argument
run_test_case(60, 'is_tag("")', false, false);

// Test Case 61: Function call with nested quoted strings
run_test_case(61, 'is_tag("tag \\"with quotes\\"")', false, false);

// Test Case 62: Complex nested logical expression
run_test_case(62, '!((is_home() && !is_admin()) || (!is_home() && is_admin()))', false, false);

// Test Case 63: Function with escaped quotes in argument
run_test_case(63, 'is_tag("escaped\\"quote")', false, false);

// Test Case 64: Function with single quotes inside double quotes
run_test_case(64, 'is_tag("tag with \'quotes\'")', false, false);

// Test Case 65: Function with double quotes inside single quotes
run_test_case(65, "is_tag('tag with \"quotes\"')", false, false);

// Test Case 66: Zero-length expression inside parentheses (already tested with case 33)
run_test_case(66, '()', true, false);

// Test Case 67: Expression with tabs and newlines
run_test_case(67, "is_home()\t&&\nis_user_logged_in()", true, false);

// Test Case 68: Multiple function calls separated by spaces (should fail)
run_test_case(68, 'is_home() is_admin()', false, true);

// Test Case 69: Function with trailing comma (should fail)
run_test_case(69, 'is_tag("date",)', false, true);

// Test Case 70: String value as a boolean (should fail)
run_test_case(70, '"true"', false, true);

// Test Case 71: Numeric value as a boolean (should fail)
run_test_case(71, '1', false, true);

// Test Case 72: Function with excessive nesting (should fail)
run_test_case(72, 'is_tag(is_tag(is_tag("date")))', false, false);

// Test Case 73: Function with unmatched quotes (should fail)
run_test_case(73, 'is_tag("unmatched\')', false, true);

// Test Case 74: Function with backslashes
run_test_case(74, 'is_tag("date\\\\tag")', false, false);

// Test Case 75: Complex expression with many operators and functions
run_test_case(75, 'is_home() && !is_admin() || is_page("about") && is_user_logged_in() || is_tag("date") && !is_archive()', true, false);

// Test Case 76: Function call with malformed argument list (spaces in wrong place)
run_test_case(76, 'is_page( "about" , "contact" )', false, true);

// Test Case 77: Function with valid but incorrect argument type
run_test_case(77, 'current_user_can(123)', false, false);

// Test Case 78: Expression with incorrect capitalization of function name
run_test_case(78, 'IS_HOME()', false, true);

// Test Case 79: Decimal number as argument (should be converted properly)
run_test_case(79, 'is_category(3.14)', false, false);

// Test Case 80: Very long complex expression
run_test_case(80, '(is_home() && is_user_logged_in()) || (is_page("about") && !is_admin()) || (is_tag("date") && is_user_logged_in() && !is_admin()) || (is_category("news") && (is_user_logged_in() || is_admin()))', true, false);

// Enhanced error message tests with the additional edge cases
function enhance_additional_error_tests() {
    echo "\n=== ADDITIONAL EDGE CASE ERROR MESSAGES ===\n";

    $additional_test_cases = [
        'Null argument' => 'is_tag(null)',
        'Boolean argument' => 'is_page(true)',
        'Multiple opening parentheses' => '((((true)',
        'Multiple closing parentheses' => 'true))))',
        'Nested quoted strings' => 'is_tag("tag \\"with quotes\\"")',
        'Trailing comma' => 'is_tag("date",)',
        'String as boolean' => '"true"',
        'Numeric value as boolean' => '1',
        'Excessive nesting' => 'is_tag(is_tag(is_tag("date")))',
        'Unmatched quotes' => 'is_tag("unmatched\')',
        'Incorrect capitalization' => 'IS_HOME()',
        'Long nested expression' => '(((((((((((is_home()))))))))))) && (((((((((is_admin())))))))))'
    ];

    foreach ($additional_test_cases as $description => $logic) {
        echo "\nTesting error message for $description:\n";
        echo "Logic: $logic\n";

        ob_start();
        $result = widget_logic_check_logic($logic);
        $output = ob_get_clean();

        echo "Result: ";
        var_dump($result);
        echo "Error message: " . ($output ? trim($output) : "No error message displayed") . "\n";
    }
}

// Run additional error message tests
enhance_additional_error_tests();

echo "\n=== CUSTOM FUNCTION TEST CASES ===\n";

// Test Case 81: Custom function with string argument (should return true)
run_test_case(81, 'my_custom_func("test string")', true, false);

// Test Case 82: Custom function with numeric argument (should return true)
run_test_case(82, 'my_custom_func(123)', true, false);

// Test Case 83: Custom function with boolean argument (should return true)
run_test_case(83, 'my_custom_func(true)', true, false);

// Test Case 84: Custom function with nested function call that returns false
run_test_case(84, 'my_custom_func(is_admin())', true, false);

// Test Case 85: Custom function with nested function call that returns true
run_test_case(85, 'my_custom_func(is_home())', true, false);

// Test Case 86: Negated custom function
run_test_case(86, '!my_custom_func("test")', false, false);

// Test Case 87: Custom function in AND expression
run_test_case(87, 'my_custom_func("test") && true', true, false);

// Test Case 88: Custom function in OR expression with false
run_test_case(88, 'my_custom_func("test") || false', true, false);

// Test Case 89: Custom function with null argument (should return true)
run_test_case(89, 'my_custom_func(null)', true, false);

// Test Case 90: Complex expression with custom function
run_test_case(90, '(my_custom_func("test") && is_home()) || (my_custom_func(false) && is_admin())', true, false);

echo "\n=== ERROR CONDITION TEST CASES ===\n";

// Test Case 91: Custom function with missing argument (should return false)
run_test_case(91, 'my_custom_func()', false, true);

// Test Case 92: Custom function with array argument (should return false)
run_test_case(92, 'my_custom_func(array())', false, false);

// Test Case 93: Custom function with object argument (should return false)
run_test_case(93, 'my_custom_func(new stdClass())', false, true);

// Test Case 94: Custom function with invalid function name
run_test_case(94, 'invalid_func_name("test")', false, true);

// Test Case 95: Custom function with malformed nested function call
run_test_case(95, 'my_custom_func(is_tag("test", "extra"))', false, true);

// Test Case 96: Custom function with unexpected end of argument
run_test_case(96, 'my_custom_func("test",', false, true);

// Test Case 97: Custom function with unclosed string in argument
run_test_case(97, 'my_custom_func("test)', false, true);

// Test Case 98: Custom function with numeric string argument (should return true)
run_test_case(98, 'my_custom_func("123")', true, false);

// Test Case 99: Custom function with boolean string argument (should return true)
run_test_case(99, 'my_custom_func("true")', true, false);

// Test Case 100: Custom function with empty array argument (should return true)
run_test_case(100, 'my_custom_func(array())', false, false);

// Test Case 101: Custom function with deeply nested function calls
run_test_case(101, 'my_custom_func(is_tag(is_home(is_admin())))', true, false);

// Test Case 102: Custom function with array argument (should return false)
run_test_case(102, 'my_custom_func([])', false, false);

echo "\n=== ARRAY ARGUMENT TEST CASES ===\n";

// Test Case 103: Simple array notation - traditional style
run_test_case(103, 'my_custom_func(array())', false, false);

// Test Case 104: Simple array notation - modern style
run_test_case(104, 'my_custom_func([])', false, false);

// Test Case 105: Array with elements - traditional style
run_test_case(105, 'my_custom_func(array(1, 2, 3))', false, false);

// Test Case 106: Array with elements - modern style
run_test_case(106, 'my_custom_func([1, 2, 3])', false, false);

// Test Case 107: Array with associative keys - traditional style
run_test_case(107, 'my_custom_func(array("a" => 1, "b" => 2))', false, false);

// Test Case 108: Array with associative keys - modern style
run_test_case(108, 'my_custom_func(["a" => 1, "b" => 2])', false, false);

// Test Case 109: Nested array - traditional style
run_test_case(109, 'my_custom_func(array(1, array(2, 3), 4))', false, false);

// Test Case 110: Nested array - modern style
run_test_case(110, 'my_custom_func([1, [2, 3], 4])', false, false);

// Test Case 111: Mixed notation - traditional outer, modern inner
run_test_case(111, 'my_custom_func(array(1, [2, 3], 4))', false, false);

// Test Case 112: Mixed notation - modern outer, traditional inner
run_test_case(112, 'my_custom_func([1, array(2, 3), 4])', false, false);

// Test Case 113: Deeply nested array - traditional style
run_test_case(113, 'my_custom_func(array(1, array(2, array(3, 4), 5), 6))', false, false);

// Test Case 114: Deeply nested array - modern style
run_test_case(114, 'my_custom_func([1, [2, [3, 4], 5], 6])', false, false);

// Test Case 115: Array with various value types
run_test_case(115, 'my_custom_func(array(1, "string", true, null, array()))', false, false);

// Test Case 116: Array with associative keys and nested structures
run_test_case(116, 'my_custom_func(array("a" => 1, "b" => array("c" => 2, "d" => [3, 4])))', false, false);

// Test Case 117: Array with escaped quotes in strings
run_test_case(117, 'my_custom_func(["key with \"quotes\"" => "value with \"quotes\""])', false, false);

// Test Case 118: Empty array passed to is_home
run_test_case(118, 'is_home([])', true, false);

// Test Case 119: Array with element passed to is_home
run_test_case(119, 'is_home(["test"])', true, false);

// Test Case 120: Array function inside array argument
run_test_case(120, 'my_custom_func([array(1, 2), array(3, 4)])', false, false);

// Test Case 121: Deeply nested mixed arrays
run_test_case(121, 'my_custom_func([1, array(2, [3, array(4, [5, 6])]), 7])', false, false);

echo "\n=== DIRECT ARRAY USAGE TEST CASES ===\n";

// Test Case 122: Direct array usage (traditional style) - should throw error
run_test_case(122, 'array(1, 2, 3)', false, true);

// Test Case 123: Direct array usage (modern style) - should throw error
run_test_case(123, '[1, 2, 3]', false, true);

// Test Case 124: Array in boolean expression (traditional style) - should throw error
run_test_case(124, 'array(1, 2, 3) && true', false, true);

// Test Case 125: Array in boolean expression (modern style) - should throw error
run_test_case(125, '[1, 2, 3] || false', false, true);

// Test Case 126: Complex expression with direct array - should throw error
run_test_case(126, '(is_home() && array("a", "b")) || is_admin()', false, true);

// Test Case 127: Nested direct arrays - should throw error
run_test_case(127, 'array(1, [2, 3], 4)', false, true);

echo "\n=== ARRAY WITH FUNCTION CALLS TEST CASES ===\n";

// Test Case 128: Array with allowed function call (traditional style)
run_test_case(128, 'my_custom_func(array(1, is_home(), 3))', false, false);

// Test Case 129: Array with allowed function call (modern style)
run_test_case(129, 'my_custom_func([1, is_admin(), 3])', false, false);

// Test Case 130: Array with nested allowed function calls
run_test_case(130, 'my_custom_func(array("key" => is_home(), "nested" => [is_admin(), is_page("about")]))', false, false);

// Test Case 131: Array with disallowed function call (traditional style)
run_test_case(131, 'my_custom_func(array(1, htmlspecialchars("test"), 3))', false, true);

// Test Case 132: Array with disallowed function call (modern style)
run_test_case(132, 'my_custom_func([1, file_get_contents("test"), 3])', false, true);

// Test Case 133: Mixed allowed and disallowed function calls in array
run_test_case(133, 'my_custom_func(array(is_home(), file_get_contents("test"), is_admin()))', false, true);

// Test Case 134: Deeply nested array with function calls
run_test_case(134, 'my_custom_func([1, ["nested" => array(is_home(), is_admin())], 3])', false, false);

// Test Case 135: Array with non-existent function call
run_test_case(135, 'my_custom_func(array(1, function_does_not_exist(), 3))', false, true);

// Test Case 136: Array with function calls as keys and values
run_test_case(136, 'my_custom_func([is_home() => is_admin(), "test" => is_page("about")])', false, true);

// Test Case 137: Array with complex nested function calls
run_test_case(137, 'my_custom_func(array("complex" => [is_home() => [is_admin() => is_page("about")]]))', false, true);

echo "\n=== SECURITY EXPLOIT TEST CASES ===\n";

// Test Case 138: Try to use system() in array key
run_test_case(138, 'my_custom_func(array(system("ls") => "value"))', false, true);

// Test Case 139: Try to use dangerous PHP stream wrapper
run_test_case(139, 'is_tag("php://filter/convert.base64-encode/resource=index.php")', false, false);

// Test Case 140: Try to use dangerous PHP stream wrapper in array value
run_test_case(140, 'my_custom_func(["key" => "php://input"])', false, true);

// Test Case 141: Try to use nested disallowed function in array
run_test_case(141, 'my_custom_func(array("key" => phpinfo()))', false, true);

// Test Case 142: Try to use deeply nested disallowed function
run_test_case(142, 'my_custom_func([1, ["nested" => [system("ls") => "value"]]])', false, true);

// Test Case 143: Try directory traversal attack
run_test_case(143, 'is_tag("../../../etc/passwd")', false, false);

// Test Case 144: Try to use disallowed function within allowed function arguments
run_test_case(144, 'is_tag(system("ls"))', false, true);

// Test Case 145: Try to execute code through function arguments
run_test_case(145, 'is_tag(""); system("ls"); //")', false, true);

// Test Case 146: Try to use special characters to confuse parser
run_test_case(146, 'my_custom_func(array("a\x00b" => "value"))', false, false);

// Test Case 147: Try to use variable interpolation
run_test_case(147, 'my_custom_func(["${phpinfo()}" => "value"])', false, false);

// Test Case 148: Try to use a malformed array to confuse parser
run_test_case(148, 'my_custom_func(array("key" => "value", ))', false, false);

// Test Case 149: Try to use multi-level array with disallowed function
run_test_case(149, 'my_custom_func(array("outer" => array("inner" => exec("id"))))', false, true);

// Test Case 150: Try to call disallowed function through fake array syntax
run_test_case(150, 'my_custom_func(array(1); phpinfo(); //))', false, true);

// Test Case 151: Try to inject shell command through array argument
run_test_case(151, 'my_custom_func(["`ls`" => "value"])', false, false);

echo "\n=== END OF TEST CASES ===\n";

// After all tests are done, check if there were any failures
echo "\n=== TEST SUMMARY ===\n";
if ($test_failures > 0) {
    echo "FAILED: {$test_failures} test case(s) failed.\n";
    exit(1); // Exit with failure code
} else {
    echo "SUCCESS: All test cases passed!\n";
    exit(0); // Exit with success code
}
//*/
