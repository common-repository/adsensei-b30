jQuery(document).ready(function(){
    jQuery(document).on('click', '#adsb30 .notice-dismiss', function( event ) {
        data = {
            action : 'dismissible_admin_notice',
        };

    jQuery.post(ajaxurl, data, function (response) {

        });
    });
});
