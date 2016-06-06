# blogs-mu-plugin
Must use plugin for blogs.kent [unikent/blogs](https://github.com/unikent/blogs)

This plugin combines a number of components/features some of which were previously in separate plugins.

Components:

- [Aggregator](#Aggregator)

- [Analytics](#analytics)

- [Blogs Footer](#blogs-footer)

- [Multisite Cron](#cron)

- [Kent Nav Bar](#kent-nav-bar)

- [Login Form](#login-form)

- [Report Concern](#report-concern)

- [Subheadings](#subheadings)

- [Additional Misc Functions](#misc)

 
## Usage
This product is publicly available under the terms of the MIT license included in this repository. Please refer to the current [brand guidelines](https://www.kent.ac.uk/brand) for use of the existing brand.

## Installing
Download and install contents to the root of your mu-plugins folder within wp-content, you may need to create this directory if it doesnt exist.

To install via composer see [composer installers](https://github.com/composer/installers), note this plugin should be in the root of mu-plugins not a subfolder so your composer.json should contain the following:
 
```

	{
	   "extra": {
		 "installer-paths": {
		   "web/app/mu-plugins/": ["type:wordpress-muplugin"]
		 }
	   }
	}
   
```

## Creating A Build
In order to compress the assets (CSS and JS) for a distribution.

1. Install Node.js - this includes npm by default.

2. Install Grunt globally - its quite useful! `npm install -g grunt` or `npm install -g grunt-cli` for the cli version. 

4. Install the dependencies of our Grunt task - `npm install` from the kentblogs directory.

3. Run Grunt - `grunt` from the kentblogs directory.


## <a name="aggregator">Aggregator</a>
Aggregates content from across all blogs to a single "Recent posts" list.

Example usage here [http://blogs-test.kent.ac.uk/](http://blogs-test.kent.ac.uk/)

The list is currently limited to the latest 60 items. Only *published* **posts** are accepted and only if they have an image. Featured image (post_thumbnail) is used, but in the absence of a featured image it will attempt to use the first image embedded in the content or in any included gallery.

List is updated on the post_save action. 

The list can be retrieved as JSON from [https://blogs.kent.ac.uk/wp-content/mu-plugins/kentblogs/aggregator/output.php](https://blogs.kent.ac.uk/wp-content/mu-plugins/kentblogs/aggregator/output.php).

Or as a php array from within a blogs site via `get_site_option('wp-multisite-post-aggregate')`

Item format:

```

	"{BLOG_ID}_{POST_ID}": {
		"id": {POST_ID},
		"title": "{POST_TITLE}",
		"slug": "{POST_NAME}",
		"date": {POST_DATE},
		"excerpt": "{POST_EXCERPT}",
		"permalink": "{PERMALINK}",
		"author": {
			"id": {USER_ID},
			"id_str": "{USER_ID}",
			"nicename": "{USER_NICENAME}",
			"display_name": "{DISPLAY_NAME}",
			"user_url": "{USER_URL}",
			"posts_url": "{AUTHOR_POSTS_URL}",
			"meta": {
				"description": "...",
				"first_name": "{FIRST_NAME}",
				"last_name": "{LAST_NAME}",
				"nickname": "{NICKNAME}"
			}
		},
		"featured_image": {
			"id": {ATTACHMENT_ID},
			"slug": "{ATTACHMENT_SLUG}",
			"description": "{ATTACHMENT_DESC}",
			"caption": "{ATTACHMENT_CAPTION}",
			"title": "{ATTACHMENT_TITLE}",
			"mime_type": "{MIME}",
			"alt_text": "{ATTACHMENT_ALT}",
			"sizes": {
				"full": {
					"height": {IMG_HEIGHT},
					"url": "{IMG_URL}",
					"width": {IMG_WIDTH}
				},
				"thumbnail": {
					...
				},
				"medium": {
					...
				},
				"large": {
					...
				},
				{additional size}: {
					...
				}
			}
		},
		"blog_id": "{BLOG_ID}",
		"blog_name": "{BLOG_TITLE}",
		"blog_path": "{BLOG_PATH}"
	},
	
```

**NOTE this component is dependant on the [Thermal API](https://github.com/unikent/thermal-api) plugin being installed

## <a name="analytics">Analytics</a>
Simple insert of Google Analytics JS to the footer in production environment only.


## <a name="blogs-footer">Blogs Footer</a>
Adds a simple footer containing a disclaimer, *condition of use* and *guideline* links.

Footer attempts to stick to the bottom of the window in the event that the page is shorter than the window height.

Attempts to deal with possibel margin or padding on the `body` tag.


## <a name="cron">Multisite Cron</a>
An aggregator/handler/throttler for wp-cron in a wordpress multisite install.

Whenever an individual blog adds/modifies/deletes a task from its own cron schedule a central list (site option) is updated with this blogs details and the timestamp of the next task due.

This plugin provides a script that will execute the wp-cron job of the top *5* blogs on this central cron list each time it is hit.

### Usage
1. add `define('DISABLE_WP_CRON','TRUE');` to your wp-config.php file to disable the native wp-cron handling. *This is already done on blogs.kent.ac.uk*

2. add a cron task to execute the `wp-multisite-cron.php` file on a regular schedule. this can be run directly from the php cli or requested via curl/wget.

3. adjust timing to suit dependant on blog activity and number of blogs. the number of crons executed per request could also be increased if required.


## <a name="kent-nav-bar">Kent Nav Bar</a>
Implements the [Kent Nav Bar](https://github.com/unikent/kent-nav-bar) across all blogs.

Uses a local config to replace the toplinks, removing the standard dropdown.

The background colour of the bar can be optionaly changed from the default blue to a charcoal for each blog using a settings page:

Settings -> Kent Nav Bar [example](https://blogs.kent.ac.uk/wp-admin/options-general.php?page=kent-nav-bar.php)


## <a name="login-form">Login Form</a>
Customises the Wordpress login form for Kent.
 
**Also includes the [kent nav bar](#kent-nav-bar) and uses the kent-font included within it so is dependant on this component being present.**

## <a name="report-concern">Report Concern</a>
Adds links to *Report Concern* on each post and comment output across all blogs.

Links will open a short form which will submit to a ticket in footprints.

Various config options available in Network Admin -> Settings -> [Report Concern](https://blogs.kent.ac.uk/wp-admin/network/settings.php?page=report-concern.php)  


## <a name="subheadings">Subheadings</a>
Adds a Subheading field to posts using a custom metabox. Value is stored in a "SubHeading" meta.

can be retrieved using `get_post_meta({post_id},'SubHeading',true)` from within wordpress

or it is surfaced in the [Thermal API](https://github.com/unikent/thermal-api) output for a post.


## <a name="misc">Additional Misc Functions<a>
- Forces IE Edge rendering to prevent compatibility mode on *intranet* sites when within kent domain

- Disables pingbacks globaly by filtering the XMLRPC method
 
- Cleans up shortcode markup left behind by removal of the ShowHide plugin
 
- Removes additional add user forms and redirects add_user page to the LDAP aware add user page.

- Provides a settinsg page to nework admisn to clear and regenerate globals like the [aggregator](#aggregator) and the [multisite cron queue](#cron)
