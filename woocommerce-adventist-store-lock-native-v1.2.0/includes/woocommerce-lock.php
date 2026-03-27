<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcasl_filter_is_purchasable( $purchasable, $product ) {
    $settings = wcasl_get_settings();

    if ( '1' === $settings['block_purchases'] && wcasl_is_store_locked() ) {
        return false;
    }

    return $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'wcasl_filter_is_purchasable', 10, 2 );

function wcasl_validate_add_to_cart( $passed, $product_id, $quantity ) {
    $settings = wcasl_get_settings();

    if ( '1' === $settings['block_purchases'] && wcasl_is_store_locked() ) {
        wc_add_notice( wp_kses_post( $settings['modal_message'] ), 'error' );
        return false;
    }

    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'wcasl_validate_add_to_cart', 10, 3 );

function wcasl_remove_purchase_buttons() {
    $settings = wcasl_get_settings();

    if ( '1' !== $settings['block_purchases'] || ! wcasl_is_store_locked() ) {
        return;
    }

    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
}
add_action( 'wp', 'wcasl_remove_purchase_buttons' );

function wcasl_add_store_locked_notice() {
    $settings = wcasl_get_settings();

    if ( '1' !== $settings['show_notice'] || ! wcasl_is_store_locked() ) {
        return;
    }

    $message = wp_kses_post( $settings['modal_message'] );

    if ( function_exists( 'is_cart' ) && is_cart() ) {
        wc_add_notice( $message, 'error' );
    }

    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        wc_add_notice( $message, 'error' );
    }
}
add_action( 'template_redirect', 'wcasl_add_store_locked_notice' );
