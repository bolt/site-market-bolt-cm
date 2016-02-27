jQuery(document).ready(function($) {




    $(".v3status").each(function() {
        var el = $(this);
        var name = el.data('name')
        $.ajax({
                url: '/info.json?package='+name+'&bolt=3.1'
            })
            .done(function(response) {
                if (response.version.length > 0) {
                    var stability = 'dev';
                    $.each(response.version, function(v){
                        if (stability == 'dev' && v.stability == 'stable') {
                            stability = 'stable';
                        }
                    });
                    if (stability == 'dev') {
                        status = 'orange';
                        icon = 'checkmark';
                        message = 'Bolt 3.0 Ready (dev)'
                    } else {
                        status = 'green';
                        icon = 'checkmark';
                        message = 'Bolt 3.0 Ready'
                    }

                } else {
                    status = 'red';
                    icon = 'remove';
                    message = 'Not Ready';
                }
                el.html('<div class="buildstatus ui icon label '+ status +'"><i class="icon '+ icon +'"></i> '+ message +' </div>')
            });
    });


});





