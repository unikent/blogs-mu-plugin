<?php
/**
 * Make video embeds responsive.
 *
 * @return string
 */
function kent_responsive_videos_embed_html( $html ) {
	if ( empty( $html ) || ! is_string( $html ) ) return $html;

	// Queue responsive video JS.
	wp_enqueue_script( 'responsive-videos-min-script', plugins_url( 'responsive-videos/js/responsive-videos.min.js', dirname(__FILE__))
	, array( 'jquery' ), '1.1', true );


	return '<div class="kent-video-wrapper">' . $html . '</div>';
}

// After theme setup, init responsive video's
add_action( 'after_setup_theme', function(){

	// Hook to filters
	add_filter( 'wp_video_shortcode', 'kent_responsive_videos_embed_html' );
	add_filter( 'embed_oembed_html',  'kent_responsive_videos_embed_html' );
	add_filter( 'video_embed_html',   'kent_responsive_videos_embed_html' );

}, 99);



