<?php

// Get composer
require_once(dirname( __FILE__ )."/../../../../../vendor/autoload.php");

// Attempt to load config
$config = dirname( __FILE__ ) .'/../../../../../';
if (!file_exists($config.'/.env')) die("No config (.env) found :("); 

Dotenv::load($config);
Dotenv::required(array('WP_SITEURL'));
$blog_url = parse_url(getenv('WP_SITEURL'));

// Fake some server vars to make WP happy - use .env to make this portable
$_SERVER = array(
  "HTTP_HOST" => $blog_url['host'],
  "SERVER_NAME" => "http://".$blog_url['host'],
  "REQUEST_URI" => isset($blog_url['path']) ? $blog_url['path'] : '',
  "REQUEST_METHOD" => "GET"
);

// Boot wp (Shortinit set)
require_once( dirname( __FILE__ ) . '/../../../../wp/wp-load.php' );
// Load driver
require_once( 'cron-helper.php' );

// Get crons to run
$crons_to_run = multisite_cron_get_next(5);

// No crons to run
if(sizeof($crons_to_run)===0){
	echo "\nNo CRON's need running at this time. \n";
	die();
}

// Run some CRON's :D

echo "\nRunning next 5 crons \n\n";

foreach($crons_to_run as $cron){

	// Fire all curls
	if(isset($cron['path']) &&  $cron['path'] !== ''){

		// clear trailing /
		$cron['path']= rtrim($cron['path'], '/');

		$path = $cron['path'].'/wp-cron.php';
		echo "Curl: {$path}  [Blog: {$cron['blogid']} - CRON scheduled for " .$cron['timestamp'].']';
		
		$success = wp_remote_get( $path );

		// report status
		if(!is_wp_error($success) && isset($success['response']['code']) && $success['response']['code'] == 200){
			echo "- OK \n";
		}else{
			if(is_wp_error($success)){
				error_log(print_r($success->get_error_codes(),true));
				if(strpos($success->get_error_message(),'Operation timed out')!==false){
					echo "- OK [timeout] \n";
				}else{
					echo "- FAIL ".$success->get_error_message()."\n";
				}
				
			}else{
				echo "- FAIL \n";
			}
		}
	}
}

echo "\n";
echo "WP-Multsite-cron update complete.";
