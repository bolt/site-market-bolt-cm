jQuery(document).ready(function($) {




    $(".v3status").each(function() {
        var el = $(this);
        var name = el.data('name')
        $.ajax({
                url: '/info.json?package='+name+'&bolt=3.1'
            })
            .done(function(response) {
                if (response.version.length > 0) {
                    status = 'green';
                    icon = 'checkmark';
                    message = 'Bolt 3.0 Ready'
                } else {
                    status = 'red';
                    icon = 'remove';
                    message = 'Not Ready';
                }
                el.html('<div class="buildstatus ui icon label '+ status +'"><i class="icon '+ icon +'"></i> '+ message +' </div>')
            });
    });


});





