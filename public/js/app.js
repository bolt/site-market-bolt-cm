jQuery(document).ready(function($) {
    
    
    var form = $("#extension-search");

    
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
        
    form.on('keyup', function(e){
        var searchVal = $(this).find('#search-text').val();
        delay(function(){
            $.get('/search',{'q':searchVal}, function(data) {
                $('.package-list').html(data);  
            });
        }, 500 );
        e.preventDefault();
    });
});