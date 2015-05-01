<?php
/*
Plugin Name: Report Concern
Plugin URI: http://blogs.kent.ac.uk/webdev/
Description: Report a concern about a blog post.
Version: 1.1
Author: IS Web Development at The University of Kent
Author URI: http://blogs.kent.ac.uk/webdev/
*/

// set site options in the sitemeta table.
// note that tests for get_site_option are needed because the wpmu-specific function add_site_option (incorrectly?) overwrites existing values

// default options
$rc_default_options = array(
	'rc_linktext_post' => 'Report concern',
	'rc_beforelink_post' => '<p align="right">',
	'rc_afterlink_post' => '</p>',
	'rc_window_post' => FALSE,
	'rc_success_post' => '<p>The post has been reported. Thank you.</p>',
	'rc_confirm_header_post' => '
<h2>Report a Concern about a Post</h2>
<p>You have elected to inform us about a concern you have with the post titled:<br /><a href="%POST_URL%">%POST_TITLE%</a></p>
<p>Please fill out the form below, and click the button to send us your concerns about this post.</p>
',
	'rc_confirm_button_post' => 'Report Post',
	'rc_email_post' => 'xyz@domain.com',
	'rc_email_subject_post' => 'Post reported',
	'rc_email_msg_post' => '
A visitor to your site has reported the following post
URL - %POST_URL%
Author id - %POST_AUTHOR%
Date - %POST_DATE%
Post text -
%POST_TEXT%

Visitor feedback
%VISITOR_FEEDBACK_POST%

Visitor details
%VISITOR_DETAILS%
',
	'rc_linktext_comment' => 'Report concern',
	'rc_beforelink_comment' => '<p align="right">',
	'rc_afterlink_comment' => '</p>',
	'rc_window_comment' => FALSE,
	'rc_success_comment' => '<p>The comment has been reported. Thank you.</p>',
	'rc_confirm_header_comment' => '
<h2>Report a Concern about a Comment</h2>
<p>You have elected to inform us about a concern you have with a comment to the post:<br /><a href="%POST_URL%">%POST_TITLE%</a></p>
Comment details<br />
URL: <a href="%COMMENT_URL%">%COMMENT_URL%</a><br />
Comment author: %COMMENT_AUTHOR%<br />
Comment date: %COMMENT_DATE%<br />
Comment text:<br />%COMMENT_TEXT%<br />
<p>Please fill out the form below, and click the button to send us your concerns about this comment.</p>
',
	'rc_confirm_button_comment' => 'Report Comment',
	'rc_email_comment' => 'xyz@domain.com',
	'rc_email_subject_comment' => 'Comment reported',
	'rc_email_msg_comment' => '
A visitor to your site has reported the following comment -
URL - %COMMENT_URL%
Author - %COMMENT_AUTHOR%
Author id - %COMMENT_AUTHOR_ID%
Date - %COMMENT_DATE%
Comment text -
%COMMENT_TEXT%

Post details
Post id - %POST_ID%
Post url - %POST_URL%
Post title - %POST_TITLE%

Visitor feedback
%VISITOR_FEEDBACK_COMMENT%

Visitor details
%VISITOR_DETAILS%
'
);



get_site_option('rc_linktext_post') ? false : add_site_option('rc_linktext_post', $rc_default_options['rc_linktext_post']);
get_site_option('rc_beforelink_post') ? false : add_site_option('rc_beforelink_post', $rc_default_options['rc_beforelink_post']);
get_site_option('rc_afterlink_post') ? false : add_site_option('rc_afterlink_post', $rc_default_options['rc_afterlink_post']);
get_site_option('rc_window_post') ? false : add_site_option('rc_window_post', $rc_default_options['rc_window_post']);
get_site_option('rc_success_post') ? false : add_site_option('rc_success_post', $rc_default_options['rc_success_post']);
get_site_option('rc_confirm_header_post') ? false : add_site_option('rc_confirm_header_post', $rc_default_options['rc_confirm_header_post']);
get_site_option('rc_confirm_button_post') ? false : add_site_option('rc_confirm_button_post', $rc_default_options['rc_confirm_button_post']);
get_site_option('rc_email_post') ? false : add_site_option('rc_email_post', $rc_default_options['rc_email_post']);
get_site_option('rc_email_subject_post') ? false : add_site_option('rc_email_subject_post', $rc_default_options['rc_email_subject_post']);
get_site_option('rc_email_msg_post') ? false : add_site_option('rc_email_msg_post', $rc_default_options['rc_email_msg_post']);

get_site_option('rc_linktext_comment') ? false : add_site_option('rc_linktext_comment', $rc_default_options['rc_linktext_comment']);
get_site_option('rc_beforelink_comment') ? false : add_site_option('rc_beforelink_comment', $rc_default_options['rc_beforelink_comment']);
get_site_option('rc_afterlink_comment') ? false : add_site_option('rc_afterlink_comment', $rc_default_options['rc_afterlink_comment']);
get_site_option('rc_window_comment') ? false : add_site_option('rc_window_comment', $rc_default_options['rc_window_comment']);
get_site_option('rc_success_comment') ? false : add_site_option('rc_success_comment', $rc_default_options['rc_success_comment']);
get_site_option('rc_confirm_header_comment') ? false : add_site_option('rc_confirm_header_comment', $rc_default_options['rc_confirm_header_comment']);
get_site_option('rc_confirm_button_comment') ? false : add_site_option('rc_confirm_button_comment', $rc_default_options['rc_confirm_button_comment']);
get_site_option('rc_email_comment') ? false : add_site_option('rc_email_comment', $rc_default_options['rc_email_comment']);
get_site_option('rc_email_subject_comment') ? false : add_site_option('rc_email_subject_comment', $rc_default_options['rc_email_subject_comment']);
get_site_option('rc_email_msg_comment') ? false : add_site_option('rc_email_msg_comment', $rc_default_options['rc_email_msg_comment']);


/**
 * Adds a sub menu to the Site Admin panel.  If the currently logged in user is
 * a site-admin, then this menu is created using the rc_options_page function.
 * Otherwise, nothing happens.
 *
 * @return null - does not actively return a value
 */
function rc_add_option_pages() {
	if (function_exists('add_submenu_page') && is_super_admin()) {
		// does not use add_options_page, because it is site-wide configuration,
		//  not blog-specific config, but side-wide
		add_submenu_page('settings.php', 'Report Concern Options', 'Report Concern', 9, basename(__FILE__), 'rc_options_page');
	}
}

/**
* updates options in sitemeta table with the ones entered by the user
*/
function rc_options_page() {
	global $rc_default_options;
	// reset to default options
	if (isset($_POST['set_defaults'])) {
		

		echo '<div id="message" class="updated fade"><p><strong>Default Options Loaded!</strong></p></div>';
		
		update_site_option('rc_linktext_post', $rc_default_options['rc_linktext_post']);
		update_site_option('rc_beforelink_post', $rc_default_options['rc_beforelink_post']);
		update_site_option('rc_afterlink_post', $rc_default_options['rc_afterlink_post']);
		update_site_option('rc_window_post', $rc_default_options['rc_window_post']);
		update_site_option('rc_success_post', $rc_default_options['rc_success_post']);
		update_site_option('rc_confirm_header_post', $rc_default_options['rc_confirm_header_post']);
		update_site_option('rc_confirm_button_post', $rc_default_options['rc_confirm_button_post']);
		update_site_option('rc_email_post', $rc_default_options['rc_email_post']);
		update_site_option('rc_email_subject_post', $rc_default_options['rc_email_subject_post']);
		update_site_option('rc_email_msg_post', $rc_default_options['rc_email_msg_post']);

		update_site_option('rc_linktext_comment', $rc_default_options['rc_linktext_comment']);
		update_site_option('rc_beforelink_comment', $rc_default_options['rc_beforelink_comment']);
		update_site_option('rc_afterlink_comment', $rc_default_options['rc_afterlink_comment']);
		update_site_option('rc_window_comment', $rc_default_options['rc_window_comment']);
		update_site_option('rc_success_comment', $rc_default_options['rc_success_comment']);
		update_site_option('rc_confirm_header_comment', $rc_default_options['rc_confirm_header_comment']);
		update_site_option('rc_confirm_button_comment', $rc_default_options['rc_confirm_button_comment']);
		update_site_option('rc_email_comment', $rc_default_options['rc_email_comment']);
		update_site_option('rc_email_subject_comment', $rc_default_options['rc_email_subject_comment']);
		update_site_option('rc_email_msg_comment', $rc_default_options['rc_email_msg_comment']);
		
	}
	// update with user-entered info
	else if (isset($_POST['info_update'])) {

	 // posts
		echo '<div id="message" class="updated fade"><p><strong>';
		update_site_option('rc_linktext_post', stripslashes((string)$_POST["rc_linktext_post"]));
		update_site_option('rc_beforelink_post', stripslashes((string)$_POST["rc_beforelink_post"]));
		update_site_option('rc_afterlink_post', stripslashes((string)$_POST["rc_afterlink_post"]));
		update_site_option('rc_window_post', (bool)$_POST["rc_window_post"]);
		update_site_option('rc_success_post', stripslashes((string)$_POST["rc_success_post"]));
		update_site_option('rc_confirm_header_post', stripslashes((string)$_POST["rc_confirm_header_post"]));
		update_site_option('rc_confirm_button_post', stripslashes((string)$_POST["rc_confirm_button_post"]));
		update_site_option('rc_email_post', stripslashes((string)$_POST["rc_email_post"]));
		update_site_option('rc_email_subject_post', stripslashes((string)$_POST["rc_email_subject_post"]));
		update_site_option('rc_email_msg_post', stripslashes((string)$_POST["rc_email_msg_post"]));
		echo "Configuration Updated!";
	  echo '</strong></p></div>';
	
		// comments
		echo '<div id="message" class="updated fade"><p><strong>';
		update_site_option('rc_linktext_comment', stripslashes((string)$_POST["rc_linktext_comment"]));
		update_site_option('rc_beforelink_comment', stripslashes((string)$_POST["rc_beforelink_comment"]));
		update_site_option('rc_afterlink_comment', stripslashes((string)$_POST["rc_afterlink_comment"]));
		update_site_option('rc_window_comment', (bool)$_POST["rc_window_comment"]);
		update_site_option('rc_success_comment', stripslashes((string)$_POST["rc_success_comment"]));
		update_site_option('rc_confirm_header_comment', stripslashes((string)$_POST["rc_confirm_header_comment"]));
		update_site_option('rc_confirm_button_comment', stripslashes((string)$_POST["rc_confirm_button_comment"]));
		update_site_option('rc_email_comment', stripslashes((string)$_POST["rc_email_comment"]));
		update_site_option('rc_email_subject_comment', stripslashes((string)$_POST["rc_email_subject_comment"]));
		update_site_option('rc_email_msg_comment', stripslashes((string)$_POST["rc_email_msg_comment"]));
		echo "Configuration Updated!";
	  echo '</strong></p></div>';
	}
?>

	<div class=wrap>

	<h2>Report Concern</h2>

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<input type="hidden" name="info_update" id="info_update" value="true" />
		
		<h3>Post Feedback Settings</h3>
		<fieldset class="options"> 
		<legend>Link Display Options</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="6">

		<tr valign="top"><td width="15%" align="right">
			<strong>Link text</strong>
		</td><td align="left">
			<input name="rc_linktext_post" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_linktext_post')) ?>"/>
		</td></tr>

		<tr valign="top"><td width="15%" align="right">
			<strong>Code before link</strong>
		</td><td align="left">
			<input name="rc_beforelink_post" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_beforelink_post')) ?>"/>
		</td></tr>

		<tr valign="top"><td width="15%" align="right">
			<strong>Code after link</strong>
		</td><td align="left">
			<input name="rc_afterlink_post" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_afterlink_post')) ?>"/>
		</td></tr>

		<tr valign="top"><td width="15%" align="right">
			<strong>Open in new window</strong>
		</td><td align="left">
			<input type="checkbox" name="rc_window_post" value="checkbox" <?php if (get_site_option('rc_window_post')) echo "checked='checked'"; ?>/>
		</td></tr>

		</table> 
		</fieldset>


		<fieldset class="options"> 
		<legend>Email Options</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="6">

		<tr valign="top"><td width="15%" align="right">
			<strong>Email address</strong>
		</td><td align="left">
			<input name="rc_email_post" type="text" size="30" value="<?php echo htmlspecialchars(get_site_option('rc_email_post')) ?>"/>
		</td></tr>

		<tr valign="top"><td width="15%" align="right">
			<strong>Email subject</strong>
		</td><td align="left">
			<input name="rc_email_subject_post" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_email_subject_post')) ?>"/>
		</td></tr>

		<tr valign="top"><td width="15%" align="right">
			<strong>Email message</strong>
		</td><td align="left">
			<textarea name="rc_email_msg_post" cols="70" rows="14"><?php echo htmlspecialchars(get_site_option('rc_email_msg_post')) ?></textarea>
		</td></tr>

		<tr valign="top"><td width="15%" align="right">
			<strong>Email Tags</strong>
		</td><td align="left">
			<p>You can use the following tags in the email subject and/or message:</p>
			<ul>
			<li><strong>%POST_ID%</strong> - The ID of the post</li>
			<li><strong>%POST_URL%</strong> - The URL of the post</li>
			<li><strong>%POST_TITLE%</strong> - The title of the post</li>
			<li><strong>%POST_AUTHOR%</strong> - The id of the post author</li>
			<li><strong>%POST_DATE%</strong> - The date of the post</li>
			<li><strong>%VISITOR_FEEDBACK_POST%</strong> - Optional message from the person reporting this comment</li>
			</ul>	
		</td></tr>

		</table> 
		</fieldset>



		<fieldset class="options"> 
		<legend>Confirmation Page Options</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="6">
		
			<tr valign="top"><td width="15%" align="right">
				<strong>Report form header</strong>
			</td><td align="left">
				<textarea name="rc_confirm_header_post" cols="70" rows="14"><?php echo htmlspecialchars(get_site_option('rc_confirm_header_post')) ?></textarea>
			</td></tr>
			
		<tr valign="top"><td width="15%" align="right">
			<strong>Confirmation button</strong>
		</td><td align="left">
			<input name="rc_confirm_button_post" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_confirm_button_post')) ?>"/>
		</td></tr>

		<tr valign="top"><td width="15%" align="right">
			<strong>Success message</strong>
		</td><td align="left">
			<input name="rc_success_post" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_success_post')) ?>"/>
		</td></tr>


		</table> 
		</fieldset>


  <br /><br />
	<h3>Comment Feedback Settings</h3>
	<fieldset class="options"> 
	<legend>Link Display Options</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="6">

	<tr valign="top"><td width="15%" align="right">
		<strong>Link text</strong>
	</td><td align="left">
		<input name="rc_linktext_comment" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_linktext_comment')) ?>"/>
	</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Code before link</strong>
	</td><td align="left">
		<input name="rc_beforelink_comment" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_beforelink_comment')) ?>"/>
	</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Code after link</strong>
	</td><td align="left">
		<input name="rc_afterlink_comment" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_afterlink_comment')) ?>"/>
	</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Open in new window</strong>
	</td><td align="left">
		<input type="checkbox" name="rc_window_comment" value="checkbox" <?php if (get_site_option('rc_window_comment')) echo "checked='checked'"; ?>/>
	</td></tr>

	</table> 
	</fieldset>


	<fieldset class="options"> 
	<legend>Email Options</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="6">

	<tr valign="top"><td width="15%" align="right">
		<strong>Email address</strong>
	</td><td align="left">
		<input name="rc_email_comment" type="text" size="30" value="<?php echo htmlspecialchars(get_site_option('rc_email_comment')) ?>"/>
	</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Email subject</strong>
	</td><td align="left">
		<input name="rc_email_subject_comment" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_email_subject_comment')) ?>"/>
	</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Email message</strong>
	</td><td align="left">
		<textarea name="rc_email_msg_comment" cols="70" rows="14"><?php echo htmlspecialchars(get_site_option('rc_email_msg_comment')) ?></textarea>
	</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Email Tags</strong>
	</td><td align="left">
		<p>You can use the following tags in the email subject and/or message:</p>
		<ul>
		<li><strong>%COMMENT_ID%</strong> - The ID number of the comment</li>
		<li><strong>%COMMENT_URL%</strong> - The URL of the comment</li>
		<li><strong>%POST_ID%</strong> - The ID of the post</li>
		<li><strong>%POST_URL%</strong> - The URL of the post</li>
		<li><strong>%POST_TITLE%</strong> - The title of the post</li>
		<li><strong>%COMMENT_AUTHOR%</strong> - The author of the comment</li>
		<li><strong>%COMMENT_AUTHOR_ID%</strong> - The id of the comment author</li>
		<li><strong>%COMMENT_DATE%</strong> - The date of the comment</li>
		<li><strong>%COMMENT_TEXT%</strong> - The comment text</li>
		<li><strong>%VISITOR_FEEDBACK_COMMENT%</strong> - Optional message from the person reporting this comment</li>
		</ul>	
	</td></tr>

	</table> 
	</fieldset>



	<fieldset class="options"> 
	<legend>Confirmation Page Options</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="6">
	
		<tr valign="top"><td width="15%" align="right">
			<strong>Report form header</strong>
		</td><td align="left">
			<textarea name="rc_confirm_header_comment" cols="70" rows="14"><?php echo htmlspecialchars(get_site_option('rc_confirm_header_comment')) ?></textarea>
		</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Confirmation button</strong>
	</td><td align="left">
		<input name="rc_confirm_button_comment" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_confirm_button_comment')) ?>"/>
	</td></tr>

	<tr valign="top"><td width="15%" align="right">
		<strong>Success message</strong>
	</td><td align="left">
		<input name="rc_success_comment" type="text" size="70" value="<?php echo htmlspecialchars(get_site_option('rc_success_comment')) ?>"/>
	</td></tr>


	</table> 
	</fieldset>

	<div class="submit">
		<input type="submit" name="set_defaults" value="<?php _e('Load Default Options'); ?> &raquo;" />
		<input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
	</div>
	</form>
	</div>
	<?php
}

/**
 * Return the URI of the reporting script.
 * 
 * @return string The URI of the script.
 */
function rc_script_location() {
	return get_option('siteurl') . '/wp-content/mu-plugins/kentblogs/report-concern/report.php';
}

/**
* Filter which modifies the post contents so it has a 'report concern' button in it
*
* @return string filtered post contents
*/
function rc_process_post($content) {
	if ( is_home() )
	{
		return $content;
	}
	$link_before = get_site_option('rc_beforelink_post');
	$link_after = get_site_option('rc_afterlink_post');
	$link_text = get_site_option('rc_linktext_post');
	$new_window = (bool)get_site_option('rc_window_post');
	$t_out = $link_before;
	$link = rc_script_location() . '?p=' . get_the_ID();
	// do nonce stuff on the link
	$link = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($link, 'report-concern-confirm-post_' . get_the_ID()) : $link;
	$t_out .= '<a href="' . $link . '"';
	if ($new_window) {
		$t_out .= ' target="_blank"';
	}
	$t_out .= '>' . $link_text . '</a>' . $link_after;
	return $content . $t_out;
}

/**
* Filter which modifies the comment contents so it has a 'report concern' button in it
*
* @return string filtered comment contents
*/
function rc_process_comment($content) {
	$link_before = get_site_option('rc_beforelink_comment');
	$link_after = get_site_option('rc_afterlink_comment');
	$link_text = get_site_option('rc_linktext_comment');
	$new_window = (bool)get_site_option('rc_window_comment');
	$t_out = $link_before;

	$link = rc_script_location() . '?c=' . get_comment_ID();

	// do nonce stuff on the link
	$link = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($link, 'report-concern-confirm-comment_' . get_comment_ID()) : $link;
	$t_out .= '<a href="' . $link . '"';
	if ($new_window) {
		$t_out .= ' target="_blank"';
	}
	$t_out .= '>' . $link_text . '</a>' . $link_after;
	return $content . $t_out;
}

add_filter('the_content', 'rc_process_post'); // add a filter for posts to put the Report Concern link in
add_filter('comment_text', 'rc_process_comment'); // add a filter for comments to put the Report Concern link in

add_action('network_admin_menu', 'rc_add_option_pages'); // add an action for the admin menu so we can have the plugin options page

?>
