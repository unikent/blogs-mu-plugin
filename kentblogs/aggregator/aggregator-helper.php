<?php 

function kentblogs_aggregator_post_saved($id){

	// if there is no previous cache, generate it

	$data = get_site_option('wp-multisite-post-aggregate');

	if($data === false){
		$data = kentblogs_aggregator_init_posts();
	}

	// add this post if it meets the criteria
	
}

function kentblogs_aggregator_post_trashed($id){
	// remove this post if in list

}

function kentblogs_aggregator_post_deleted($id){
	// remove this post if in list

}

/**
 * Generate initial list of posts.
 * Grabs up to 5 of the most recent posts from each blog, within the last 2 months.
 */
function kentblogs_aggregator_init_posts(){

	// set thermal defaults
	if ( !defined( 'Voce\Thermal\v1\MAX_POSTS_PER_PAGE' ) ) {
		define( 'Voce\Thermal\v1\MAX_POSTS_PER_PAGE', 100 );
	}

	// remove report concern filter
	remove_filter( 'the_content', 'rc_process_post' );


	$data = array();
	$blogs = wp_get_sites(array('limit'=> 1000));

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
				Voce\Thermal\v1\Controllers\Posts::format($post);
				$featured_image = isset($post->featured_image) && !empty($post->featured_image) ? $post->featured_image : isset($post->media[0]) ? $post->media[0] : array();
				
				if (empty($featured_image)) continue;
				
				$data[] = array(
					'id' => $post->id,
					'title' => $post->title,
					'name' => $post->name,
					'date' => strtotime($post->date),
					'excerpt' => $post->excerpt_display,
					'permalink' => $page->permalink,
					'author' => $page->author,
					'featured_image' => $featured_image,
					'blog_id' => $blog['blog_id'],
					'blog_name' => get_bloginfo('name')
				);
			}
		}

		restore_current_blog();
	}

	usort($data, function($a, $b){
		if($a['date'] == $b['date']) return 0;
		return ($a['date'] < $b['date']) ? 1 : -1;
	});

	$data = array_slice($data, 0, 60);

	// create value
	update_site_option('wp-multisite-post-aggregate', $data);

	// re-add report concern filter
	add_filter( 'the_content', 'rc_process_post' );

	return $data;
}