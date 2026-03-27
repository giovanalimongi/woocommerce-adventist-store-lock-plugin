<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcasl_get_settings() {
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

    $settings = get_option( 'wcasl_settings', array() );
    return wp_parse_args( $settings, $defaults );
}

function wcasl_register_settings() {
    register_setting(
        'wcasl_settings_group',
        'wcasl_settings',
        array(
            'sanitize_callback' => 'wcasl_sanitize_settings',
        )
    );

    add_settings_section(
        'wcasl_general_section',
        __( 'General settings', 'wcasl' ),
        '__return_false',
        'wcasl'
    );

    add_settings_section(
        'wcasl_schedule_section',
        __( 'Weekly schedule', 'wcasl' ),
        'wcasl_render_schedule_help',
        'wcasl'
    );

    add_settings_section(
        'wcasl_modal_section',
        __( 'Native modal', 'wcasl' ),
        '__return_false',
        'wcasl'
    );

    $fields = array(
        'enabled'                => __( 'Enable store lock', 'wcasl' ),
        'block_purchases'        => __( 'Block purchases', 'wcasl' ),
        'show_notice'            => __( 'Show cart/checkout notice', 'wcasl' ),
        'show_modal'             => __( 'Show native modal', 'wcasl' ),
        'start_day'              => __( 'Start day', 'wcasl' ),
        'start_time'             => __( 'Start time', 'wcasl' ),
        'end_day'                => __( 'End day', 'wcasl' ),
        'end_time'               => __( 'End time', 'wcasl' ),
        'modal_content_type'     => __( 'Modal content type', 'wcasl' ),
        'modal_image_id'         => __( 'Modal image', 'wcasl' ),
        'modal_title'            => __( 'Modal title', 'wcasl' ),
        'modal_message'          => __( 'Modal message', 'wcasl' ),
        'button_label'           => __( 'Button label', 'wcasl' ),
        'show_close_button'      => __( 'Show close button', 'wcasl' ),
        'block_page_interaction' => __( 'Block page interaction', 'wcasl' ),
        'overlay_bg'             => __( 'Overlay background', 'wcasl' ),
        'box_bg'                 => __( 'Modal background', 'wcasl' ),
        'text_color'             => __( 'Text color', 'wcasl' ),
        'button_bg'              => __( 'Button background', 'wcasl' ),
        'button_text_color'      => __( 'Button text color', 'wcasl' ),
        'custom_css'             => __( 'Custom CSS', 'wcasl' ),
    );

    foreach ( $fields as $key => $label ) {
        $section = 'wcasl_general_section';
        if ( in_array( $key, array( 'start_day', 'start_time', 'end_day', 'end_time' ), true ) ) {
            $section = 'wcasl_schedule_section';
        }
        if ( in_array( $key, array( 'modal_content_type', 'modal_image_id', 'modal_title', 'modal_message', 'button_label', 'show_close_button', 'block_page_interaction', 'overlay_bg', 'box_bg', 'text_color', 'button_bg', 'button_text_color', 'custom_css' ), true ) ) {
            $section = 'wcasl_modal_section';
        }

        add_settings_field(
            'wcasl_' . $key,
            $label,
            'wcasl_render_field',
            'wcasl',
            $section,
            array( 'key' => $key )
        );
    }
}
add_action( 'admin_init', 'wcasl_register_settings' );

function wcasl_render_schedule_help() {
    echo '<p>' . esc_html__( 'The schedule is recurring every week and uses the timezone configured in WordPress.', 'wcasl' ) . '</p>';
}

function wcasl_sanitize_settings( $input ) {
    $output = wcasl_get_settings();
    $checkboxes = array( 'enabled', 'block_purchases', 'show_notice', 'show_modal', 'show_close_button', 'block_page_interaction' );
    foreach ( $checkboxes as $key ) {
        $output[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
    }

    $allowed_days = array( 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' );
    $allowed_content_types = array( 'text', 'image', 'image_text' );

    $output['start_day']          = in_array( $input['start_day'] ?? '', $allowed_days, true ) ? $input['start_day'] : 'fri';
    $output['end_day']            = in_array( $input['end_day'] ?? '', $allowed_days, true ) ? $input['end_day'] : 'sat';
    $output['modal_content_type'] = in_array( $input['modal_content_type'] ?? '', $allowed_content_types, true ) ? $input['modal_content_type'] : 'text';

    $output['start_time'] = preg_match( '/^\d{2}:\d{2}$/', $input['start_time'] ?? '' ) ? $input['start_time'] : '18:00';
    $output['end_time']   = preg_match( '/^\d{2}:\d{2}$/', $input['end_time'] ?? '' ) ? $input['end_time'] : '18:00';

    $output['modal_image_id']     = absint( $input['modal_image_id'] ?? 0 );
    $output['modal_title']        = sanitize_text_field( $input['modal_title'] ?? '' );
    $output['modal_message']      = wp_kses_post( $input['modal_message'] ?? '' );
    $output['button_label']       = sanitize_text_field( $input['button_label'] ?? '' );
    $output['overlay_bg']         = sanitize_text_field( $input['overlay_bg'] ?? '' );
    $output['box_bg']             = sanitize_text_field( $input['box_bg'] ?? '' );
    $output['text_color']         = sanitize_text_field( $input['text_color'] ?? '' );
    $output['button_bg']          = sanitize_text_field( $input['button_bg'] ?? '' );
    $output['button_text_color']  = sanitize_text_field( $input['button_text_color'] ?? '' );
    $output['custom_css']         = wp_strip_all_tags( $input['custom_css'] ?? '' );

    return $output;
}

function wcasl_day_options() {
    return array(
        'sun' => __( 'Sunday', 'wcasl' ),
        'mon' => __( 'Monday', 'wcasl' ),
        'tue' => __( 'Tuesday', 'wcasl' ),
        'wed' => __( 'Wednesday', 'wcasl' ),
        'thu' => __( 'Thursday', 'wcasl' ),
        'fri' => __( 'Friday', 'wcasl' ),
        'sat' => __( 'Saturday', 'wcasl' ),
    );
}

function wcasl_modal_content_type_options() {
    return array(
        'text'       => __( 'Text only', 'wcasl' ),
        'image'      => __( 'Image only', 'wcasl' ),
        'image_text' => __( 'Image + text', 'wcasl' ),
    );
}

function wcasl_render_field( $args ) {
    $key = $args['key'];
    $settings = wcasl_get_settings();
    $value = $settings[ $key ] ?? '';
    $name = 'wcasl_settings[' . esc_attr( $key ) . ']';

    switch ( $key ) {
        case 'enabled':
        case 'block_purchases':
        case 'show_notice':
        case 'show_modal':
        case 'show_close_button':
        case 'block_page_interaction':
            echo '<label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . checked( $value, '1', false ) . '> ' . esc_html__( 'Enabled', 'wcasl' ) . '</label>';
            break;

        case 'start_day':
        case 'end_day':
            echo '<select name="' . esc_attr( $name ) . '">';
            foreach ( wcasl_day_options() as $day_key => $label ) {
                echo '<option value="' . esc_attr( $day_key ) . '" ' . selected( $value, $day_key, false ) . '>' . esc_html( $label ) . '</option>';
            }
            echo '</select>';
            break;

        case 'modal_content_type':
            echo '<select name="' . esc_attr( $name ) . '">';
            foreach ( wcasl_modal_content_type_options() as $type_key => $label ) {
                echo '<option value="' . esc_attr( $type_key ) . '" ' . selected( $value, $type_key, false ) . '>' . esc_html( $label ) . '</option>';
            }
            echo '</select>';
            echo '<p class="description">' . esc_html__( 'Choose whether the modal should show text, an image, or both.', 'wcasl' ) . '</p>';
            break;

        case 'start_time':
        case 'end_time':
            echo '<input type="time" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
            break;

        case 'modal_image_id':
            $image_url = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
            echo '<input type="hidden" id="wcasl-modal-image-id" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
            echo '<div id="wcasl-modal-image-preview" style="margin-bottom:10px;">';
            if ( $image_url ) {
                echo '<img src="' . esc_url( $image_url ) . '" alt="" style="max-width:240px;height:auto;display:block;border:1px solid #dcdcde;padding:4px;background:#fff;">';
            }
            echo '</div>';
            echo '<button type="button" class="button" id="wcasl-select-image">' . esc_html__( 'Select image', 'wcasl' ) . '</button> ';
            echo '<button type="button" class="button" id="wcasl-remove-image">' . esc_html__( 'Remove image', 'wcasl' ) . '</button>';
            echo '<p class="description">' . esc_html__( 'Recommended for visual notices or full-image lock screens.', 'wcasl' ) . '</p>';
            break;

        case 'modal_message':
        case 'custom_css':
            echo '<textarea name="' . esc_attr( $name ) . '" rows="5" cols="60" class="large-text">' . esc_textarea( $value ) . '</textarea>';
            break;

        case 'overlay_bg':
        case 'box_bg':
        case 'text_color':
        case 'button_bg':
        case 'button_text_color':
            echo '<input type="text" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
            echo '<p class="description">' . esc_html__( 'Supports hex, rgb, or rgba values.', 'wcasl' ) . '</p>';
            break;

        default:
            echo '<input type="text" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
            break;
    }
}

function wcasl_add_settings_page() {
    $parent = post_type_exists( 'product' ) ? 'woocommerce' : 'options-general.php';

    add_submenu_page(
        $parent,
        __( 'Store Lock', 'wcasl' ),
        __( 'Store Lock', 'wcasl' ),
        'manage_options',
        'wcasl-settings',
        'wcasl_render_settings_page'
    );
}
add_action( 'admin_menu', 'wcasl_add_settings_page' );

function wcasl_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $locked = function_exists( 'wcasl_is_store_locked' ) && wcasl_is_store_locked();
    $timezone = wp_timezone_string() ?: 'UTC';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'WooCommerce Adventist Store Lock', 'wcasl' ); ?></h1>
        <p><?php esc_html_e( 'Use this page to configure the recurring store lock schedule and native modal.', 'wcasl' ); ?></p>
        <div style="background:#fff;border:1px solid #ccd0d4;padding:12px 16px;margin:16px 0;max-width:760px;">
            <strong><?php esc_html_e( 'Store locked right now:', 'wcasl' ); ?></strong>
            <?php echo $locked ? '<span style="color:#b32d2e;">' . esc_html__( 'Yes', 'wcasl' ) . '</span>' : '<span style="color:#008a20;">' . esc_html__( 'No', 'wcasl' ) . '</span>'; ?>
            <br>
            <strong><?php esc_html_e( 'WordPress timezone:', 'wcasl' ); ?></strong>
            <?php echo esc_html( $timezone ); ?>
        </div>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'wcasl_settings_group' );
            do_settings_sections( 'wcasl' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function wcasl_admin_enqueue_assets( $hook ) {
    if ( 'woocommerce_page_wcasl-settings' !== $hook && 'settings_page_wcasl-settings' !== $hook ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script(
        'wcasl-admin-media',
        WCASL_PLUGIN_URL . 'assets/js/admin-media.js',
        array( 'jquery' ),
        WCASL_VERSION,
        true
    );

    wp_localize_script(
        'wcasl-admin-media',
        'wcaslAdminMedia',
        array(
            'title'       => __( 'Select modal image', 'wcasl' ),
            'button'      => __( 'Use this image', 'wcasl' ),
            'previewText' => __( 'Selected image preview', 'wcasl' ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'wcasl_admin_enqueue_assets' );
