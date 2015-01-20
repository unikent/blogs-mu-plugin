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
require_once('helper.php');

$aggregate_data = get_site_option('wp-multisite-post-aggregate');

if($aggregate_data === false){
	$aggregate_data = kentblogs_aggregator_init_posts();
}

if(empty($aggregate_data)){
	echo "no data available.";
	die();
}

header("Content-Type: application/json");
echo json_encode($aggregate_data);
