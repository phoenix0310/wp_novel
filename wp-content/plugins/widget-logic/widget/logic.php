<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once 'logic/tokenizer.php';
include_once 'logic/parser.php';

/**
 * Main function to check widget logic expressions
 */
function widget_logic_check_logic($logic)
{
    $allowed_functions = array(
        // Main page checks
        'is_home', 'is_front_page', 'is_admin',

        // Single post/page checks
        'is_single', 'is_page', 'is_singular', 'is_sticky', 'is_attachment', 'is_tree',

        // Category, Tag & Taxonomy checks
        'is_category', 'is_tag', 'is_tax', 'in_category', 'has_tag', 'has_term',
        'is_product_category', 'taxonomy_exists', 'has_category',

        // Archive checks
        'is_archive', 'is_post_type_archive', 'is_author', 'is_multi_author',
        'is_date', 'is_year', 'is_month', 'is_day', 'is_time',

        // Special page checks
        'is_search', 'is_404', 'is_privacy_policy', 'is_page_template',

        // Post type checks
        'get_post_type', 'post_type_exists', 'is_post_type_hierarchical', 'has_post_format',

        // User & capability checks
        'is_user_logged_in', 'current_user_can', 'is_super_admin',

        // Sidebar & widget checks
        'is_active_sidebar', 'has_nav_menu', 'in_the_loop',

        // Multisite checks
        'is_multisite', 'is_main_site',

        // Plugin & theme checks
        'is_plugin_active', 'is_child_theme', 'current_theme_supports',

        // Feed & preview checks
        'is_feed', 'is_trackback', 'is_preview',

        // Content checks
        'has_excerpt', 'comments_open', 'pings_open', 'is_new_day',
        'has_post_thumbnail', 'has_shortcode', 'has_block', 'get_post_format',

        // Device & request checks
        'wp_is_mobile', 'is_rtl', 'is_customize_preview', 'wp_doing_ajax',

        // Error & validation checks
        'is_wp_error', 'is_email', 'is_serialized',

        // Query checks
        'is_main_query', 'is_paged',

        // WooCommerce conditional tags
        'is_woocommerce', 'is_shop', 'is_product', 'is_product_category',
        'is_product_tag', 'is_cart', 'is_checkout', 'is_account_page',
        'is_wc_endpoint_url',
    );

    $allowed_functions = apply_filters('widget_logic_allowed_functions', $allowed_functions);

    $logic = trim((string) $logic);
    if ('' === $logic) {
        return true;
    }

    // Set up error handling
    set_error_handler('widget_logic_error_handler', E_WARNING | E_USER_WARNING);  // @codingStandardsIgnoreLine - we need this for error handling

    try {
        // Tokenize the logic string
        $tokens = widget_logic_tokenize($logic);

        // Parse and evaluate the expression
        $pos = 0;
        $result = widget_logic_parse_expression($tokens, $pos, $allowed_functions);

        // Check if there are any unexpected tokens after the expression
        if ($pos < count($tokens)) {
            throw new Exception(esc_html__('Widget Logic: Unexpected tokens after expression.', 'widget-logic'));
        }

        return (bool)$result;
    } catch (Exception $e) {
        widget_logic_error_handler(E_USER_WARNING, $e->getMessage());
        return false;
    } finally {
        restore_error_handler();
    }
}

/**
 * Generic error handler for widget logic
 */
function widget_logic_error_handler($errno, $errstr)
{
    global $wl_options;

    // For testing, we want to see all errors
    $show_errors = true;

    // In normal operation, respect user settings
    if (!defined('WIDGET_LOGIC_TESTING')) {
        $show_errors = !empty($wl_options['widget_logic-options-show_errors']) && current_user_can('manage_options');
    }

    if ($show_errors) {
        echo 'Invalid Widget Logic: ' . esc_html($errstr);
    }

    return true;
}

function widget_logic_by_id($widget_id)
{
    global $wl_options;

    if (preg_match('/^(.+)-(\d+)$/', $widget_id, $m)) {
        $widget_class = $m[1];
        $widget_i = $m[2];

        $info = get_option('widget_' . $widget_class);
        if (empty($info[$widget_i])) {
            return '';
        }

        $info = $info[$widget_i];
    } else {
        $info = (array) get_option('widget_' . $widget_id, array());
    }

    if (isset($info['widget_logic'])) {
        $logic = $info['widget_logic'];
    } elseif (isset($wl_options[$widget_id])) {
        $logic = stripslashes($wl_options[$widget_id]);
        widget_logic_save($widget_id, $logic);

        unset($wl_options[$widget_id]);
        update_option('widget_logic', $wl_options);
    } else {
        $logic = '';
    }

    return $logic;
}

function widget_logic_save($widget_id, $logic)
{
    global $wl_options;

    if (preg_match('/^(.+)-(\d+)$/', $widget_id, $m)) {
        $widget_class = $m[1];
        $widget_i = $m[2];

        $info = get_option('widget_' . $widget_class);
        if (!is_array($info[$widget_i])) {
            $info[$widget_i] = array();
        }

        $info[$widget_i]['widget_logic'] = $logic;
        update_option('widget_' . $widget_class, $info);
    } elseif (
        isset($_POST['widget_logic_nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['widget_logic_nonce'])), 'widget_logic_save')
    ) {
        $info                 = (array) get_option('widget_' . $widget_id, array());
        $info['widget_logic'] = $logic;
        update_option('widget_' . $widget_id, $info);
    }
}
