jQuery(document).ready(function($){
    $('body').removeClass('masthead-fixed');
    $(window).scroll(function(){
        if($(this).scrollTop() < $('#kent-bar').height()){
            $('body').removeClass('masthead-fixed');
        }else{
            $('body').addClass('masthead-fixed');
        }
    });
});
