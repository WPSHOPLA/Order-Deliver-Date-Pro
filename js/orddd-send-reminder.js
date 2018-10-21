jQuery( document ).ready( function ($) {
    $( '#orddd_save_message' ).on( "click", function() {
        $( "#ajax_img" ).show();
        var data = {
            subject : $( '#orddd_reminder_subject' ).val(),
            message :tinyMCE.get( 'orddd_reminder_message' ).getContent(),
            action: 'orddd_save_reminder_message'
        };

        $.post( orddd_reminder_params.ajax_url, data, function(response) {
            if( response !== false ) {
                $( "#ajax_img" ).hide();
                $( '.wrap form' ).after( '<div class="notice notice-success"><p>Message draft saved</p></div>' );
                $( '.notice-success' ).fadeOut( 5000 );
            }
        });
    });
});