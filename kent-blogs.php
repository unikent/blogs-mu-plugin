<?php
include_once('kentblogs/vendor/MM_MediaAccess.php');
include_once('kentblogs/cron/cron-scheduler.php');
include_once('kentblogs/kent-nav-bar/kent-nav-bar.php');
include_once('kentblogs/login-form/login-form.php');
include_once('kentblogs/analytics/analytics.php');
include_once('kentblogs/subheadings/subheadings.php');
include_once('kentblogs/blogs-footer/blogs-footer.php');
include_once('kentblogs/social/social-share.php');
include_once('kentblogs/responsive-videos/responsive-videos.php');
include_once('kentblogs/aggregator/aggregator.php');
include_once('kentblogs/popular/popular.php');

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


add_action('network_admin_menu', 'kentblogs_add_manage_globals_page');

function kentblogs_add_manage_globals_page(){
    if (function_exists('add_submenu_page') && is_super_admin()) {
        // does not use add_options_page, because it is site-wide configuration,
        //  not blog-specific config, but side-wide
        add_submenu_page('settings.php', 'Manage Kent Blogs Multisite Globals', 'Multisite Globals','manage_network','manage_kentblogs_globals','kentblogs_manage_globals');
    }
}

function kentblogs_manage_globals(){
    $actioned=false;
    if (isset($_POST['submit'])) {

        if(isset($_POST['rebuild_cron']) && $_POST['rebuild_cron']=='true'){
            multisite_cron_init();
            $actioned=true;
        }
        if(isset($_POST['rebuild_aggregate']) && $_POST['rebuild_aggregate']=='true'){
            kentblogs_aggregator_init_posts();
            $actioned=true;
        }
        if($actioned){
            echo '<div id="message" class="updated fade"><p><strong>Global(s) Rebuilt</strong></p></div>';
        }

    }

    ?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="rebuild_cron">Rebuild Multisite Cron Queue</label></th>
                    <td>
                        <input type="checkbox" id="rebuild_cron" name="rebuild_cron" value="true">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="rebuild_aggregate">Rebuild Multisite Aggregated Content</label></th>
                    <td>
                        <input type="checkbox" id="rebuild_aggregate" name="rebuild_aggregate" value="true">
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" value="Perform Actions" class="button button-primary" id="submit" name="submit"></p>
    </form>
<?php
}

// Base path fixer.
// Without this wp_upload_dir returns none writable path.  
add_filter('upload_dir', function($opt){
    $opt['basedir'] = str_replace('/web/wp/wp-content/blogs.dir/', '/web/app/blogs.dir/', $opt['basedir']);
    $opt['path'] = str_replace('/web/wp/wp-content/blogs.dir/', '/web/app/blogs.dir/', $opt['path']);
    return $opt;
});


function kentblogs_blog_options_menu() {
    if (function_exists('add_submenu_page') && is_admin()) {
        add_submenu_page('options-general.php', 'Kent Blog Options', 'Kent Blog Options','manage_options', basename(__FILE__), 'kentblogs_blog_options');
    }
}
add_action('admin_menu','kentblogs_blog_options_menu');

function kentblogs_blog_options(){
?>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<?php
    do_action('kentblogs_blog_options_page');
?>
<p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="update_kentblog_options"></p>
</form>
<?php
}

//force ie edge mode
header('X-UA-Compatible: IE=edge');