<?php

wp_register_style('kent-blogs-footer',plugins_url( 'kent-blogs-footer.css' , __FILE__ ));

function kentblogs_add_footer(){
?>
<div id="kent-blogs-footer">The views expressed in this blog are not necessarily those of the University of Kent. View <a href="http://www.kent.ac.uk/is/regulations/it/index.html?tab=blogs-conditions-of-use" target="_blank">Conditions of use</a> and <a href="http://www.kent.ac.uk/itservices/email/blogs.html" target="_blank">Guidelines</a>.</div>
    <script type="text/javascript">
        styles = [];

        bodystyle = window.getComputedStyle(document.body);
        if(bodystyle.marginLeft != "0px"){
            styles.push('margin-left: -' + bodystyle.marginLeft +';');
        }
        if(bodystyle.marginRight != "0px"){
            styles.push('margin-right: -' + bodystyle.marginRight +';');
        }
        if(bodystyle.paddingLeft != "0px"){
            styles.push('margin-left: -' + bodystyle.paddingLeft +';');
        }
        if(bodystyle.paddingRight != "0px"){
            styles.push('margin-right: -' + bodystyle.paddingRight +';');
        }
        style ='';
        if(styles.length>0){
            style = styles.join(' ');
        }
        document.getElementById('kent-blogs-footer').style = style;
    </script>
<?php
}

add_filter('wp_footer','kentblogs_add_footer');

function kentblogs_footer_scripts(){
    wp_enqueue_style('kent-blogs-footer');
}
add_action( 'wp_enqueue_scripts', 'kentblogs_footer_scripts');