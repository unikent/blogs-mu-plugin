<?php
// Grab cron helper
include "cron-helper.php";

// When cron value is updated, something has either been scheduled or unscheduled. Queue next cron in central cron list
add_action("update_option_cron", 'kentblogs_update_cron_schedule');

function kentblogs_update_cron_schedule(){

	$crons = _get_cron_array();
	if(sizeof($crons) !== 0){
		//ksort($crons);
		$timestamp = key($crons);
		// Queue blog up in multisite cron list (with latest timestamp)
		//error_log(get_current_blog_id() . ' : ' .  get_bloginfo('url') . ' : ' . $timestamp);
		multisite_cron_queue(get_current_blog_id(), get_bloginfo('url'), $timestamp);
	}

}