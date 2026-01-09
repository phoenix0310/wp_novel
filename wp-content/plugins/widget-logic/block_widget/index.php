<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

function widget_logic_register_block()
{
    register_logic_live_match_widget_service();
    register_block_type(__DIR__ . '/block.json');
}
add_action('init', 'widget_logic_register_block');

// Register block scripts and styles.
function widget_logic_widget_enqueue_block_editor_assets()
{
    if (is_admin()) {
        $isWidgetScreen = 'widgets' === get_current_screen()->id;
        wp_enqueue_script(
            'block-widget',
            esc_url(plugins_url('/js/widget.js', __FILE__)),
            ['wp-blocks', 'wp-i18n', 'wp-element', $isWidgetScreen ? 'wp-edit-widgets' : 'wp-editor'],
            filemtime(plugin_dir_path(__FILE__) . '/js/widget.js'),
            false // Do not enqueue the script in the footer.
        );
        wp_add_inline_script(
            'block-widget',
            'var myPluginBlockImageUrl = "' . plugins_url('preview.png', __FILE__) . '";',
            'before'
        );
    }
    wp_enqueue_style(
        'block-widget',
        esc_url(plugins_url('/css/widget.css', __FILE__)),
        array(),
        filemtime(plugin_dir_path(__FILE__) . '/css/widget.css')
    );
}
add_action('enqueue_block_assets', 'widget_logic_widget_enqueue_block_editor_assets');
