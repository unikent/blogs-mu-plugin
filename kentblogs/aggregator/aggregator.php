<?php 

include 'helper.php';
include '/../vendor/MM_MediaAccess.php';

$kb_aggregator_media_access = new HaddowG\MetaMaterial\MM_MediaAccess(array(
    'name'=>'fdefault',
    'file_type'=>'image',
    'buttonText'=>'Select Image',
    'modal_button_text'=>'Select Image',
    'modal_title'=>'Choose a Default Image',
    'placeholderImg'=>'/wp-content/mu-plugins/kentblogs/aggregator/featured_default.png'));

// This is run every time a post is saved/updated
add_action('save_post', 'kentblogs_aggregator_post_saved');

add_action("archive_blog", "kentblogs_aggregator_init_posts");
add_action("delete_blog", "kentblogs_aggregator_init_posts");
add_action("deactivate_blog", "kentblogs_aggregator_init_posts");


function kentblogs_aggregator_options_menu() {
    if (function_exists('add_submenu_page') && is_admin()) {
        add_submenu_page('options-general.php', 'Aggregator Options', 'Blogs Aggregator','manage_options', basename(__FILE__), 'kentblogs_aggregator_options');
    }
}
add_action('admin_menu','kentblogs_aggregator_options_menu');

function kentblogs_aggregator_options(){
    global $kb_aggregator_media_access;
    $kb_aggregator_media_access->setGroupName('fdefault');

    $ex = get_option('kb_exclude_from_aggregator');
    $img = get_option('kb_default_aggregator_img');

    if (isset($_POST['update_aggregator'])) {
        if(isset($_POST['kb_exclude_from_aggregator'])){
            update_option('kb_exclude_from_aggregator',$_POST['kb_exclude_from_aggregator']);
            $changed = (((!empty($ex)) && ($ex!==$_POST['kb_exclude_from_aggregator'])) || (empty($ex) && (!empty($_POST['kb_exclude_from_aggregator']))));
            $ex='excluded';
        }else{
            delete_option('kb_exclude_from_aggregator');
            $changed = (!empty($ex));
            $ex=false;
        }

        if(isset($_POST['kb_default_aggregator_img'])) {
            $changed= $changed || (((!empty($img)) && ($img!==$_POST['kb_default_aggregator_img'])) ||( empty($img) && (!empty($_POST['kb_default_aggregator_img']))))  ;

            update_option('kb_default_aggregator_img', $_POST['kb_default_aggregator_img']);
            $img =  $_POST['kb_default_aggregator_img'];
        }
        if($changed) {
            kentblogs_aggregator_init_posts();
        }
    }

    ?>
    <script>
        jQuery(document).ready(function($){
           $('#kb_agg_clear_img').click(function(e){e.preventDefault();
           $('.media-field-id-fdefault').val('');
           $img = $('.media-field-thumb-fdefault').first();
               $img.attr('src',$img.data('placeholder'));
           });
        });
    </script>
    <div class=wrap>
        <h2>Blogs Aggregator Options</h2>
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="kb_exclude_from_aggregator">Exclude this Blog</label>
                    </th>
                    <td>
                        <input type="checkbox" id="kb_exclude_from_aggregator" name="kb_exclude_from_aggregator" value="exclude"<?php echo (!empty($ex)?' checked="checked"':''); ?>>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="kb_default_aggregator_img">Default Featured Image</label>
                    </th>
                    <td>
                        <?php
                            echo $kb_aggregator_media_access->getField($img,'kb_default_aggregator_img');
                            if(!empty($img)){
                                echo '<button class="button" id="kb_agg_clear_img">Clear Image</button>';
                            }
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="update_aggregator"></p>
        </form>
    </div>
<?php
}