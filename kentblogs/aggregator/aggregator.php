<?php 

include 'helper.php';

// This is run every time a post is saved/updated
add_action('save_post', 'kentblogs_aggregator_post_saved');