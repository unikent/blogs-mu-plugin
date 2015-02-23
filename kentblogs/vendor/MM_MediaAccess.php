<?php

/**
 * @package     MetaMaterial
 * @author      Gregory Haddow
 * @copyright   Copyright (c) 2014, Gregory Haddow, http://www.greghaddow.co.uk/
 * @license     http://opensource.org/licenses/gpl-3.0.html The GPL-3 License with additional attribution clause as detailed below.
 * @version     0.1
 * @link        http://www.greghaddow.co.uk/MetaMaterial
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program has the following attribution requirement (GPL Section 7):
 *     - you agree to retain in MetaMaterial and any modifications to MetaMaterial the copyright, author attribution and
 *       URL information as provided in this notice and repeated in the licence.txt document provided with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class MM_MediaAccess
{

    /**
     * User defined identifiers for the css class names of the id field, tumbnail and linking elements
     * their presence will trigger javascript behaviour
     *
     * @since   0.1
     * @access  private
     * @var     array required
     */
    private $classNames = array(
        'id'    =>  'media-field-id',
        'thumb' =>  'media-field-thumb',
        'url'   =>  'media-field-url',
        'trigger'   =>  'media-field-trigger',
        'title'     => 'media-field-title',
        'filename'  => 'media-field-filename'
    );


    /**
     * Used to track the current groupname for grouping media field elements.
     *
     * @since   0.1
     * @access  private
     * @var     string
     * @see     setGroupName()
     */
    protected $groupname = null;

    public $showThumb = true;

    public $showField = false;

    public $useIcons = false;

    public $thumb_size = 'thumbnail';

    public $echo = false;

    public $thumbTrigger = true;

    public $buttonText = 'Add Media';

    public $modal_button_text = 'Add Media';

    public $modal_title = 'Choose a file';

    public $file_type = 'any'; // any, image, audio, video, text or any single valid mime type e.g. application/pdf

    public $allowed_extensions = '*';

    public $placeholderImg = null;

    public $placeholderImgText = 'Add Media';

    private static $instances = array();

    private $name;

    public static function getInstance($name)
    {
        // Check if an instance exists with this key already
        if(!array_key_exists($name, self::$instances)) {
            // instance doesn't exist yet, so create it
            self::$instances[$name] = new self();
        }

        // Return the correct instance of this class
        return self::$instances[$name];
    }

    public function setName($name)
    {
        $this->name = $name;
        self::$instances[$name] = $this;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
    /**
     * MediaAccess class
     *
     * @since   0.1
     * @access  public
     * @param   array $config
     */
    public function __construct($config = array())
    {

        foreach ($config as $n => $v)
        {
            $this->$n = $v;
        }

        $this->setAllowedExtensions();

        if ( ! defined('WPALCHEMY_MEDIA_ACCESS_ENABLED'))
        {
            add_action( "admin_enqueue_scripts", array( $this, "enqueueMedia") );
            add_action('admin_footer', array($this, 'initOnce'));
            define('WPALCHEMY_MEDIA_ACCESS_ENABLED', true);
        }

        if ( ! defined('WPALCHEMY_MEDIA_ACCESS_ENABLED_' . strtoupper($this->name)))
        {
            add_action('admin_footer', array($this, 'init'));
            define('WPALCHEMY_MEDIA_ACCESS_ENABLED_' . strtoupper($this->name), true);
        }
    }

    public function enqueueMedia()
    {
        wp_enqueue_media();
    }

    public function setOptions(array $a = array())
    {
        if(isset($a['name'])){
            unset($a['name']);
        }
        if(isset($a['classNames'])){
            unset($a['classNames']);
        }
        foreach ($a as $n => $v)
        {
            $this->$n = $v;
        }
        $this->setAllowedExtensions();
    }

    private function setAllowedExtensions(){
        if ( 'images' == $this->file_type )
            $this->allowed_extensions = 'jpg,jpeg,png,gif';
        elseif ( 'video' == $this->file_type )
            $this->allowed_extensions = 'mpg,mov,flv,mp4';
        elseif ( 'audio' == $this->file_type )
            $this->allowed_extensions = 'mp3,m4a,wav,wma';
        elseif ( 'text' == $this->file_type )
            $this->allowed_extensions = 'txt,rtx,csv,tsv';
        elseif ( 'any' == $this->file_type )
            $this->allowed_extensions = '*';
    }


    /**
     * Used before calls to getField(), getButton() or getButtonClass() to set
     * the groupname to pair a field and button element.
     *
     * @since   0.1
     * @access  public
     * @param   string $name unique name per pair of field and button
     * @return  object $this
     * @see     setInsertButtonLabel()
     */
    public function setGroupName($name)
    {
        $this->groupname = $name;

        return $this;
    }

    /**
     * Used to insert a form field of type "text", this should be paired with a
     * button element. The name and value attributes are required.
     *
     * @since   0.1
     * @access  public
     * @param   array $attr INPUT tag parameters
     * @param   string $type either 'id' or 'thumb' indicates the desired data from the media modal
     * @return  HTML
     */
    public function getField($id, $name, $options = array() )
    {

        $groupname = isset($options['groupname']) ? $options['groupname'] : $this->groupname ;

        $options_default = array
        (
            'class'             =>  $this->classNames['id'] . '-' . $groupname,
            'thumbClass'        =>  $this->classNames['thumb'] . '-' . $groupname,
            'showThumb'         =>  $this->showThumb,
            'showField'         =>  $this->showField,
            'useIcons'          =>  $this->useIcons,
            'thumb_size'        =>  $this->thumb_size,
            'echo'              =>  $this->echo,
            'thumbTrigger'      =>  $this->thumbTrigger,
            'modal_button_text' =>  $this->modal_button_text,
            'modal_title'       =>  $this->modal_title,
            'file_type'     =>  $this->file_type,
            'allowed_extensions' => $this->allowed_extensions,
            'placeholderImg'     => $this->placeholderImg,
            'placeholderImgText'   => $this->placeholderImgText
        );

        if (isset($options['class']))
        {
            $options['class'] = $options_default['class'] . ' ' . trim($options['class']);
        }
        if (isset($options['thumbClass']))
        {
            $options['thumbClass'] = $options_default['thumbClass'] . ' ' . trim($options['thumbClass']);
        }

        $options = array_merge($options_default, $options);


        $out = '';
        if($options['showThumb']){

            $img_src = wp_get_attachment_image_src( $id, $options['thumb_size'], $options['useIcons'] );

            if(is_array($options['thumb_size']) && $options['thumb_size']>=2){
                $dataThumbSize = htmlspecialchars(json_encode($options['thumb_size']));
            }else{
                if(is_array($options['thumb_size'])){
                    $dataThumbSize = 'thumbnail';
                }else{
                    $dataThumbSize = $options['thumb_size'];
                }
            }


            $placeholder =array();
            if((!is_array($options['thumb_size']) )|| (is_array($options['thumb_size'] && count($options['thumb_size']<2)))){
                $size = $this->get_image_sizes($options['thumb_size']);
                if(!$size){
                    $size = $this->get_image_sizes('thumbnail');
                }
            }else{
                $size = $options['thumb_size'];
            }
            if(empty($options['placeholderImg'])){
                $placeholder[] = 'http://placehold.it/' . $size[0] . 'x' . $size[1] . '&text=' . $options['placeholderImgText'];
            }else{
                $placeholder[] = $options['placeholderImg'];
            }
            $placeholder[] = $size[0];
            $placeholder[] = $size[1];

            if(!$img_src){
                $img_src =$placeholder;
            }

            if($options['thumbTrigger']){
                $options['thumbClass'] = $options['thumbClass'] . ' ' . $this->getTriggerClass($groupname);
            }
            $out .= '<img src="' . $img_src[0] . '" class="' . $options['thumbClass'] . '" width="' . $img_src[1] . '" height="' . $img_src[2] . '" data-thumb_size="' . $dataThumbSize . '" data-modal_button_text="' . $options['modal_button_text'] . '" data-modal_title="' . $options['modal_title'] . '" data-file_type="' . $options['file_type'] . '" data-allowed_extensions="' . $options['allowed_extensions'] . '" data-placeholder=" ' . $placeholder[0] . '">';
        }
        $type = (($options['showField'])?'text':'hidden');

        $out .= '<input type="' . $type . '" name="' . $name . '" class="' . $options['class'] . '" value="' . $id . '" />';

        if($options['echo']){
            echo $out;
        }else{
            return $out;
        }
    }

    /**
     * Used to get the CSS class name(s) used for the button element. If
     * creating custom buttons, this method should be used to get the css class
     * names needed for proper functionality.
     *
     * @since   0.1
     * @access  public
     * @param   string $groupname name used when pairing a text field and button
     * @return  string css class(es)
     * @see     getButtonLink(), getButton()
     */
    public function getTriggerClass($groupname = null)
    {
        $groupname = isset($groupname) ? $groupname : $this->groupname ;

        return  $this->classNames['trigger']. '-' . $groupname;
    }

    /**
     * Used to get the CSS class name used for the field element. If
     * creating a custom field, this method should be used to get the css class
     * name needed for proper functionality.
     *
     * @since   0.2
     * @access  public
     * @param   string $groupname name used when pairing a text field and button
     * @return  string css class(es)
     * @see     getButtonClass(), getField()
     */
    public function getFieldClass($groupname = null)
    {
        $groupname = isset($groupname) ? $groupname : $this->groupname ;

        return $this->classNames['id'] . '-' . $groupname;
    }

    /**
     * Used to get the CSS class name used for the field element. If
     * creating a custom field, this method should be used to get the css class
     * name needed for proper functionality.
     *
     * @since   0.2
     * @access  public
     * @param   string $groupname name used when pairing a text field and button
     * @return  string css class(es)
     * @see     getButtonClass(), getField()
     */
    public function getThumbClass($groupname = null)
    {
        $groupname = isset($groupname) ? $groupname : $this->groupname ;

        return $this->classNames['thumb'] . '-' . $groupname;
    }

    /**
     * Used to get the CSS class name used for the a field element.
     * Note this is the generic base class name and does not include the groupname
     *
     * @since   0.1
     * @access  public
     * @param   string $type
     * @return  string css class(es)
     */
    public function getClass($type = 'id', $groupname = null)
    {
        $groupname = isset($groupname) ? $groupname : $this->groupname ;
        return $this->classNames[$type] . '-' . $groupname;
    }

    /**
     * Used to insert a WordPress styled button, should be paired with a text
     * field element.
     *
     * @since   0.1
     * @access  public
     * @return  HTML
     * @see     getField(), getButtonClass(), getButtonLink()
     */
    public function getButton(array $attr = array())
    {
        $groupname = isset($attr['groupname']) ? $attr['groupname'] : $this->groupname ;

        $tab = isset($attr['tab']) ? $attr['tab'] : $this->tab ;

        $attr_default = array
        (
            'buttonText' => $this->buttonText,
            'class' => $this->getTriggerClass($groupname) . ' button',
            'modal_button_text' =>$this->modal_button_text,
            'modal_title' => $this->modal_title,
            'file_type' => $this->file_type,
            'allowed_extensions' => $this->allowed_extensions
        );

        if (isset($attr['class']))
        {
            $attr['class'] = $this->getTriggerClass($groupname) . ' ' . trim($attr['class']);
        }

        $attr = array_merge($attr_default, $attr);

        $buttonText = $attr['buttonText'];
        unset($attr['buttonText']);

        $attr['data-modal_button_text'] = $attr['modal_button_text'];
        unset($attr['modal_button_text']);
        $attr['data-modal_title'] = $attr['modal_title'];
        unset($attr['modal_title']);
        $attr['data-file_type'] = $attr['file_type'];
        unset($attr['file_type']);
        $attr['data-allowed_extensions'] = $attr['allowed_extensions'];
        unset($attr['allowed_extensions']);
        ###

        $elem_attr = array();

        foreach ($attr as $n => $v)
        {
            array_push($elem_attr, $n . '="' . $v . '"');
        }

        ###

        return '<a ' . implode(' ', $elem_attr) . '>' . $buttonText . '</a>';
    }


    public function initOnce(){
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL ;

        $file = basename(parse_url($uri, PHP_URL_PATH));

        if ($uri AND in_array($file, array('post.php', 'post-new.php','edit-tags.php','options-general.php')))
        {
            // include javascript for special functionality
            ?>
            <script type="text/javascript">
                /* <![CDATA[ */
                var img_sizes = jQuery.parseJSON('<?php echo json_encode($this->get_image_sizes()); ?>');

                var getClosestSize = function($targetsize, $attachment){
                    if($attachment.sizes){
                        if(jQuery.isArray($targetsize)){

                            var $found = false;
                            var $areas = {};
                            jQuery.each($attachment.sizes, function($_size, $data){
                                // already cropped to width or height; so use this size
                                if ( ( $data['width'] == $targetsize[0] && $data['height'] <= $targetsize[1] ) || ( $data['height'] == $targetsize[1] && $data['width'] <= $targetsize[0] ) ) {
                                    $found = [$data['url'],$data['width'],$data['height']];
                                    return false;
                                }
                                // add to lookup table: area => size
                                $areas[$data['width'] * $data['height']] = $_size;
                            });
                            if($found){
                                return $found;
                            }
                            if ( !jQuery.isEmptyObject($areas) ) {
                                // find for the smallest image not smaller than the desired size
                                keys = [];

                                for (k in $areas)
                                {
                                    keys.push(k);
                                }
                                keys.sort(function(a, b){return a-b});
                                for (i = 0; i < keys.length; i++)
                                {
                                    var $_size = $areas[keys[i]];

                                    $data = $attachment.sizes[$_size];
                                    if ( $data['width'] >= $targetsize[0] || $data['height'] >= $targetsize[1] ) {
                                        // Skip images with unexpectedly divergent aspect ratios (crops)
                                        // First, we calculate what size the original image would be if constrained to a box the size of the current image in the loop
                                        $maybe_cropped = constrainDimensions($attachment.width, $attachment.height, $data['width'], $data['height']);
                                        // If the size doesn't match within one pixel, then it is of a different aspect ratio, so we skip it, unless it's the thumbnail size
                                        if ( 'thumbnail' != $_size && ( !$maybe_cropped || ( $maybe_cropped['width'] != $data['width'] && $maybe_cropped['width'] + 1 != $data['width'] ) || ( $maybe_cropped['height'] != $data['height'] && $maybe_cropped['height'] + 1 != $data['height'] ) ) )
                                            continue;
                                        // If we're still here, then we're going to use this size
                                        $constrained =  constrainDimensions($data['width'],$data['height'],$targetsize[0],$targetsize[1]);
                                        $found = [$data['url'],$constrained['width'],$constrained['height']];
                                        return $found;
                                    }
                                }
                            }
                            $constrained =  constrainDimensions($attachment.sizes.thumbnail.width, $attachment.sizes.thumbnail.height,$targetsize[0],$targetsize[1]);
                            return [$attachment.sizes.thumbnail.url, $constrained['width'],$constrained['height']];

                        }else{
                            if($attachment.sizes[$targetsize]){
                                return [$attachment.sizes[$targetsize].url, $attachment.sizes[$targetsize].width,$attachment.sizes[$targetsize].height];
                            }else{
                                if($attachment.sizes.thumbnail){
                                    return [$attachment.sizes.thumbnail.url, $attachment.sizes.thumbnail.width,$attachment.sizes.thumbnail.height];
                                }else{
                                    return [$attachment.sizes.full.url, $attachment.sizes.full.width,$attachment.sizes.full.height];
                                }
                            }
                        }
                    }else{
                        if(jQuery.isArray($targetsize)){
                            $constrained =  constrainDimensions(48, 64,$targetsize[0],$targetsize[1]);
                            return [$attachment.icon, $constrained['width'],$constrained['height']];
                        }else{
                            if(img_sizes.$targetsize){
                                $constrained =  constrainDimensions(48, 64,img_sizes.$targetsize[0],img_sizes.$targetsize[1]);
                                return [$attachment.icon, $constrained['width'],$constrained['height']];
                            }else{
                                return [$attachment.icon,48,64];
                            }
                        }
                    }
                }

                var constrainDimensions  = function(origW, origH, maxW, maxH){
                    var h = origH;
                    var w = origW;
                    if (h > maxH) {
                        h = maxH;
                        w = Math.ceil(origW / origH * maxH);
                    }
                    if (w > maxW) {
                        h = Math.ceil(origH / origW * maxW);
                        w = maxW;
                    }
                    return {'width':w,'height':h}
                }

                /* ]]> */
            </script><?php
        }
    }
    /**
     * Used to insert global STYLE or SCRIPT tags into the footer, called on
     * WordPress admin_footer action.
     *
     * @since   0.1
     * @access  public
     * @return  HTML/Javascript
     */
    public function init()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL ;

        $file = basename(parse_url($uri, PHP_URL_PATH));

        if ($uri AND in_array($file, array('post.php', 'post-new.php','edit-tags.php','options-general.php')))
        {
            // include javascript for special functionality
            ?>
            <script type="text/javascript">
                /* <![CDATA[ */

                jQuery(function($)
                {
                    var media_frame_<?php echo $this->name; ?>;
                    var media_frame_<?php echo $this->name; ?>_trigger;


                    $(document).on('click','[class*="<?php echo $this->classNames['trigger']; ?>"]', function( event ){


                        event.preventDefault();

                        if(typeof wp.Uploader.defaults.filters.mime_types == 'undefined') {
                            wp.Uploader.defaults.filters.mime_types = [{title:'Allowed Files', extensions: '*'}];
                        }

                        $modalTitle = jQuery(this).data('modal_title') || '<?php echo $this->modal_title; ?>';
                        $modalButtonText = jQuery(this).data('modal_button_text') || '<?php echo $this->modal_button_text; ?>';
                        $fileType = jQuery(this).data('file_type') || '<?php echo $this->file_type; ?>';
                        $allowedExtensions = jQuery(this).data('allowed_extensions') || '<?php echo $this->allowed_extensions; ?>';
                        $library = {};
                        if(jQuery.inArray($fileType,['image','audio','video','text'])>=0){
                            $library = {
                                type : $fileType
                            }
                        }else{
                            if($fileType !='any'){
                                $mime = $fileType.split('/');
                                if($mime.length==2){
                                    $library = {
                                        type : $fileType
                                    }
                                }
                            }
                        }

                        var default_ext = wp.Uploader.defaults.filters.mime_types[0].extensions;
                        wp.Uploader.defaults.filters.mime_types[0].extensions = $allowedExtensions;

                        media_frame_<?php echo $this->name; ?>_trigger = this;

                        // If the media frame already exists and doesnt need to change, reopen it.
                        if ( media_frame_<?php echo $this->name; ?> &&  JSON.stringify(media_frame_<?php echo $this->name; ?>.activeLibrary)== JSON.stringify($library) && media_frame_<?php echo $this->name; ?>.activeTitle == $modalTitle && media_frame_<?php echo $this->name; ?>.activeButtonText == $modalButtonText) {
                            media_frame_<?php echo $this->name; ?>.open();
                            //choose tab
                        }else{


                            // Create the media frame.
                            media_frame_<?php echo $this->name; ?> = wp.media.frames.media_frame_<?php echo $this->name; ?> = wp.media({
                                frame: 'select',
                                library: jQuery.extend({}, $library),
                                title: $modalTitle,
                                button: {
                                    text: $modalButtonText
                                },
                                multiple: false
                            });
                            media_frame_<?php echo $this->name; ?>.activeLibrary = $library;
                            media_frame_<?php echo $this->name; ?>.activeTitle = $modalTitle;
                            media_frame_<?php echo $this->name; ?>.activeButtonText = $modalButtonText;

                            // When an image is selected, run a callback.
                            media_frame_<?php echo $this->name; ?>.on( 'select', function() {
                                // We set multiple to false so only get one image from the uploader
                                attachment = media_frame_<?php echo $this->name; ?>.state().get('selection').first().toJSON();
                                console.log(attachment);
                                //Check File Type
                                if ( !($fileType == attachment.type || $fileType == attachment.mime ) ){ return };

                                // Do something with attachment here
                                $name = jQuery(media_frame_<?php echo $this->name; ?>_trigger).attr('class').match(/<?php echo $this->classNames['trigger']; ?>-([a-zA-Z0-9_-]*)/i);
                                $name = ($name && $name[1]) ? $name[1] : '' ;
                                var $context = jQuery(media_frame_<?php echo $this->name; ?>_trigger).closest('.postbox, .mm_taxonomybox, #wpbody-content');
                                <?php foreach($this->classNames as $key =>$classname){
                                    if($key!='thumb'){
                                ?>
                                $field_<?php echo $key; ?> = jQuery('.<?php echo $classname; ?>-'+$name,$context);
                                $field_<?php echo $key; ?>.each(function(){
                                    var $this = jQuery(this);
                                    <?php
                                    if($key=='url'){
                                    ?>
                                    if($this.is('a')) {
                                        $this.attr('href',attachment.<?php echo $key; ?>);
                                        if($this.hasClass('both')){
                                            $this.html(attachment.<?php echo $key; ?>);
                                        }
                                        return;
                                    }
                                    if($this.is('img')) {
                                        $this.attr('src',attachment.<?php echo $key; ?>);
                                        return;
                                    }
                                    <?php
                                    }
                                    ?>

                                    if($this.is('input,textarea,select')){
                                        $this.val(attachment.<?php echo $key; ?>);
                                        return;
                                    }

                                    $this.html(attachment.<?php echo $key; ?>);

                                });
                                <?php
                                    }
                                 }
                                ?>
                                $thumb = jQuery('.<?php echo $this->classNames['thumb']; ?>-'+$name,$context);
                                $thumb.each(function($i){
                                    $thumb_size = jQuery(this).data('thumb_size') || 'thumbnail';
                                    $thumb_data = getClosestSize($thumb_size,attachment);
                                    jQuery(this).attr('src',$thumb_data[0]);
                                    jQuery(this).attr('width',$thumb_data[1]);
                                    jQuery(this).attr('height',$thumb_data[2]);
                                });
                                $context.trigger('mm_media', [attachment, $name]);
                                $context.trigger('mm_media.'+$name, [attachment, $name]);

                            });

                            // Finally, open the modal
                            media_frame_<?php echo $this->name; ?>.open();

                        }

                        // Reset the allowed file extensions
                        wp.Uploader.defaults.filters.mime_types[0].extensions = default_ext;

                    });

                });

                /* ]]> */
            </script><?php
        }
    }

    private function get_image_sizes( $size = '' ) {

        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach( $get_intermediate_image_sizes as $_size ) {

            if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

                $sizes[ $_size ][] = get_option( $_size . '_size_w' );
                $sizes[ $_size ][] = get_option( $_size . '_size_h' );

            } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

                $sizes[ $_size ] = array(
                    $_wp_additional_image_sizes[ $_size ]['width'],
                    $_wp_additional_image_sizes[ $_size ]['height']
                );

            }

        }

        // Get only 1 size if found
        if ( $size ) {

            if( isset( $sizes[ $size ] ) ) {
                return $sizes[ $size ];
            } else {
                return false;
            }

        }

        return $sizes;
    }
}
?>