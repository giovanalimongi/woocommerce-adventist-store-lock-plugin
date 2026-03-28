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
    $parts  = explode( ':', (string) $time );
    $hour   = isset( $parts[0] ) ? (int) $parts[0] : 0;
    $minute = isset( $parts[1] ) ? (int) $parts[1] : 0;

    return ( $hour * 60 ) + $minute;
}

function wcasl_get_current_position() {
    $now = current_datetime();

    return ( (int) $now->format( 'w' ) * 1440 ) + ( (int) $now->format( 'H' ) * 60 ) + (int) $now->format( 'i' );
}

function wcasl_get_schedule_positions() {
    $settings = wcasl_get_settings();

    return array(
        'start' => ( wcasl_day_to_int( $settings['start_day'] ) * 1440 ) + wcasl_time_to_minutes( $settings['start_time'] ),
        'end'   => ( wcasl_day_to_int( $settings['end_day'] ) * 1440 ) + wcasl_time_to_minutes( $settings['end_time'] ),
    );
}

function wcasl_is_store_locked() {
    $settings = wcasl_get_settings();

    if ( empty( $settings['enabled'] ) || '1' !== $settings['enabled'] ) {
        return false;
    }

    $positions        = wcasl_get_schedule_positions();
    $current_position = wcasl_get_current_position();
    $start_position   = $positions['start'];
    $end_position     = $positions['end'];

    if ( $start_position < $end_position ) {
        return ( $current_position >= $start_position && $current_position < $end_position );
    }

    if ( $start_position > $end_position ) {
        return ( $current_position >= $start_position || $current_position < $end_position );
    }

    return false;
}

function wcasl_get_next_unlock_datetime() {
    if ( ! wcasl_is_store_locked() ) {
        return null;
    }

    $settings         = wcasl_get_settings();
    $positions        = wcasl_get_schedule_positions();
    $current_position = wcasl_get_current_position();
    $start_position   = $positions['start'];
    $end_position     = $positions['end'];

    $now       = new DateTimeImmutable( 'now', wp_timezone() );
    $week_day  = (int) $now->format( 'w' );
    $week_base = $now->modify( '-' . $week_day . ' days' )->setTime( 0, 0, 0 );

    $end_minutes = wcasl_time_to_minutes( $settings['end_time'] );
    $end_hour    = (int) floor( $end_minutes / 60 );
    $end_minute  = $end_minutes % 60;
    $end_day     = wcasl_day_to_int( $settings['end_day'] );

    $unlock_date = $week_base->modify( '+' . $end_day . ' days' )->setTime( $end_hour, $end_minute, 0 );

    if ( $start_position > $end_position && $current_position >= $start_position ) {
        $unlock_date = $unlock_date->modify( '+7 days' );
    }

    return $unlock_date;
}
