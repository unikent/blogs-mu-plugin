<?php

include_once('kentblogs/cron/cron-scheduler.php');
include_once('kentblogs/kent-nav-bar/kent-nav-bar.php');
include_once('kentblogs/login-form/login-form.php');
include_once('kentblogs/analytics/analytics.php');
include_once('kentblogs/report-concern/report-concern.php');
include_once('kentblogs/subheadings/subheadings.php');
include_once('kentblogs/blogs-footer/blogs-footer.php');
include_once('kentblogs/aggregator/aggregator.php');


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


function redirect_add_users() {
    global $pagenow;

    if ( 'user-new.php' === $pagenow ) {
        if ( function_exists('admin_url') ) {
            wp_redirect( admin_url('users.php?page=wpmu_ldap_adduser.functions.php') );
        } else {
            wp_redirect( get_option('siteurl') . '/wp-admin/' . 'users.php?page=wpmu_ldap_adduser.functions.php' );
        }
    }
}
if ( is_admin() ) {
    add_action('admin_menu', 'redirect_add_users');
}

add_filter('show_network_site_users_add_new_form','__return_false');
add_filter('show_network_site_users_add_existing_form','__return_false');
