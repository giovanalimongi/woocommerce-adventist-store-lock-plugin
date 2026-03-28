<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcasl_enqueue_modal_assets() {
    $settings = wcasl_get_settings();

    if ( '1' !== $settings['show_modal'] || ! wcasl_is_store_locked() || is_admin() ) {
        return;
    }

    $unlock_datetime = wcasl_get_next_unlock_datetime();

    wp_enqueue_style( 'wcasl-modal', WCASL_PLUGIN_URL . 'assets/css/modal.css', array(), WCASL_VERSION );
    wp_enqueue_script( 'wcasl-modal', WCASL_PLUGIN_URL . 'assets/js/modal.js', array(), WCASL_VERSION, true );

    wp_localize_script(
        'wcasl-modal',
        'wcaslModal',
        array(
            'showCloseButton'      => ( '1' === $settings['show_close_button'] ),
            'blockPageInteraction' => ( '1' === $settings['block_page_interaction'] ),
            'showCountdown'        => ( '1' === $settings['show_countdown'] && $unlock_datetime instanceof DateTimeInterface ),
            'unlockAt'             => $unlock_datetime instanceof DateTimeInterface ? $unlock_datetime->getTimestamp() : 0,
            'countdownExpired'     => __( 'Store reopening now', 'wcasl' ),
        )
    );

    $custom_css = sprintf(
        ':root{--wcasl-overlay-bg:%1$s;--wcasl-box-bg:%2$s;--wcasl-text-color:%3$s;--wcasl-button-bg:%4$s;--wcasl-button-text:%5$s;}',
        esc_attr( $settings['overlay_bg'] ),
        esc_attr( $settings['box_bg'] ),
        esc_attr( $settings['text_color'] ),
        esc_attr( $settings['button_bg'] ),
        esc_attr( $settings['button_text_color'] )
    );

    if ( ! empty( $settings['custom_css'] ) ) {
        $custom_css .= $settings['custom_css'];
    }

    wp_add_inline_style( 'wcasl-modal', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'wcasl_enqueue_modal_assets' );

function wcasl_render_modal() {
    $settings = wcasl_get_settings();

    if ( '1' !== $settings['show_modal'] || ! wcasl_is_store_locked() || is_admin() ) {
        return;
    }

    $content_type = $settings['modal_content_type'] ?? 'text';
    $image_id     = ! empty( $settings['modal_image_id'] ) ? (int) $settings['modal_image_id'] : 0;
    $image_html   = $image_id ? wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'wcasl-modal-image', 'loading' => 'eager' ) ) : '';
    $show_text    = in_array( $content_type, array( 'text', 'image_text' ), true );
    $show_image   = in_array( $content_type, array( 'image', 'image_text' ), true ) && ! empty( $image_html );
    ?>
    <div class="wcasl-modal-overlay" id="wcasl-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="wcasl-modal-title">
        <div class="wcasl-modal-box<?php echo $show_image && ! $show_text ? ' wcasl-modal-box--image-only' : ''; ?>">
            <?php if ( '1' === $settings['show_close_button'] ) : ?>
                <button type="button" class="wcasl-modal-close" id="wcasl-modal-close" aria-label="<?php esc_attr_e( 'Close modal', 'wcasl' ); ?>">&times;</button>
            <?php endif; ?>

            <?php if ( $show_image ) : ?>
                <div class="wcasl-modal-image-wrap"><?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php endif; ?>

            <?php if ( $show_text ) : ?>
                <?php if ( ! empty( $settings['modal_title'] ) ) : ?>
                    <h2 id="wcasl-modal-title"><?php echo esc_html( $settings['modal_title'] ); ?></h2>
                <?php endif; ?>
                <?php if ( ! empty( $settings['modal_message'] ) ) : ?>
                    <div class="wcasl-modal-message"><?php echo wpautop( wp_kses_post( $settings['modal_message'] ) ); ?></div>
                <?php endif; ?>
            <?php else : ?>
                <span id="wcasl-modal-title" class="screen-reader-text"><?php echo esc_html( $settings['modal_title'] ?: __( 'Store lock modal', 'wcasl' ) ); ?></span>
            <?php endif; ?>

            <?php if ( '1' === $settings['show_countdown'] ) : ?>
                <div class="wcasl-countdown" aria-live="polite">
                    <span class="wcasl-countdown-label"><?php esc_html_e( 'Store reopens in', 'wcasl' ); ?></span>
                    <div class="wcasl-countdown-grid" id="wcasl-countdown-grid">
                        <div class="wcasl-countdown-item"><strong id="wcasl-countdown-hours">00</strong><span><?php esc_html_e( 'Hours', 'wcasl' ); ?></span></div>
                        <div class="wcasl-countdown-item"><strong id="wcasl-countdown-minutes">00</strong><span><?php esc_html_e( 'Minutes', 'wcasl' ); ?></span></div>
                        <div class="wcasl-countdown-item"><strong id="wcasl-countdown-seconds">00</strong><span><?php esc_html_e( 'Seconds', 'wcasl' ); ?></span></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( '' !== trim( (string) $settings['button_label'] ) ) : ?>
                <button type="button" class="wcasl-modal-button" id="wcasl-modal-button"><?php echo esc_html( $settings['button_label'] ); ?></button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'wcasl_render_modal', 100 );
