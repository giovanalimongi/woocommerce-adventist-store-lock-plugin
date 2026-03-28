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
        'notice_message'         => 'Purchases are temporarily unavailable during this period. Please come back after the scheduled reopening time.',
        'button_label'           => 'I understand',
        'show_close_button'      => '0',
        'show_countdown'         => '1',
        'block_page_interaction' => '1',
        'overlay_bg'             => 'rgba(15,17,34,0.78)',
        'box_bg'                 => '#ffffff',
        'text_color'             => '#111111',
        'button_bg'              => '#111827',
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
}
add_action( 'admin_init', 'wcasl_register_settings' );

function wcasl_sanitize_settings( $input ) {
    $output = wcasl_get_settings();
    $checkboxes = array( 'enabled', 'block_purchases', 'show_notice', 'show_modal', 'show_close_button', 'show_countdown', 'block_page_interaction' );
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

    $output['modal_image_id']    = absint( $input['modal_image_id'] ?? 0 );
    $output['modal_title']       = sanitize_text_field( $input['modal_title'] ?? '' );
    $output['modal_message']     = wp_kses_post( $input['modal_message'] ?? '' );
    $output['notice_message']    = wp_kses_post( $input['notice_message'] ?? '' );
    $output['button_label']      = sanitize_text_field( $input['button_label'] ?? '' );
    $output['overlay_bg']        = sanitize_text_field( $input['overlay_bg'] ?? '' );
    $output['box_bg']            = sanitize_hex_color( $input['box_bg'] ?? '' ) ?: '#ffffff';
    $output['text_color']        = sanitize_hex_color( $input['text_color'] ?? '' ) ?: '#111111';
    $output['button_bg']         = sanitize_hex_color( $input['button_bg'] ?? '' ) ?: '#111827';
    $output['button_text_color'] = sanitize_hex_color( $input['button_text_color'] ?? '' ) ?: '#ffffff';
    $output['custom_css']        = wp_strip_all_tags( $input['custom_css'] ?? '' );

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

function wcasl_toggle_field( $key, $label, $description = '' ) {
    $settings = wcasl_get_settings();
    ?>
    <label class="wcasl-toggle-card">
        <span class="wcasl-toggle-switch">
            <input type="checkbox" name="wcasl_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $settings[ $key ] ?? '0', '1' ); ?>>
            <span class="wcasl-toggle-slider"></span>
        </span>
        <span class="wcasl-toggle-content">
            <strong><?php echo esc_html( $label ); ?></strong>
            <?php if ( $description ) : ?>
                <small><?php echo esc_html( $description ); ?></small>
            <?php endif; ?>
        </span>
    </label>
    <?php
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

    $settings = wcasl_get_settings();
    $locked   = function_exists( 'wcasl_is_store_locked' ) && wcasl_is_store_locked();
    $timezone = wp_timezone_string() ?: 'UTC';
    $days     = wcasl_day_options();
    $types    = wcasl_modal_content_type_options();
    $image_id = ! empty( $settings['modal_image_id'] ) ? (int) $settings['modal_image_id'] : 0;
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
    ?>
    <div class="wrap wcasl-admin-page">
        <div class="wcasl-admin-hero">
            <div>
                <h1><?php esc_html_e( 'Store Lock', 'wcasl' ); ?></h1>
                <p><?php esc_html_e( 'Control recurring purchase blocking, shopper messaging, and popup appearance from one place.', 'wcasl' ); ?></p>
            </div>
            <div class="wcasl-admin-status <?php echo $locked ? 'is-locked' : 'is-open'; ?>">
                <span><?php esc_html_e( 'Store status', 'wcasl' ); ?></span>
                <strong><?php echo $locked ? esc_html__( 'Locked now', 'wcasl' ) : esc_html__( 'Open now', 'wcasl' ); ?></strong>
                <small><?php echo esc_html( $timezone ); ?></small>
            </div>
        </div>

        <div class="wcasl-admin-nav">
            <a href="#wcasl-general"><?php esc_html_e( 'General', 'wcasl' ); ?></a>
            <a href="#wcasl-blocking"><?php esc_html_e( 'Purchase blocking', 'wcasl' ); ?></a>
            <a href="#wcasl-popup"><?php esc_html_e( 'Popup', 'wcasl' ); ?></a>
            <a href="#wcasl-appearance"><?php esc_html_e( 'Appearance', 'wcasl' ); ?></a>
        </div>

        <form method="post" action="options.php" class="wcasl-admin-form">
            <?php settings_fields( 'wcasl_settings_group' ); ?>

            <div class="wcasl-admin-grid">
                <div class="wcasl-admin-column">
                    <section class="wcasl-card" id="wcasl-general">
                        <div class="wcasl-card-head"><span class="dashicons dashicons-admin-generic"></span><h2><?php esc_html_e( 'General settings', 'wcasl' ); ?></h2></div>
                        <div class="wcasl-card-body">
                            <?php wcasl_toggle_field( 'enabled', __( 'Plugin enabled', 'wcasl' ), __( 'Turn the store lock on or off without losing your schedule or content.', 'wcasl' ) ); ?>

                            <div class="wcasl-field-group">
                                <label for="wcasl-timezone"><?php esc_html_e( 'Timezone', 'wcasl' ); ?></label>
                                <input id="wcasl-timezone" type="text" value="<?php echo esc_attr( $timezone ); ?>" readonly>
                                <p class="description"><?php esc_html_e( 'The schedule follows the timezone configured in WordPress.', 'wcasl' ); ?></p>
                            </div>

                            <div class="wcasl-inline-grid wcasl-inline-grid--schedule">
                                <div class="wcasl-field-group">
                                    <label for="wcasl-start-day"><?php esc_html_e( 'Start day', 'wcasl' ); ?></label>
                                    <select id="wcasl-start-day" name="wcasl_settings[start_day]">
                                        <?php foreach ( $days as $value => $label ) : ?>
                                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['start_day'], $value ); ?>><?php echo esc_html( $label ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="wcasl-field-group">
                                    <label for="wcasl-start-time"><?php esc_html_e( 'Start time', 'wcasl' ); ?></label>
                                    <input id="wcasl-start-time" type="time" name="wcasl_settings[start_time]" value="<?php echo esc_attr( $settings['start_time'] ); ?>">
                                </div>
                                <div class="wcasl-field-group">
                                    <label for="wcasl-end-day"><?php esc_html_e( 'End day', 'wcasl' ); ?></label>
                                    <select id="wcasl-end-day" name="wcasl_settings[end_day]">
                                        <?php foreach ( $days as $value => $label ) : ?>
                                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['end_day'], $value ); ?>><?php echo esc_html( $label ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="wcasl-field-group">
                                    <label for="wcasl-end-time"><?php esc_html_e( 'End time', 'wcasl' ); ?></label>
                                    <input id="wcasl-end-time" type="time" name="wcasl_settings[end_time]" value="<?php echo esc_attr( $settings['end_time'] ); ?>">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="wcasl-card" id="wcasl-blocking">
                        <div class="wcasl-card-head"><span class="dashicons dashicons-cart"></span><h2><?php esc_html_e( 'Purchase blocking', 'wcasl' ); ?></h2></div>
                        <div class="wcasl-card-body">
                            <?php wcasl_toggle_field( 'block_purchases', __( 'Block checkout and purchases', 'wcasl' ), __( 'Prevents add-to-cart, disables purchase buttons, and protects checkout during the lock period.', 'wcasl' ) ); ?>
                            <?php wcasl_toggle_field( 'show_notice', __( 'Show cart and checkout notice', 'wcasl' ), __( 'Adds an error notice if someone tries to continue purchasing while the store is locked.', 'wcasl' ) ); ?>

                            <div class="wcasl-field-group">
                                <label for="wcasl-notice-message"><?php esc_html_e( 'Purchase blocking message', 'wcasl' ); ?></label>
                                <textarea id="wcasl-notice-message" name="wcasl_settings[notice_message]" rows="5"><?php echo esc_textarea( $settings['notice_message'] ); ?></textarea>
                                <p class="description"><?php esc_html_e( 'Displayed in WooCommerce notices when someone tries to purchase during the lock period.', 'wcasl' ); ?></p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="wcasl-admin-column">
                    <section class="wcasl-card" id="wcasl-popup">
                        <div class="wcasl-card-head"><span class="dashicons dashicons-format-image"></span><h2><?php esc_html_e( 'Popup customization', 'wcasl' ); ?></h2></div>
                        <div class="wcasl-card-body">
                            <?php wcasl_toggle_field( 'show_modal', __( 'Show popup during lock period', 'wcasl' ), __( 'Displays a full-screen modal while the store is unavailable.', 'wcasl' ) ); ?>

                            <div class="wcasl-field-group">
                                <label for="wcasl-modal-content-type"><?php esc_html_e( 'Popup content type', 'wcasl' ); ?></label>
                                <select id="wcasl-modal-content-type" name="wcasl_settings[modal_content_type]">
                                    <?php foreach ( $types as $value => $label ) : ?>
                                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['modal_content_type'], $value ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="wcasl-field-group">
                                <label for="wcasl-modal-title"><?php esc_html_e( 'Popup title', 'wcasl' ); ?></label>
                                <input id="wcasl-modal-title" type="text" name="wcasl_settings[modal_title]" value="<?php echo esc_attr( $settings['modal_title'] ); ?>">
                            </div>

                            <div class="wcasl-field-group">
                                <label for="wcasl-modal-copy"><?php esc_html_e( 'Popup message', 'wcasl' ); ?></label>
                                <textarea id="wcasl-modal-copy" name="wcasl_settings[modal_message]" rows="5"><?php echo esc_textarea( $settings['modal_message'] ); ?></textarea>
                            </div>

                            <div class="wcasl-field-group">
                                <label><?php esc_html_e( 'Popup image', 'wcasl' ); ?></label>
                                <input type="hidden" id="wcasl-modal-image-id" name="wcasl_settings[modal_image_id]" value="<?php echo esc_attr( $image_id ); ?>">
                                <div id="wcasl-modal-image-preview" class="wcasl-image-dropzone <?php echo $image_url ? 'has-image' : ''; ?>">
                                    <?php if ( $image_url ) : ?>
                                        <img src="<?php echo esc_url( $image_url ); ?>" alt="" />
                                    <?php else : ?>
                                        <span><?php esc_html_e( 'No image selected', 'wcasl' ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="wcasl-image-actions">
                                    <button type="button" class="button button-secondary" id="wcasl-select-image"><?php esc_html_e( 'Select image', 'wcasl' ); ?></button>
                                    <button type="button" class="button button-link-delete" id="wcasl-remove-image"><?php esc_html_e( 'Remove image', 'wcasl' ); ?></button>
                                </div>
                            </div>

                            <div class="wcasl-inline-grid">
                                <div class="wcasl-field-group">
                                    <label for="wcasl-button-label"><?php esc_html_e( 'Button label', 'wcasl' ); ?></label>
                                    <input id="wcasl-button-label" type="text" name="wcasl_settings[button_label]" value="<?php echo esc_attr( $settings['button_label'] ); ?>">
                                </div>
                            </div>

                            <?php wcasl_toggle_field( 'show_countdown', __( 'Show countdown', 'wcasl' ), __( 'Displays hours, minutes, and seconds remaining until the store reopens.', 'wcasl' ) ); ?>
                            <?php wcasl_toggle_field( 'show_close_button', __( 'Show close button', 'wcasl' ), __( 'Allow visitors to dismiss the popup while the lock is active.', 'wcasl' ) ); ?>
                            <?php wcasl_toggle_field( 'block_page_interaction', __( 'Blur and lock background', 'wcasl' ), __( 'Prevents scrolling and keeps focus on the popup during the lock period.', 'wcasl' ) ); ?>
                        </div>
                    </section>

                    <section class="wcasl-card" id="wcasl-appearance">
                        <div class="wcasl-card-head"><span class="dashicons dashicons-art"></span><h2><?php esc_html_e( 'Appearance', 'wcasl' ); ?></h2></div>
                        <div class="wcasl-card-body">
                            <div class="wcasl-inline-grid wcasl-inline-grid--colors">
                                <div class="wcasl-field-group">
                                    <label for="wcasl-overlay-bg"><?php esc_html_e( 'Overlay background', 'wcasl' ); ?></label>
                                    <input id="wcasl-overlay-bg" type="text" name="wcasl_settings[overlay_bg]" value="<?php echo esc_attr( $settings['overlay_bg'] ); ?>">
                                    <p class="description"><?php esc_html_e( 'Accepts rgba values for transparency.', 'wcasl' ); ?></p>
                                </div>
                                <div class="wcasl-field-group">
                                    <label for="wcasl-box-bg"><?php esc_html_e( 'Popup background', 'wcasl' ); ?></label>
                                    <input id="wcasl-box-bg" class="wcasl-color-field" type="text" name="wcasl_settings[box_bg]" value="<?php echo esc_attr( $settings['box_bg'] ); ?>">
                                </div>
                                <div class="wcasl-field-group">
                                    <label for="wcasl-text-color"><?php esc_html_e( 'Text color', 'wcasl' ); ?></label>
                                    <input id="wcasl-text-color" class="wcasl-color-field" type="text" name="wcasl_settings[text_color]" value="<?php echo esc_attr( $settings['text_color'] ); ?>">
                                </div>
                                <div class="wcasl-field-group">
                                    <label for="wcasl-button-bg"><?php esc_html_e( 'Button background', 'wcasl' ); ?></label>
                                    <input id="wcasl-button-bg" class="wcasl-color-field" type="text" name="wcasl_settings[button_bg]" value="<?php echo esc_attr( $settings['button_bg'] ); ?>">
                                </div>
                                <div class="wcasl-field-group">
                                    <label for="wcasl-button-text-color"><?php esc_html_e( 'Button text color', 'wcasl' ); ?></label>
                                    <input id="wcasl-button-text-color" class="wcasl-color-field" type="text" name="wcasl_settings[button_text_color]" value="<?php echo esc_attr( $settings['button_text_color'] ); ?>">
                                </div>
                            </div>

                            <div class="wcasl-field-group">
                                <label for="wcasl-custom-css"><?php esc_html_e( 'Custom CSS', 'wcasl' ); ?></label>
                                <textarea id="wcasl-custom-css" name="wcasl_settings[custom_css]" rows="5"><?php echo esc_textarea( $settings['custom_css'] ); ?></textarea>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="wcasl-admin-actions">
                <?php submit_button( __( 'Save changes', 'wcasl' ), 'primary', 'submit', false ); ?>
            </div>
        </form>
    </div>
    <?php
}

function wcasl_admin_enqueue_assets( $hook ) {
    if ( 'woocommerce_page_wcasl-settings' !== $hook && 'settings_page_wcasl-settings' !== $hook ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style( 'wcasl-admin', WCASL_PLUGIN_URL . 'assets/css/admin.css', array( 'wp-color-picker' ), WCASL_VERSION );
    wp_enqueue_script(
        'wcasl-admin-media',
        WCASL_PLUGIN_URL . 'assets/js/admin-media.js',
        array( 'jquery', 'wp-color-picker' ),
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
            'emptyImage'  => __( 'No image selected', 'wcasl' ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'wcasl_admin_enqueue_assets' );
