<?php
// Grab cron helper
include "cron-helper.php";

// When cron value is updated, something has either been scheduled or unscheduled. Queue next cron in central cron list
add_action("update_option_cron", 'kentblogs_update_cron_schedule');

function kentblogs_update_cron_schedule(){

	$crons = _get_cron_array();
	if(sizeof($crons) !== 0){
		ksort($crons);
		$timestamp = key($crons);
		// Queue blog up in multisite cron list (with latest timestamp)
		multisite_cron_queue(get_current_blog_id(), get_bloginfo('url'), $timestamp);
	}

}

function kentblogs_remove_cron_schedule($id){
	$data = get_site_option('wp-multisite-crons');

	if($data === false){
		return;
	}

	if(isset($data[$id])){
		unset($data[$id]);
		update_site_option('wp-multisite-crons', $data);
	}
}

add_action("archive_blog", "kentblogs_remove_cron_schedule");
add_action("delete_blog", "kentblogs_remove_cron_schedule");
add_action("deactivate_blog", "kentblogs_remove_cron_schedule");
add_action("make_delete_blog", "kentblogs_remove_cron_schedule"); // wat?

function kentblogs_add_cron_schedule($id){
	switch_to_blog($id);
		$crons = _get_cron_array();
		if(sizeof($crons) !== 0){
			ksort($crons);
			$timestamp = key($crons);
			// Queue blog up in multisite cron list (with latest timestamp)
			multisite_cron_queue(get_current_blog_id(), get_bloginfo('url'), $timestamp);
		}
	restore_current_blog();
}

add_action("unarchive_blog", "kentblogs_add_cron_schedule");
add_action("make_undelete_blog", "kentblogs_add_cron_schedule");
add_action("activate_blog", "kentblogs_add_cron_schedule");
add_action("make_undelete_blog", "kentblogs_add_cron_schedule");