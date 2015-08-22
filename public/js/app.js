jQuery(document).ready(function($) {
    
    
    var form = $("#extension-search");

    
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
        
    form.on('keyup', function(){
        var searchVal = $(this).find('#search-text').val();
        delay(function(){
            $.get('/search',{'q':searchVal}, function(data) {
                $('.package-list').html(data);  
            });
        }, 500 );
    });
    
    $(form).bind('keypress keydown keyup', function(e){
       if(e.keyCode == 13) { e.preventDefault(); }
    });
    
    $('.browse').popup({
        movePopup: false,
        inline   : false,
        hoverable: true,
        position : 'bottom left',
        delay: {
            show: 300,
            hide: 800
        }
    });
    
});


jQuery(document).ready(function($){
    
    if ($('div[data-build-building]').length) {
        checkStatus();
    } 
});

var statustimer;
var waitCount=0;
var buildCount=0;
function checkStatus() {
    var el = $('div[data-build-building]');
    var id = el.data('build');
    $.ajax({
          url: '/check/'+id
    })
    .done(function(response) {
        switch(response.status) {
            case 'building': 
                el.append('.');
                buildCount ++;
                statustimer = setTimeout(checkStatus, 3000);
                break;
            case 'waiting':
                el.append('.');
                waitCount ++;
                statustimer = setTimeout(checkStatus, 3000);
                break;
            case 'complete':
                var reload = el.data('build-url');
                el.html("RUNNING FUNCTIONAL TESTS."); 
                setTimeout(function(){location.href=reload;}, 300);
                break;
                
        }
        
    });
}



    