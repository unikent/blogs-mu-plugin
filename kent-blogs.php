<?php

include_once('kentblogs/cron/cron-scheduler.php');
include_once('kentblogs/kent-nav-bar/kent-nav-bar.php');
include_once('kentblogs/login-form/login-form.php');
include_once('kentblogs/analytics/analytics.php');
include_once('kentblogs/report-concern/report-concern.php');
include_once('kentblogs/subheadings/subheadings.php');
include_once('kentblogs/blogs-footer/blogs-footer.php');
include_once('kentblogs/social/social-share.php');
include_once('kentblogs/responsive-videos/responsive-videos.php');

/*
 * Disable Trackbacks/Pingbacks
 */
add_filter( 'xmlrpc_methods', 'remove_xmlrpc_pingback_ping' );
function remove_xmlrpc_pingback_ping( $methods ) {

    if ( isset( $methods['pingback.ping'] ) ) {
        unset( $methods['pingback.ping'] );
    }

    if ( isset( $methods['pingback.extensions.getPingbacks'] ) ) {
        unset( $methods['pingback.extensions.getPingbacks'] );
    }

    return $methods;

} ;

/*
 * Remove unwanted shortcode markup from content for removed plugin
 */
add_shortcode( 'showhide', 'kentblogs_remove_shortcode' );
function kentblogs_remove_shortcode( $atts, $content = "" ){
    return $content;
}