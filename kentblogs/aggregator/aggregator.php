<?php 

include 'helper.php';

// This is run every time a post is saved/updated
add_action('save_post', 'kentblogs_aggregator_post_saved');
// This responds to posts being trashed but not deleted (default interface behaviour)
add_action('trashed_post', 'kentblogs_aggregator_post_removed');
// This is required by the expires module (it seems to jump straight to deletion)
add_action('delete_post', 'kentblogs_aggregator_post_removed');