<?php
/**
 * Creates form used when the user reports a concern.
 */

// This is a standalone script not loading the WordPress core automatically.
// Bootstrap WordPress.
require_once('../../../../wp/wp-load.php');

showForm();

function showForm() {
	
	// some checks on query strings
	// p for posts, and c for comments. id is a unique key to help stop spam and CSRF

	if (isset($_REQUEST['p'])) {
		// check to make sure that if the user's just submitted some feedback, post id and key are POSTs (makes things slightly trickier for CSRF)
		// we don't want to do anything else if not, so this comes first
		if (isset($_POST['submit']) && !isset($_POST['p'])) {
			exit();
		}
		
		// post id must be a number
		$post_id = (int)$_REQUEST['p'];

		if (!is_numeric($post_id)) {
			exit();
		}

		$post = get_post($post_id);

		// must be only one row
		if (empty($post)) {
			exit();
		}
		
		// this part gets executed when the report form has been submitted
		if (isset($_POST['submit'])) {
			
			// check the nonce from the user feedback form
			if (function_exists('wp_nonce_field') && !check_admin_referer('report-concern-confirm-post_'.$post_id)){
				die("Nonce incorrect.");
			}
			
			// used for outputting error messages if visitor hasn't given any feedback etc
			$error = '';
			// start building up the message
			$msg = get_site_option('rc_email_msg_post');
			
			// visitor feedback, email address, etc is added to the message
			$msg = visitor_feedback($msg, $email, $error, 'post');

			// have an error so output the feedback form again
			if ($error != '') {
				// output the feedback form as though the visitor hadn't submitted anything, only with error info in it
				feedback_form_post($post, $error);
			}
			
			// process form and send a message
			else {
				// get various bits and pieces ready to put in the message
				$addr = get_site_option('rc_email_post');
				$subject = get_site_option('rc_email_subject_post');
				$post_url = get_permalink($post->ID);
				$post_title = get_the_title($post->ID);
			
				// depending on admin preferences, subject line of email can include: id, url, title, author, date
				$subject = str_replace('%POST_ID%', $post->ID, $subject);
				$subject = str_replace('%POST_URL%', $post_url, $subject);
				$subject = str_replace('%POST_TITLE%', $post_title, $subject);
				$subject = str_replace('%POST_AUTHOR%', $post->post_author, $subject);
				$subject = str_replace('%POST_DATE%', $post->post_date, $subject);
			
				// as above for the body of the email, only with the option to include the entire post text
				$msg = str_replace('%POST_ID%', $post->ID, $msg);
				$msg = str_replace('%POST_URL%', $post_url, $msg);
				$msg = str_replace('%POST_TITLE%', $post_title, $msg);
				$msg = str_replace('%POST_AUTHOR%', $post->post_author, $msg);
				$msg = str_replace('%POST_DATE%', $post->post_date, $msg);
				
				// the message sent to the user doesn't include the contents of the original post
				$msg_user = $msg;
				$msg_user = str_replace('%POST_TEXT%', '', $msg_user);
				
				$msg = str_replace('%POST_TEXT%', $post->post_content, $msg);
			
				// send out the email
				if ($addr != '' && $email != '') {
					$ticket = build_ticket($email, $subject, $msg, $msg_user);
					//echo $ticket;
					wp_mail($addr, $subject, $ticket, 'From: blogs.kent admin <' . get_site_option('admin_email') . '>');
				}
	
				// print out the success message
				$content = get_site_option('rc_success_post');
				build_template($content);
			}
		}
		// show the feedback form by default
		else {
			feedback_form_post($post);
		}
	}
	
	elseif (isset($_REQUEST['c'])) {
		
		// check to make sure that if the user's just submitted some feedback, comment id and key are POSTs (makes things slightly trickier for CSRF)
		// we don't want to do anything else if not, so this comes first
		if (isset($_POST['submit']) && !isset($_POST['c'])) {
			exit();
		}
		
		// comment's id must be a number
		$comment_id = (int)$_REQUEST['c'];
		if (!is_numeric($comment_id)) {
			exit();
		}
		
		$comment = get_comment($comment_id);

		if( empty($comment)) {
			exit();
		}
		
		// this part gets executed when the report form has been submitted
		if (isset($_POST['submit'])) {
			
			// check the nonce from the user feedback form
			if (function_exists('wp_nonce_field') && !check_admin_referer('report-concern-confirm-comment_'.$comment_id))
				exit;
				
			// used for outputting error messages if visitor hasn't given any feedback etc
			$error = '';
			
			// start building up the message
			$msg = get_site_option('rc_email_msg_comment');
			
			// visitor feedback, email address, etc is added to the message
			$msg = visitor_feedback($msg, $email, $error, 'comment');
			
			// have an error so output the feedback form again
			if ($error != '') {
				// output the feedback form as though the visitor hadn't submitted anything, only with error info in it
				feedback_form_comment($comment, $error);
			}
			// process form and send a message
			else {
				// get various bits and pieces ready to put in the message
				$addr = get_site_option('rc_email_comment');
				$subject = get_site_option('rc_email_subject_comment');
				$post_url = get_permalink($comment->comment_post_ID);
				$post_title = get_the_title($comment->comment_post_ID);
			
				// depending on admin preferences, subject line of email can include: comment id, comment url, post id, post url, post title, comment author, comment date
				$subject = str_replace('%COMMENT_ID%', $comment->comment_ID, $subject);
				$subject = str_replace('%COMMENT_URL%', $post_url . '#comment-' . $comment->comment_ID, $subject);
				$subject = str_replace('%POST_ID%', $comment->comment_post_ID, $subject);
				$subject = str_replace('%POST_URL%', $post_url, $subject);
				$subject = str_replace('%POST_TITLE%', $post_title, $subject);
				$subject = str_replace('%COMMENT_AUTHOR%', $comment->comment_author, $subject);
				$subject = str_replace('%COMMENT_DATE%', $comment->comment_date, $subject);
			
				// as above for the body of the email, only with the option to include the entire post text and comment author id
				$msg = str_replace('%COMMENT_ID%', $comment->comment_ID, $msg);
				$msg = str_replace('%COMMENT_URL%', $post_url . '#comment-' . $comment->comment_ID, $msg);
				$msg = str_replace('%POST_ID%', $comment->comment_post_ID, $msg);
				$msg = str_replace('%POST_URL%', $post_url, $msg);
				$msg = str_replace('%POST_TITLE%', $post_title, $msg);
				$msg = str_replace('%COMMENT_AUTHOR%', $comment->comment_author, $msg);
				$msg = str_replace('%COMMENT_AUTHOR_ID%', $comment->user_id, $msg);
				$msg = str_replace('%COMMENT_DATE%', $comment->comment_date, $msg);
				$msg = str_replace('%COMMENT_TEXT%', $comment->comment_content, $msg);
			
				// send out the email
				if ($addr != '' && $email != '') {
					$ticket = build_ticket($email, $subject, $msg, $msg);
					//echo $ticket;
					wp_mail($addr, $subject, $ticket, 'From: blogs.kent admin <' . get_site_option('admin_email') . '>');
				}
	
				// print out the success message
				$content = get_site_option('rc_success_comment');
				build_template($content);
			}
		}
		// show the feedback form by default
		else {
			feedback_form_comment($comment);
		}
	}
	else {
		wp_redirect(get_home_url());
		exit;
	}

}

/**
* Output feedback form and thank you message in whichever template
*
* @param string page contents to be output in a template
*/
function build_template($content) {
  get_header();
	echo '<div style="margin:50px;">';
	echo $content;
	echo '</div>';
  get_footer();
}

/**
* Builds up visitor info for inclusion in the final message
*
* @param string message
* @param string error message if feedback input is missing
* @return string modified message
*/
function visitor_feedback($msg, &$email, &$error, $type='') {
	$visitor_details = '';
	// if the user making the complaint is logged in, email their details
	if (is_user_logged_in()) {
		global $current_user;
		get_currentuserinfo();
		$email = $current_user->user_email;
		$visitor_details .= 'Username - ' . $current_user->user_login . "\n";
		$visitor_details .= 'User email - ' . $email . "\n";
		$visitor_details .= 'User first name - ' . $current_user->user_firstname . "\n";
		$visitor_details .= 'User last name - ' . $current_user->user_lastname . "\n";
		$visitor_details .= 'User display name - ' . $current_user->display_name . "\n";
		$visitor_details .= 'User ID - ' . $current_user->ID . "\n";
	}
	// otherwise use name and email form fields
	else {
		if (!isset($_POST['email']) || $_POST['email'] == '') {
			$error .= 'please enter your email address<br />';
		}
		else {
			$email  = htmlentities(strip_tags(trim($_POST['email'])));
			$visitor_details .= 'email - ' . $email . "\n";
		}
		if (!isset($_POST['name']) || $_POST['name'] == '') {
			$error .= 'please enter your name<br />';
		}
		else {
			$visitor_details .= 'name - ' . htmlentities(strip_tags(trim($_POST['name'])));
		}
	}
	$msg = str_replace('%VISITOR_DETAILS%', $visitor_details, $msg);
	
	// include the feedback from the person making the complaint in the body of the email
	$feedback = '';
	if (!isset($_POST['feedback']) || $_POST['feedback'] == '') {
		$error .= 'please let us know what your concern is<br />';
	}
	else {
		$feedback = htmlentities(strip_tags(trim($_POST['feedback'])));
	}
	if ($type == 'post') {
		$msg = str_replace('%VISITOR_FEEDBACK_POST%', $feedback, $msg);
	}
	elseif ($type == 'comment') {
		$msg = str_replace('%VISITOR_FEEDBACK_COMMENT%', $feedback, $msg);
	}
	return $msg;
}

/**
* Builds up the feedback form for posts
*
* @param object post data
* @param string admin-defined content to print to the feedback form page
* @param int post id
* @param string key used in url
* @param string optional error message
*/
function feedback_form_post($post, $error='') {
	// display the header of the report form, which can optionally contain information about the post
	$content = get_site_option('rc_confirm_header_post');
	$content = str_replace('%POST_URL%', get_permalink($post->ID), $content);
	$content = str_replace('%POST_TITLE%', get_the_title($post->ID), $content);
	$content = str_replace('%POST_AUTHOR%', $post->post_author, $content);
	$content = str_replace('%POST_DATE%', $post->post_date, $content);
	$content = str_replace('%POST_TEXT%', $post->post_content, $content);
	
	// error msg header
	$email = '';
	$name = '';
	$feedback = '';
	if ($error != '') {
		$content .= '<div style="color:red;">' . $error . '</div>';
		$email = htmlentities(strip_tags(trim($_POST['email'])));
		$name = htmlentities(strip_tags(trim($_POST['name'])));
		$feedback = htmlentities(strip_tags(trim($_POST['feedback'])));
	}
	
	// report form
	$content .= '<form method="post" name="confirm" action="">';
	$content .= (function_exists('wp_nonce_field')) ? wp_nonce_field('report-concern-confirm-post_'.$post->ID, '_wpnonce', false, false) : '';
	$content .= '<input type="hidden" name="p" value="' . $post->ID . '" />';
	// show email and name boxes to people who aren't logged in
	if (!is_user_logged_in()) {
		$content .= '<p>Your name (required) <input type="text" name="name" value="' .  $name . '" size="20" /></p>';
		$content .= '<p>Your email address (required) <input type="text" name="email" value="' .  $email . '" size="20" /></p>';
	}
	$content .= '<p><textarea name="feedback" id="feedback" cols="45" rows="5">' .  $feedback . '</textarea></p>';

	$content .= '<input type="submit" name="submit" value="' . get_site_option('rc_confirm_button_post') . '" />';
	$content .= '</form>';
	build_template($content);
	
	

}



/**
* Builds up the feedback form for comments
*
* @param object comment data
* @param string admin-defined content to print to the feedback form page
* @param int comment id
* @param string key used in url
* @param string optional error message
*/
function feedback_form_comment($comment, $error='') {
	// show report form header which can contain various pieces of information about the comment
	$content = get_site_option('rc_confirm_header_comment');
	$content = str_replace('%POST_URL%', get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID, $content);
	$content = str_replace('%POST_TITLE%', get_the_title($comment->comment_post_ID), $content);
	$content = str_replace('%COMMENT_URL%', get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID, $content);
	$content = str_replace('%COMMENT_AUTHOR%', $comment->comment_author, $content);
	$content = str_replace('%COMMENT_DATE%', $comment->comment_date, $content);
	$content = str_replace('%COMMENT_TEXT%', $comment->comment_content, $content);
	
	// error msg header
	$email = '';
	$name = '';
	$feedback = '';
	if ($error != '') {
		$content .= '<div style="color:red;">' . $error . '</div>';
		$email = htmlentities(strip_tags(trim($_POST['email'])));
		$name = htmlentities(strip_tags(trim($_POST['name'])));
		$feedback = htmlentities(strip_tags(trim($_POST['feedback'])));
	}
	
	// report form
	$content .= '<form method="post" name="confirm" action="' . basename($_SERVER["PHP_SELF"]) . '">';
	$content .= (function_exists('wp_nonce_field')) ? wp_nonce_field('report-concern-confirm-comment_'.$comment->comment_ID, '_wpnonce', false, false) : '';
	$content .= '<input type="hidden" name="c" value="' . $comment->comment_ID . '" />';
	// show email and name boxes to people who aren't logged in
	if (!is_user_logged_in()) {
		$content .= '<p>Your name (required) <input type="text" name="name" value="' .  $name . '" size="20" /></p>';
		$content .= '<p>Your email address (required) <input type="text" name="email" value="' .  $email . '" size="20" /></p>';
	}
	$content .= '<p><textarea name="feedback" id="feedback" cols="45" rows="5">' .  $feedback . '</textarea></p>';
	
	$content .= '<input type="submit" name="submit" value="' . get_site_option('rc_confirm_button_comment') . '" />';
	$content .= '</form>';
	build_template($content);
}

function build_ticket($email, $subject, $msg, $msg_user) {
	// use the username if the person making the report is logged in
	if (is_user_logged_in()) {
		global $current_user;
		$username = $current_user->user_login;
	}
	else {
		$username = 'unknown';
	}
	$ticket = <<<EOT
Schema: UKCHelpDesk
Server: abyss.kent.ac.uk
Login: Automated Logging
TCP: 20000
Password: tieg1TahEicei9Ra
Action: Submit
Format: Short

logged by ! 2!: Automated Logging
Email+ !536870920!: $email
Name !536870914!: $username
To: (email addresses) !536870972!: $email
Description !536870946!: 

$msg

login+ !536870915!: unk
Title ! 8!: $subject
work log !536870923!: 
telephone number !536870919!: 
Send email !536870939!: yes
Status !111111!: New
text of mail !536870922!: Thank you for reporting a concern with a blog post or comment at blogs.kent.ac.uk
Details of your complaint are as follows:

$msg_user

Best wishes
Solution !536870949!: 
Category !111555!: Web
Sub-category !111444!: Web application
Quality and Standards !111666!: Complaint 
Type of Ticket !111333!: Quality and Standards
Assignees !111777!: Quality__band__bStandards

EOT;
	return $ticket;
}