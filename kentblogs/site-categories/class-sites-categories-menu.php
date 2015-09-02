<?php

class KentBlogs_Sites_Categories_Menu
{
    /**
     * Add network admin menu
     */
    public function __construct()
    {
        add_action( 'network_admin_menu', array( $this, 'cat_menu' ) );
    }
    
    /**
     * Assign menu and set scripts
     */
    public function cat_menu()
    {
        $page = add_submenu_page(
            'sites.php',
            'Site Categories', 
            'Site Categories', 
            'add_users', 
            'site-cats', 
            array( $this, 'render_menu' )
        );
        add_action( "admin_print_scripts-$page", array( $this, 'print_script' ) );
    }

    
    /**
     * Display plugin screen
     * 
     * The count starts at 2 because Mature uses 0 and 1
     * if greater than 1, 'mature' it's not added 
     * to the site name in the Sites screen
     */
    public function render_menu()
    {
        if ( 
            isset( $_POST['cat_name'] ) 
            && isset( $_POST['KentBlogs_debug_log'] ) 
            && wp_verify_nonce( $_POST['KentBlogs_debug_log'], plugin_basename( __FILE__ ) ) 
        )
        {
            $current = get_option(KentBlogs_Multisite_Categories::$option_name);
            $cats_arr = array();
            $count = 2;

            $cats = array();
            foreach( $_POST['cat_name'] as $key => $value )
            {
                $cats[$key] = array('new'=>$value, 'old'=>(is_array($current) && array_key_exists($key,$current)?$current[$key]['name']:null));
            }

            usort( $cats, array( $this, 'cmp' ) );
            foreach( $cats as $values )
            {
                if( !empty( $values['new'] ) )
                {
                    $cats_arr[] = array( 'mature' => $count, 'name' => $values['new'] );
                    $count++;
                }
                if($values['new']!==$values['old'] && !empty($values['old'])){
                    KentBlogs_Multisite_Categories::get_instance()->update_category($values['old'],$values['new']);
                }
            }
            update_option( KentBlogs_Multisite_Categories::$option_name, $cats_arr );
            KentBlogs_Multisite_Categories::get_instance()->update_sites( $cats_arr );
        }
        $this->echo_html( get_option( KentBlogs_Multisite_Categories::$option_name ) );
    }

    
    /**
     * Helper function for plugin screen
     * 
     * @param string $repeatable_fields
     */
    private function echo_html( $repeatable_fields )
    {
        ?>
        <div class="wrap">
        <h2>Site Categories</h2>
        <div id="poststuff">

            <form action="" method="post" id="site_cats">
                <?php
                wp_nonce_field( plugin_basename( __FILE__ ), 'KentBlogs_debug_log' );
                ?>

            <table id="repeatable-fieldset-one" width="100%">
            <thead>
                <tr>
                    <th width="2%"></th>
                    <th width="90%"></th>
                    <!--<th width="2%"></th>-->
                </tr>
            </thead>
            <tbody>
            <?php
            $blogs = KentBlogs_Multisite_Categories::get_blog_list();
            
            if ( $repeatable_fields ) :

                foreach ( $repeatable_fields as $field ) {
                    $num_sites = count( $this->search_ocurrences($blogs,'mature', $field['mature'] ) );
                    $num_sites = '<span style="opacity:.5">Sites: </span><b>'. $num_sites . '</b>';
                ?>
            <tr>
                <td><a class="button remove-row" href="#">-</a></td>
                <td><input type="text" class="widefat" name="cat_name[]" value="<?php if($field['name'] != '') echo esc_attr( $field['name'] ); ?>" /></td>
                <td><?php echo $num_sites; ?></td>
           </tr>
                <?php
                }
            else :
                // show a blank one
            ?>
            <tr>
                <td><a class="button remove-row" href="#">-</a></td>
                <td><input type="text" class="widefat" name="cat_name[]" /></td>
                <td>&nbsp;</td>
            </tr>
            <?php endif; ?>

            <!-- empty hidden one for jQuery -->
            <tr class="empty-row screen-reader-text">
                <td><a class="button remove-row" href="#">-</a></td>
                <td><input type="text" class="widefat" name="cat_name[]" /></td>
                <td>&nbsp;</td>
            </tr>
            </tbody>
            </table>

            <p><a id="add-row" class="button" href="#">Add another</a></p>
        <?php submit_button(); ?>
        <hr />
        </form>
        </div>
        </div>	
        <?php
    }

    
    /**
     * Enqueue plugin script
     * 
     */
    public function print_script()
    {
        wp_enqueue_script( 
            'repeat', 
            KentBlogs_Multisite_Categories::get_instance()->plugin_url . 'js/msc-repeatable.js', 
            array( 'jquery-ui-sortable', 'jquery-ui-core', 'jquery')
        );
    }
    
    /**
     * Sort array alphabetically
     * 
     * @param string $a
     * @param string $b
     * @return array
     */
    private function cmp($a, $b)
    {
        return strcasecmp($a['new'], $b['new']);
    }
    
    /**
     * Search for ocurrences of a given $key=>$value
     * 
     * Used to count the number of sites within a category
     * 
     * @param array $array
     * @param string $key
     * @param string $value
     * @return array
     */
    private function search_ocurrences($array, $key, $value)
    {
        $results = array();

        if (is_array($array))
        {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->search_ocurrences($subarray, $key, $value));
        }

        return $results;
    }
}