<?php 

/**
 * Get most popular blog posts
 */
function kentblogs_popular_get_posts() {
	$posts = array();

	//TODO try getting from cache first

	$analytics = kentblogs_popular_get_analytics();

	// get top ids from analytics
	$limit = 3;
	$current_blog_id = get_current_blog_id();
	foreach ($analytics['rows'] as $row) {
		$post_url = $row[1];
		if(isset($post_url) && preg_match('/blogs.kent.ac.uk\/([a-z0-9-]+)\/(.+)/', $post_url, $matches)){
			
			$blog_slug = $matches[1];
			$blog_id = get_id_from_blogname($blog_slug);
			switch_to_blog($blog_id);
			$post_url = $matches[2];
			$post_id = url_to_postid($post_url);

			if ($post_id === 0 || isset($posts[$post_id])) {
				restore_current_blog();
				continue;
			}

			$post = get_post($post_id);
			$post = kentblogs_aggregator_format_post($post, $blog_id);

			if (empty($post)) {
				restore_current_blog();
				continue;
			}

			$posts[$post_id] = $post;
			$limit -= 1;
			
			if ($limit === 0) {
				restore_current_blog();
				break;
			}
			restore_current_blog();
		}
	}
	
	return !empty($posts) ? $posts : false;
}


/**
 * Get analytics on news stories
 */
function kentblogs_popular_get_analytics() {
	$results = array();

	$client = new Google_Client();
	$client->setApplicationName("Kent Blogs Analytics");

	// Configure CURL proxying
	$client->getIo()->setOptions(array(
		CURLOPT_PROXY => 'advocate.kent.ac.uk',
		CURLOPT_PROXYPORT => 3128
	));

	$service = new Google_Service_Analytics($client);

	$ga_data = '';

	if (defined('GOOGLE_ANALYTICS_KEY_FILE_LOCATION') && defined('GOOGLE_ANALYTICS_SERVICE_ACCOUNT_EMAIL')) {
		
		// try authenticating and getting analytics
		try {
			$key = file_get_contents(GOOGLE_ANALYTICS_KEY_FILE_LOCATION);
			$cred = new Google_Auth_AssertionCredentials(
				GOOGLE_ANALYTICS_SERVICE_ACCOUNT_EMAIL,
				array(Google_Service_Analytics::ANALYTICS),
				$key
			);
			$client->setAssertionCredentials($cred);
			if($client->getAuth()->isAccessTokenExpired()) {
				$client->getAuth()->refreshTokenWithAssertion($cred);
			}

			$ga_data = $service->data_ga->get(
					'ga:90342423', // view/profile id
					date('Y-m-d', strtotime('-1 day')), // start date
					date('Y-m-d'), // end date
					'ga:uniquePageviews', // metrics
					array(
							'dimensions' => 'ga:pageTitle,ga:pagePath',
							'sort' => '-ga:uniquePageviews',
							'filters' => "ga:pagePath=~^blogs.kent.ac.uk/[a-z0-9-]+/.+",
							'max-results' => '30'
						) // additional parameters
				);
		} catch (Exception $e) {
			// do nothing
		}
	}
	
	return !empty($ga_data) ? $ga_data : false;
	
	return $results;
}