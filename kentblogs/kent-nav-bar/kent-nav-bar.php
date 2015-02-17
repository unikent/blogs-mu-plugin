<?php

//register kent-nav-bar script
if( WP_ENV=='local'){
    wp_register_script('kent-nav-bar','http://localhost/kent-nav-bar/dist/kent-header-light.min.js',array(),null,true);
}else{
    wp_register_script('kent-nav-bar','//static.kent.ac.uk/navbar/kent-header-light.min.js',array(),null,true);
}

wp_register_script('kent-nav-bar-twentyfourteen-theme-fix',plugins_url( 'nav-bar-conf.js' , __FILE__ ),array('jquery'),null,true);


function kentblogs_nav_bar(){
    wp_enqueue_script('kent-nav-bar');

    $options=array(
        'navlinks'=>'<li><a role="button" aria-label="All blogs" href="//blogs.kent.ac.uk" class="menu-link">All blogs</a></li>'
    );

    if( WP_ENV=='local'){
        $options['basedir'] = 'http://localhost/kent-nav-bar/dist/';
    }

    $option_keys=array('kb_navbar_bg_color');
    foreach($option_keys as $key){
        $o = get_option($key);
        if(!empty($o)){
            $options[str_replace('kb_navbar_','',$key)] = $o;
        }
    }
    wp_localize_script('kent-nav-bar','_kentbar',$options);
    $theme = wp_get_theme();
    $theme = $theme->get_template();
    error_log(print_r($theme,true));
    if( $theme == 'twentyfourteen'){
        wp_enqueue_script('kent-nav-bar-twentyfourteen-theme-fix');
    }
}
add_action( 'wp_enqueue_scripts', 'kentblogs_nav_bar');

function kentblogs_nav_bar_options_menu() {
    if (function_exists('add_submenu_page') && is_super_admin()) {
        add_submenu_page('options-general.php', 'Kent Nav Bar Options', 'Kent Nav Bar','manage_network', basename(__FILE__), 'kentblogs_nav_bar_options');
    }
}
add_action('admin_menu','kentblogs_nav_bar_options_menu');

function kentblogs_nav_bar_options(){

    if (isset($_POST['update_nav_bar'])) {
        if(isset($_POST['kb_navbar_bg_color'])){
            update_option('kb_navbar_bg_color',$_POST['kb_navbar_bg_color']);
        }
    }
    $kb_navbar_bg_color = get_option('kb_navbar_bg_color');
    ?>
    <div class=wrap>
	<h2>Kent Nav Bar Options</h2>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="kb_navbar_bg_color">Nav Bar Background Colour</label>
                </th>
                <td>
                    <span style="background: #05345c; display:block; width:50px; height:50px; margin-bottom: 5px;"><input type="radio" name="kb_navbar_bg_color" value="#05345c"<?php echo ($kb_navbar_bg_color =='#05345c')?' checked':''; ?>></span>
                    <span style="background: #3f3f38; display:block; width:50px; height:50px; margin-bottom: 5px;"><input type="radio" name="kb_navbar_bg_color" value="#3f3f38"<?php echo ($kb_navbar_bg_color =='#3f3f38')?' checked':''; ?>></span>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="update_nav_bar"></p>
    </form>
    </div>
<?php
}

function kentblogs_nav_bar_admin_scripts(){

}
add_action('admin_enqueue_scripts','kentblogs_nav_bar_admin_scripts');