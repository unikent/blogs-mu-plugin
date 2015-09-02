<?php

add_action(
	'plugins_loaded', array( KentBlogs_Multisite_Categories::get_instance(), 'init' )
);

class KentBlogs_Multisite_Categories
{
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;
	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';
	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';


	/**
	 * Option name for the Categories List
	 *
	 * @var object
	 */
	public static $option_name = 'sites_categories_list';


	/**
	 * Holds the List of Categories and its IDs (mature)
	 *
	 * @var object
	 */
	public $options;

	/**
	 * Debug only, show the mature column
	 *
	 * Use add_filter( 'msc_show_mature_column', '__return_true' );
	 *
	 * @var bool
	 */
	public static $show_mature_column = false;


	/**
	 * Cache list of sites with id + mature
	 *
	 * add_filter( 'msc_transient_time', function(){ return 1; } );
	 *
	 * @var bool
	 */
	public static $sites_transient = 3600; // 1 hour


	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.13
	 * @return  object of this class
	 */
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Initialize.
	 */
	public function init()
	{
		global $pagenow;
		$this->plugin_url = plugins_url('/', __FILE__);
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->options = get_option(self::$option_name);

# WP, ALL MATURES ARE OK
		if ('sites.php' != $pagenow)
			add_filter('blog_details', array($this, 'hack_mature_queries'));
# BAIL OUT
		if (!is_network_admin())
			return;

# NETWORK MENU
		require_once 'class-sites-categories-menu.php';
		new KentBlogs_Sites_Categories_Menu();

# COLUMNS
		require_once 'class-sites-categories-columns.php';
		new KentBlogs_Sites_Categories_Columns();

# MANIPULATE FIELDS IN SITE-INFO
		add_action('admin_init', array($this, 'site_info_post_data'));
		add_action('admin_footer', array($this, 'site_info_scripts'));

	}

	/**
	 * Constructor. Intentionally left empty and public.
	 */
	public function __construct()
	{
	}

	/**
	 * Tell WP all Matures are equal to 0
	 * Except in the screen sites.php
	 *
	 * @param object $details
	 * @return object
	 */
	public function hack_mature_queries($details)
	{
		$details->mature = 0;
		return $details;
	}

	/**
	 * Change site category in Site Info
	 *
	 * @return void
	 */
	public function site_info_post_data()
	{
		if (
			!isset($_POST['nonce_kb_msc'])
			|| !wp_verify_nonce($_POST['nonce_kb_msc'], plugin_basename(__FILE__))
		)
			return;
		if (isset($_POST['input_site_cat'])) {
			$val = $this->do_mature_to_name($_POST['input_site_cat']);
			update_blog_option($_POST['id'], 'site_category', $val);
			update_blog_status(absint($_POST['id']), 'mature', $_POST['input_site_cat']);
			$this->get_blog_list(true);
		}
	}


	/**
	 * Manipulate fields on site-info.php
	 *
	 * @return string
	 */
	public function site_info_scripts()
	{
		if ('site-info-network' != get_current_screen()->id || !isset($_GET['id']))
			return;
		$nonce = wp_nonce_field(plugin_basename(__FILE__), 'nonce_kb_msc', true, false);
		$dropdown = $this->get_dropdown($_GET['id'], $nonce);
		$dropdown = '<tr><th scope="row">Category</th><td>' . $dropdown . $nonce . '</td></tr>';
		echo <<<HTML
<script type="text/javascript">
	jQuery(document).ready( function($) {
		$(".form-table").find("label:contains('Mature')").remove();
		$('$dropdown').appendTo('.form-table')
	});
</script>
HTML;
	}

	/**
	 * Generate HTML for categories dropdown
	 *
	 * @param type $nonce
	 */
	public function get_dropdown($site_id)
	{
		$all_cats = get_option(self::$option_name);
		$dropdown = '<select name="input_site_cat" id="input_site_cat">';
		$site_cat = !empty($site_id) ? get_blog_option($site_id, 'site_category') : false;
		$empty_cat = '';
		if ($site_cat)
			$site_cat = $this->do_name_to_mature($site_cat);
		else
			$empty_cat = 'selected="selected"';

		$dropdown .= '<option value="empty" ' . $empty_cat . '>--select--</option>';
		foreach ($all_cats as $cat) {
			$sel = $cat['mature'] == $site_cat ? 'selected="selected"' : '';
			$dropdown .= sprintf(
				'<option value="%s" %s>%s</option>',
				$cat['mature'],
				$sel,//selected( $count, $site_cat, false ),
				$cat['name']
			);
		}
		$dropdown .= '</select>';
		return $dropdown;
	}


	/**
	 * Categories settings changed, updated sites
	 *
	 * @param array $cats_arr
	 */
	public function update_sites($cats_arr)
	{
		$this->options = $cats_arr;
		$blogs = self::get_blog_list();
		foreach ($blogs as $blog) {
			$id = $blog['blog_id'];
			$opt = get_blog_option($id, 'site_category');
			$mature = $this->do_name_to_mature($opt);
			if (!$mature)
				update_blog_option($id, 'site_category', '');
			update_blog_status(absint($id), 'mature', $mature);
		}
	}

	public function update_category($old,$new)
	{
		$blogs = self::get_blog_list();
		foreach ($blogs as $blog) {
			$id = $blog['blog_id'];
			$opt = get_blog_option($id, 'site_category');
			if($opt == $old){
				update_blog_option($id, 'site_category', $new);
			}

		}
	}

	/**
	 * Get blog list
	 *
	 * @return array All blogs IDs
	 */
	public static function get_blog_list($clear=false)
	{
		$blogs = get_site_transient('multisite_blog_list');
		if (FALSE === $blogs || $clear) {
			$time = apply_filters('msc_transient_time', self::$sites_transient);
			global $wpdb;
			$limit = '';

			$blogs = $wpdb->get_results(
				$wpdb->prepare("
SELECT blog_id, mature
FROM $wpdb->blogs
WHERE site_id = %d
$limit
", $wpdb->siteid),
				ARRAY_A);
			set_site_transient('multisite_blog_list', $blogs, $time);
		}
		return $blogs;
	}

	/**
	 * Gets category name base on id (mature)
	 *
	 * @param int $mature
	 * @return string
	 */
	public function do_mature_to_name($mature)
	{
		foreach ($this->options as $opt) {
			if ($mature == $opt['mature'])
				return $opt['name'];
		}
		return '';
	}


	/**
	 * Get id (mature) based on category name
	 *
	 * @param string $category
	 * @return int
	 */
	public function do_name_to_mature($category)
	{
		foreach ($this->options as $opt) {
			if ($category == $opt['name'])
				return $opt['mature'];
		}
		return '';
	}

}