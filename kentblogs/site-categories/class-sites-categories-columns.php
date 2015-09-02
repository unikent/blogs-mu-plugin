<?php

class KentBlogs_Sites_Categories_Columns
{
    public function __construct()
    {
        add_action(
                'manage_blogs_custom_column', array( $this, 'add_columns' ), 10, 2
        );
        add_action(
                'manage_sites_custom_column', array( $this, 'add_columns' ), 10, 2
        );
        add_filter(
                'wpmu_blogs_columns', array( $this, 'print_columns' )
        );
        add_action(
                'admin_head-sites.php', array( $this, 'add_style' )
        );
        add_filter( "manage_sites-network_sortable_columns", array( $this, 'sortable' ) );
        # SORT BY MATURE
        global $pagenow;
        if( 
            is_super_admin() 
            && 'sites.php' == $pagenow
            && isset( $_GET['orderby'] ) && 'site-category' == $_GET['orderby'] 
        ) 
            add_filter( 'query', array( $this, 'filter_site_query' ) );

    }

    /**
     * Add custom columns (ID and Site Category) in Sites listing
     *
     */
    public function add_columns( $column_name, $blog_id )
    {
        if( 'mature' === $column_name )
        {
            if( apply_filters( 
                'msc_show_mature_column', 
                KentBlogs_Multisite_Categories::$show_mature_column )
            )
            {
                echo get_blog_status( $blog_id,'mature' );
            }
        }
        elseif( 'column-site-cat' === $column_name )
        {
            $cat_name = get_blog_option( $blog_id, 'site_category' );
            $uncategorized = '<span style="opacity:.5">Uncategorized</span>';
            echo ( empty( $cat_name ) ) ? $uncategorized : $cat_name;
        }        
        return $column_name;
    }


    /**
     * Add Columns
     *
     */
    public function print_columns( $cols )
    {
        if( apply_filters( 
            'msc_show_mature_column', 
            KentBlogs_Multisite_Categories::$show_mature_column )
        )
            $cols['mature'] = __( 'Mature' );
        $cols['column-site-cat'] = __( 'Category' );
        return $cols;
    }


    /**
     * Mark categories as sortable
     * 
     * @param array $columns
     * @return array
     */
    public function sortable( $columns )
    {
        $columns['column-site-cat'] = 'site-category';
        return $columns;

    }

    
    /**
     * Add column widths
     *
     */
    public function add_style()
    {
        echo '<style>#mature { width:7%; } #column-site-cat { width:10%; }</style>';
    }

    
    /**
     * Order sites by mature column
     * 
     * @global object $wpdb
     * @param string $query
     * @return strin
     */
    public function filter_site_query( $query )
    {

        global $wpdb;
        $search_query = "SELECT * FROM {$wpdb->blogs} WHERE site_id = '1'";
        
        # SANITIZE
        if( isset( $_GET['order'] ) )
        {
            $order = ( 'asc' == $_GET['order'] ) ? 'ASC' : 'DESC';
        }
        else
            $order = 'DESC';

        # MODIFY
        if( strpos( $query, $search_query ) !== FALSE )
        {
            $query = str_replace("site_id = '1'","site_id = '1' ORDER BY mature " . $order,$query);
        }
        return $query;
    }

    
}