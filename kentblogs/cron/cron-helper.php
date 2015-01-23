<?php

// Queue up a CRON job for this blog
function multisite_cron_queue($blog_id, $path, $timestamp)
{
	$data = get_site_option('wp-multisite-crons');

	if($data === false){
		$data = multisite_cron_init();
	} 

	$data[$blog_id] = array(
		"blogid" => $blog_id,
		"path" => $path,
		"timestamp" => $timestamp,
		"created" => time()
	);
	
	update_site_option('wp-multisite-crons', $data);
}

// Get next X blogs that need crons running on them
function multisite_cron_get_next($amount = 5)
{
	$data = get_site_option('wp-multisite-crons');

	if($data === false){
		$data = multisite_cron_init();
	}

	usort($data, function($a, $b){
		if($a['timestamp'] == $b['timestamp']) return 0;
		return ($a['timestamp'] > $b['timestamp']) ? 1 : -1;
	});

	$first = reset($data);
	if($first['timestamp'] > time()){
		// if nearest cron is in future (rather than now), there are no crons that need running
		return array();
	}

	return array_slice($data, 0, 5, true);
}

// Init blog crons
function multisite_cron_init(){

	$data = array();
	$blogs = wp_get_sites(array('limit'=> 1000));

	foreach($blogs as $blog){
		switch_to_blog($blog['blog_id']);
		$crons = _get_cron_array();
		$timestamp = 0;
		if(sizeof($crons) !== 0) {
			$timestamp = key($crons);
		}
		// Newly inited blogs populate with timestamp 0 so they are all at top of pile
		$data[$blog['blog_id']] = array(
			"blogid" => $blog['blog_id'],
			"path" => 'http://'.$blog['domain'].$blog['path'],
			"timestamp" => $timestamp,
			"created" => time()
		);
		restore_current_blog();
	}

	// create value
	update_site_option('wp-multisite-crons', $data);

	return $data;
}