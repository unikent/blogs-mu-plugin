<?php

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
			'name' => 'Linked In',
			'icon' => 'linkedin',
			'link' => 'http://linkedin.com/shareArticle?mini=true&amp;url={url}&amp;title={title}'
		)
	);

	public function generateSocialLinks($url, $title){

		$html = '';
		foreach($this->services as $service){
			// Generate ShareLink
			$link = str_replace(array('{url}', '{title}'), array($url, $title), $service['link']);
			$icon =  $service['icon'];

			$html .= "<li><a href='{$link}' target='_blank'><i class='kf-{$icon}' title='Share to {$service['title']}'></i></a></li>";
		}

		return '<ul class="kent-social-links">'.$html.'</ul>';
	}

}
function kentblogs_addSocialShareIcons(){

		$kSocialShare = new KentSocialShare();
		$markup = $kSocialShare->generateSocialLinks('URL', 'TITLE');
		//hook to WP top/bottom on posts depending on settings
		// Ad widget while we're at it

		//requires fontawsome or kent font
		return $markup. $html . $markup;
}
// check options
// if enabled,
if(is_single()){
	add_filter('the_content', 'kentblogs_addSocialShareIcons');
}