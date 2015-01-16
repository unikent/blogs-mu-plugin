<?php
/*
 * Login Form
 */

add_action("login_form", "kentblogs_login_message");
add_action("signup_hidden_fields","kentblogs_register_message");

// login message
function kentblogs_login_message() {
    echo '<p><strong>University of Kent users</strong> - please enter your Kent username and password above. You don\'t need to register separately for a blogs account.</p><br /><br />';
    echo '<p>If you <strong>don\'t</strong> have either a University of Kent account or a blogs.kent account, please click the \'Register\' link below to sign up for an account.</p><br /><br />';
}
// registration message
function kentblogs_register_message() {
    echo '<p>If you have a University of Kent account you don\'t need to register for a blogs account. Just <a href="wp-login.php">login</a> with your Kent username and password.</p>';
    echo '<p>Registering for an account here will register you for all University of Kent blogs.</p>';
}

// add login scripts
function kentblogs_login_head() {

    kentblogs_nav_bar();

    echo '
	<style type="text/css">
	#login h1 a{background:none; width:120px;}
	#login h1 a:before{content:"\e002"; text-indent:0; border-bottom: 0 none; color: #05345c; display: inline-block; float: left; font-family: kentfont-lite; font-size: 65px; font-style: normal; font-weight: 400; line-height: 1; text-decoration: inherit; text-rendering: optimizelegibility; text-transform: none; vertical-align: middle;}
	#user_email { height:20px; width:100%; font-size: 16px; margin:5px 0; }
	#user_name { height:20px; width:100%; font-size: 16px; margin:5px 0; }
	label { font-weight:700; font-size:15px; display:block; margin:10px 0; }
	</style>
	';
}

add_action( 'login_enqueue_scripts', 'kentblogs_login_head', 10 );