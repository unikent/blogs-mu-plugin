<?php

/**
 * Add post custom fields to the api.
 */
function kentblogs_add_subheading_to_api($data, &$post, $state ) {
	if( $state === 'read' ){
	    $subheading = get_post_meta($post->ID,'SubHeading',true);
		$data->meta->custom_fields = array('sub_title'=> $subheading);
	}
	return $data;
}
add_filter( 'thermal_post_entity', 'kentblogs_add_subheading_to_api', 10, 3);

function kentblogs_subheading_form(){
    global $post;
    $value= get_post_meta($post->ID,'SubHeading',true);
   echo '<input type="text" name="kb_subheading" value="' . $value . '" placeholder="Sub-heading (optional, not all themes will display)">';
}

function kentblogs_subheading_save($post_id, $post, $update){
    if(isset($_POST['kb_subheading'])){
        update_post_meta($post_id, 'SubHeading', $_POST['kb_subheading']);
    }
    return $post_id;
}
add_action('save_post', 'kentblogs_subheading_save',10,3);


function kentblogs_add_subheading_metabox()
{
    add_meta_box('kentblogs_subheading', 'Subheading', 'kentblogs_subheading_form', 'post', 'after_title');
}


add_action('current_screen','kentblogs_add_subheading_metabox');

function kentblogs_edit_form_after_title()
{
    global $post, $typenow, $wp_meta_boxes;
    do_meta_boxes( $typenow, 'after_title', $post);

    unset( $wp_meta_boxes[$typenow]['after_title'] );
}
add_action('edit_form_after_title', 'kentblogs_edit_form_after_title');

function kentblogs_subheading_scripts(){
    ?>
    <script type="text/javascript">
        /* <![CDATA[ */

        jQuery(function($)
        {
            //hide screen options
            jQuery('.metabox-prefs label[for=kentblogs_subheading-hide]').remove();

            //remove title
            jQuery('#kentblogs_subheading > h3, #kentblogs_subheading > .handlediv').remove();

        });
        /* ]]> */
    </script>
    <style type="text/css">
        #after_title-sortables{ margin-top:20px; }
        #kentblogs_subheading {
            border: none;
            background: transparent;
        }
        #kentblogs_subheading .inside{
            margin:0;
            padding:0;
        }
        #kentblogs_subheading input{
            background-color: #fff;
            font-size: 1.3em;
            height: 1.7em;
            line-height: 100%;
            margin: 0;
            outline: 0 none;
            padding: 2px 8px;
            width: 100%;
        }
    </style>
    <?php
}
add_action('admin_head', 'kentblogs_subheading_scripts');
