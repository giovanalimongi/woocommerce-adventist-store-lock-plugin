<?php
/**
 * Plugin Name: WooCommerce Adventist Store Lock
 * Description: Blocks purchases on a recurring weekly schedule and displays a native full-screen modal during the blocked period.
 * Version: 1.2.0
 * Author: Giovana Limongi
 * License: MIT
 * Text Domain: wcasl
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WCASL_VERSION', '1.2.0' );
define( 'WCASL_PLUGIN_FILE', __FILE__ );
define( 'WCASL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCASL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once WCASL_PLUGIN_PATH . 'includes/settings.php';
require_once WCASL_PLUGIN_PATH . 'includes/schedule.php';
require_once WCASL_PLUGIN_PATH . 'includes/woocommerce-lock.php';
require_once WCASL_PLUGIN_PATH . 'includes/native-modal.php';

register_activation_hook( __FILE__, 'wcasl_activate' );

function wcasl_activate() {
    $defaults = array(
        'enabled'                => '1',
        'block_purchases'        => '1',
        'show_notice'            => '1',
        'show_modal'             => '1',
        'start_day'              => 'fri',
        'start_time'             => '18:00',
        'end_day'                => 'sat',
        'end_time'               => '18:00',
        'modal_content_type'     => 'text',
        'modal_image_id'         => 0,
        'modal_title'            => 'Store temporarily unavailable',
        'modal_message'          => 'Purchases are temporarily unavailable during this period. Please come back after the scheduled reopening time.',
        'button_label'           => 'I understand',
        'show_close_button'      => '0',
        'block_page_interaction' => '1',
        'overlay_bg'             => 'rgba(0,0,0,0.72)',
        'box_bg'                 => '#ffffff',
        'text_color'             => '#111111',
        'button_bg'              => '#111111',
        'button_text_color'      => '#ffffff',
        'custom_css'             => '',
    );

    $current = get_option( 'wcasl_settings', array() );
    update_option( 'wcasl_settings', wp_parse_args( $current, $defaults ) );
}

function wcasl_plugin_action_links( $links ) {
    $settings_url = admin_url( 'admin.php?page=wcasl-settings' );
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        esc_url( $settings_url ),
        esc_html__( 'Settings', 'wcasl' )
    );

    array_unshift( $links, $settings_link );

    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcasl_plugin_action_links' );
