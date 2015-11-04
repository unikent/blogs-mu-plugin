<?php

wp_register_style('kent-blogs-social-buttons',plugins_url( 'kent-blogs-social-buttons.css' , __FILE__ ));

class KentSocialShare {

	public $services = array(
		'facebook' => array(
			'name' => 'Facebook',
			'icon' => 'facebook',
			'link' => 'http://www.facebook.com/sharer.php?u={url}&amp;t={title}'
		),
		'twitter' => array(
			'name' => 'Twitter',
			'icon' => 'twitter',
			'link' => 'http://twitter.com/home?status={title}%20{url}'
		),
		'google-plus' => array(
			'name' => 'Google Plus',
			'icon' => 'google-plus',
			'link' => 'https://plus.google.com/share?url={url}'
		),
		'linkedin' => array(
			'name' => 'Linked In',
			'icon' => 'linkedin',
			'link' => 'http://linkedin.com/shareArticle?mini=true&amp;url={url}&amp;title={title}'
		),
		'email' => array(
			'name' => 'Email',
			'icon' => 'email',
			'link' => 'mailto:content={url}&amp;title={title}'
		)
	);

	public function generateSocialLinks($url, $title){

		$html = '';
		foreach($this->services as $service){
			// Generate ShareLink
			$link = str_replace(array('{url}', '{title}'), array($url, $title), $service['link']);
			$icon =  $service['icon'];

			$html .= "<li><a href='{$link}' target='_blank'><i class='ksocial-{$icon}' title='Share via {$service['name']}'></i></a></li>";
		}

		return '<ul class="kent-social-links">'.$html.'</ul>';
	}

}
function kentblogs_addSocialShareIcons($html){
	global $post;
	
	// show everywhere if not
	if(get_option('kb_social_sharing_homepage') != 'show'){
		// Only apply on single posts
		if(!is_singular('post')) return $html;
	}
	

	$sharing = get_option('kb_social_sharing');

	if(empty($sharing)){
		return $html;
	}

	

	$kSocialShare = new KentSocialShare();
	$markup = $kSocialShare->generateSocialLinks(get_permalink($post->ID), $post->post_title);

	switch($sharing){
		case "above":
			return $markup . $html;
			break;
		case "below":
			return $html . $markup;
			break;
		case "both":
			return $markup . $html . $markup;
			break;
	}

	return $html;
}

add_filter('the_content', 'kentblogs_addSocialShareIcons');

function kentblogs_add_social_scripts(){
	$sharing = get_option('kb_social_sharing');

	if(!empty($sharing)){
		wp_enqueue_style('kent-blogs-social-buttons');
	}
}
add_action('wp_enqueue_scripts', 'kentblogs_add_social_scripts', 101);

add_action('kentblogs_blog_options_page','kentblogs_sharing_button_options',15);

function kentblogs_sharing_button_options(){

	if (isset($_POST['update_kentblog_options'])) {
		if(isset($_POST['kb_social_sharing']) && !empty($_POST['kb_social_sharing'])){
			update_option('kb_social_sharing', $_POST['kb_social_sharing']);
			update_option('kb_social_sharing_homepage', $_POST['kb_social_sharing_homepage']);
		}else{
			delete_option('kb_social_sharing');
			delete_option('kb_social_sharing_homepage');
		}
	}

	// Get current options
	$kb_social_sharing = isset($_POST['kb_social_sharing']) ? $_POST['kb_social_sharing'] : get_option('kb_social_sharing');
	$kb_social_sharing_homepage = isset($_POST['kb_social_sharing_homepage']) ? $_POST['kb_social_sharing_homepage'] : get_option('kb_social_sharing_homepage');

	?>
	<div class=wrap>
		<h2>Social Sharing Options</h2>
		<table class="form-table">
			<tbody>
			<tr>
				<th>
					<label for="kb_social_sharing">Social Sharing Buttons</label>
				</th>
				<td>
					<select id="kb_social_sharing" name="kb_social_sharing">
						<option value="">None - Disabled</option>
						<option value="above"<?php echo ($kb_social_sharing == "above")?'selected="selected"':''; ?>>Above Post</option>
						<option value="below"<?php echo ($kb_social_sharing == "below")?'selected="selected"':''; ?>>Below Post</option>
						<option value="both"<?php echo ($kb_social_sharing == "both")?'selected="selected"':''; ?>>Above &amp; below Post</option>
					</select>
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<label for="kb_social_sharing_homepage"><input type='checkbox' name="kb_social_sharing_homepage" value="show" <?php echo ($kb_social_sharing_homepage == "show")?'checked="checked"':''; ?>>Show sharing buttons on homepage</label>
				</td>
			</tbody>
		</table>
	</div>
	<?php
}
