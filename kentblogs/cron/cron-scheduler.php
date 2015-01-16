<?php
// Grab cron helper
include "cron-helper.php";

// When cron value is updated, something has either been scheduled or unscheduled. Queue next cron in central cron list
add_action("update_option_cron", function(){

	$crons = _get_cron_array();
	
	if(sizeof($crons) !== 0){
		ksort($crons);
		$timestamp = key($crons);
		// Queue blog up in multisite cron list (with latest timestamp)
		multisite_cron_queue(get_current_blog_id(), get_bloginfo('url'), $timestamp);
	}
});
