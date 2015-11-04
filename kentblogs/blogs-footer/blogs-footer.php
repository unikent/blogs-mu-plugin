<?php

wp_register_style('kent-blogs-footer',plugins_url( 'kent-blogs-footer.css' , __FILE__ ));

function kentblogs_add_footer(){

	$theme = wp_get_theme();
	$theme = $theme->get_template();
	if( $theme !== 'twentyfifteen') {

		kentblogs_generatefooter();
		?>
		<script type="text/javascript">
			styles = [];

			bodystyle = window.getComputedStyle(document.body);
			if (bodystyle.marginLeft != "0px") {
				styles.push('margin-left: -' + bodystyle.marginLeft + ';');
			}
			if (bodystyle.marginRight != "0px") {
				styles.push('margin-right: -' + bodystyle.marginRight + ';');
			}
			if (bodystyle.paddingLeft != "0px") {
				styles.push('margin-left: -' + bodystyle.paddingLeft + ';');
			}
			if (bodystyle.paddingRight != "0px") {
				styles.push('margin-right: -' + bodystyle.paddingRight + ';');
			}
			style = '';
			if (styles.length > 0) {
				style = styles.join(' ');
			}
			document.getElementById('kent-blogs-footer').style = style;
			// Window load event used just in case window height is dependant upon images
			jQuery(window).bind("load", function () {

				var footerHeight = 0,
					footerTop = 0,
					$footer = jQuery("#kent-blogs-footer");

				jQuery(window)
					.scroll(positionFooter)
					.resize(positionFooter);

				jQuery(window).trigger('resize');

				function positionFooter() {

					footerHeight = $footer.outerHeight();

					footerTop = (jQuery(window).scrollTop() + jQuery(window).height() - footerHeight - 75) + "px";

					if ((jQuery(document.body).height() + footerHeight) < jQuery(window).height()) {
						$footer.css({
							position: "absolute",
							top: footerTop
						});
					} else {
						$footer.css({
							position: "relative"
						})
					}
				}
			});

		</script>
		<?php
	}
}


add_filter('wp_footer','kentblogs_add_footer',99);


function kentblogs_generatefooter(){
	global $post;

	$blog_details = get_blog_details();
	$post_id = get_the_id($post);
	$post_title = get_the_title($post);

	$body = array(
		'Report concern from:',
		'Blog URL: ' . $blog_details->siteurl,
		'Post ID: ' . ($post_id ? $post_id : 'N/A'),
		'Post Title: ' . ($post_title ? $post_title : 'N/A'),
	);

	$body = implode("%0D%0A", $body);
	?>
	<div id="kent-blogs-footer">The views expressed in this blog are not necessarily those of the University of Kent. View <a href="http://www.kent.ac.uk/is/regulations/it/index.html?tab=blogs-conditions-of-use" target="_blank">Conditions of use</a> and <a href="http://www.kent.ac.uk/itservices/email/blogs.html" target="_blank">Guidelines</a>.
		<a href="mailto:qands@kent.ac.uk?subject=Report Concern from blogs.kent&body=<?php echo $body; ?>">Report concern</a>
	</div>
	<?php
}

add_action('twentyfifteen_credits','kentblogs_generatefooter');

function kentblogs_footer_scripts(){

	$theme = wp_get_theme();
	$theme = $theme->get_template();
	if( $theme !== 'twentyfifteen') {
		wp_enqueue_style('kent-blogs-footer');
	}
}

add_action( 'wp_enqueue_scripts', 'kentblogs_footer_scripts');
