<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcasl_day_to_int( $day ) {
    $map = array(
        'sun' => 0,
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
    );

    return $map[ $day ] ?? 0;
}

function wcasl_time_to_minutes( $time ) {
    $parts = explode( ':', $time );
    $hour = isset( $parts[0] ) ? (int) $parts[0] : 0;
    $minute = isset( $parts[1] ) ? (int) $parts[1] : 0;

    return ( $hour * 60 ) + $minute;
}

function wcasl_is_store_locked() {
    $settings = wcasl_get_settings();

    if ( empty( $settings['enabled'] ) || '1' !== $settings['enabled'] ) {
        return false;
    }

    $now = current_datetime();

    $current_day = (int) $now->format( 'w' );
    $current_minutes = ( (int) $now->format( 'H' ) * 60 ) + (int) $now->format( 'i' );
    $current_position = ( $current_day * 1440 ) + $current_minutes;

    $start_position = ( wcasl_day_to_int( $settings['start_day'] ) * 1440 ) + wcasl_time_to_minutes( $settings['start_time'] );
    $end_position   = ( wcasl_day_to_int( $settings['end_day'] ) * 1440 ) + wcasl_time_to_minutes( $settings['end_time'] );

    if ( $start_position < $end_position ) {
        return ( $current_position >= $start_position && $current_position < $end_position );
    }

    if ( $start_position > $end_position ) {
        return ( $current_position >= $start_position || $current_position < $end_position );
    }

    return false;
}
