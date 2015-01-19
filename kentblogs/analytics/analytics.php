<?php
/*
* Analytics
*/
function kentblogs_add_analytics(){


echo "<script type='text/javascript'>

    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-21420000-13', 'auto');
    ga('require', 'displayfeatures');
    ga('require', 'linkid', 'linkid.js');
    ga('send', 'pageview');

</script>";

}

// production environment only
if(defined('WP_ENV') && WP_ENV =='production') {
add_action('wp_footer', 'kentblogs_add_analytics');
}