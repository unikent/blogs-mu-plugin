<?php 

function kentblogs_aggregator_post_saved($id){

	// if there is no previous cache, generate it

	$aggregate_data = get_site_option('wp-multisite-post-aggregate');

	if($aggregate_data === false){
		$aggregate_data = kentblogs_aggregator_init_posts();
	}

	$blog_id = get_current_blog_id();
	$post = get_post($id);

	//check blog is active
	if(get_blog_status($blog_id,'public')==true && get_blog_status($blog_id,'archived')==false && get_blog_status($blog_id,'deleted')==false) {

		// add this post if it meets the criteria
		$post = kentblogs_aggregator_format_post($post, $blog_id);

		if (empty($post)) {
			if (isset($aggregate_data[$blog_id . '_' . $id])) {
				unset($aggregate_data[$blog_id . '_' . $id]);
				update_site_option('wp-multisite-post-aggregate', $aggregate_data);
			}
			return true;
		}

		kentblogs_aggregator_insert_post($post, $blog_id);
	}

	return true;
}

/**
 * Generate initial list of posts.
 * Grabs up to 5 of the most recent posts from each blog, within the last 2 months.
 */
function kentblogs_aggregator_init_posts(){

	// remove report concern filter
	remove_filter( 'the_content', 'rc_process_post' );

	$aggregate_data = array();
	$blogs = wp_get_sites(array('public'=>true,'archived'=>false,'deleted'=>false,'spam'=>false,'limit'=> 1000));

	foreach($blogs as $blog) {
		// get last month's posts
		switch_to_blog($blog['blog_id']);

		$posts = get_posts(
			array (
				'date_query' => array(
					'after' => '-2 months'
				),
				'posts_per_page' => 5
			)
		);

		if (!empty($posts)) {
			foreach ($posts as $post) {
				$post = kentblogs_aggregator_format_post($post, $blog['blog_id']);
				if(!empty($post)) $aggregate_data[$blog['blog_id'].'_'.$post['id']] = $post;
			}
		}

		restore_current_blog();
	}

	$aggregate_data = kentblogs_aggregator_sort_and_trim_posts($aggregate_data);

	// create value
	update_site_option('wp-multisite-post-aggregate', $aggregate_data);

	// re-add report concern filter
	add_filter( 'the_content', 'rc_process_post' );

	return $aggregate_data;
}

function kentblogs_aggregator_format_post($post, $blog){
	if(is_numeric($post)) $post = get_post($post);
	if(is_numeric($blog)) $blog = get_blog_details($blog);

	if(empty($post) || empty($blog)) return false;


	// set thermal defaults
	if ( !defined( 'Voce\Thermal\v1\MAX_POSTS_PER_PAGE' ) ) {
		define( 'Voce\Thermal\v1\MAX_POSTS_PER_PAGE', 100 );
	}

	Voce\Thermal\v1\Controllers\Posts::format($post);

	if($post->status !== 'publish' || $post->type !== 'post') return false;
	$featured_image = isset($post->featured_image) && !empty($post->featured_image) ? $post->featured_image : isset($post->media[0]) ? $post->media[0] : array();
	
	if (empty($featured_image)) return false;

	
	return array(
		'id' => $post->id,
		'title' => $post->title,
		'slug' => $post->slug,
		'date' => strtotime($post->date),
		'excerpt' => $post->excerpt,
		'permalink' => $post->permalink,
		'author' => $post->author,
		'featured_image' => $featured_image,
		'blog_id' => $blog->blog_id,
		'blog_name' => get_bloginfo('name'),
		'blog_path' => $blog->path
	);
}

function kentblogs_aggregator_insert_post($post, $blog){

	if(is_numeric($post)) $post = get_post($post);
	if(is_numeric($blog)) $blog = get_blog_details($blog);

	if(empty($post) || empty($blog)) return false;

	$aggregate_data = get_site_option('wp-multisite-post-aggregate');

	if (isset($aggregate_data[$blog->blog_id.'_'.$post['id']])) {
		$aggregate_data[$blog->blog_id.'_'.$post['id']] = $post;
	}
	else {
		// insert into the right place
		$aggregate_data = array($blog->blog_id.'_'.$post['id'] => $post) + $aggregate_data;
		$aggregate_data = kentblogs_aggregator_sort_and_trim_posts($aggregate_data);
	}

	update_site_option('wp-multisite-post-aggregate', $aggregate_data);
}

function kentblogs_aggregator_sort_and_trim_posts($posts){
	uasort($posts, function($a, $b){
		if($a['date'] == $b['date']) return 0;
		return ($a['date'] < $b['date']) ? 1 : -1;
	});

	return array_slice($posts, 0, 60);
}



function kentblogs_remove_from_aggregator($id){

	$aggregate_data = get_site_option('wp-multisite-post-aggregate');

	if($aggregate_data === false) {
		return;
	}

	foreach(array_keys($aggregate_data) as $key ){
		$keyparts = explode('_',$key);

		if($keyparts[0]==$id){
			unset($aggregate_data[$key]);
		}
	}
	update_site_option('wp-multisite-post-aggregate', $aggregate_data);
}

add_action("archive_blog", "kentblogs_remove_from_aggregator");
add_action("delete_blog", "kentblogs_remove_from_aggregator");
add_action("deactivate_blog", "kentblogs_remove_from_aggregator");