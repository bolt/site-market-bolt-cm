jQuery(document).ready(function($) {
    $('.popup').popup( {position    : 'bottom left'});
    $('.ui.toggle.button').popup();
    $('.ui.accordion').accordion();
    $('.scrollbar-inner').scrollbar();

    var client = new ZeroClipboard( document.getElementById("copy-button") );
    client.on( "ready", function( readyEvent ) {

    });

    $.ajax({
	  	url: "/view/" + packageID + "/releases",
	  	context: $('.releases-wrapper')
	}).done(function(response) {
	  	$(this).html(response);
	  	$('.ui.accordion').accordion();
	});
});
