<?php
include_once "flickr.php";
include_once "soundcloud.php";
include_once "slideshare.php";
include_once "youtube.php";

function shortcode_new_to_old_params( $params, $old_format_support = false ) {
	$str = '';

	if ( $old_format_support && isset( $params[0] ) ) {
		$str = ltrim( $params[0], '=' );
	} elseif ( is_array( $params ) ) {
		foreach ( array_keys( $params ) as $key ) {
			if ( ! is_numeric( $key ) )
			$str = $key . '=' . $params[$key];
		}
	}

	return str_replace( array( '&amp;', '&#038;' ), '&', $str );
}
