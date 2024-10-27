var strict;

jQuery(document).ready(function ($) {

    /**
     * DEACTIVATION FEEDBACK FORM
     */
    // show overlay when clicked on "deactivate"
    adsensei_deactivate_link = $('.wp-admin.plugins-php tr[data-slug="adsenseib30"] .row-actions .deactivate a');
    adsensei_deactivate_link_url = adsensei_deactivate_link.attr('href');

    adsensei_deactivate_link.click(function (e) {
        e.preventDefault();

        // only show feedback form once per day
        var c_value = adsensei_admin_get_cookie("adsensei_hide_deactivate_feedback");

        if (c_value === undefined) {
            $('#adsenseib30-feedback-overlay').show();
        } else {
            // click on the link
            window.location.href = adsensei_deactivate_link_url;
        }
    });
    // show text fields
    $('#adsenseib30-feedback-content input[type="radio"]').click(function () {
        // show text field if there is one
        $(this).parents('li').next('li').children('input[type="text"], textarea').show();
    });
    // send form or close it
    $('#adsenseib30-feedback-content .button').click(function (e) {
        e.preventDefault();
        // set cookie for 1 day
        var exdate = new Date();
        exdate.setSeconds(exdate.getSeconds() + 86400);
        document.cookie = "adsensei_hide_deactivate_feedback=1; expires=" + exdate.toUTCString() + "; path=/";

        $('#adsenseib30-feedback-overlay').hide();
        if ('adsenseib30-feedback-submit' === this.id) {
            // Send form data
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'adsensei_send_feedback',
                    data: $('#adsenseib30-feedback-content form').serialize()
                },
                complete: function (MLHttpRequest, textStatus, errorThrown) {
                    // deactivate the plugin and close the popup
                    $('#adsenseib30-feedback-overlay').remove();
                    window.location.href = adsensei_deactivate_link_url;

                }
            });
        } else {
            $('#adsenseib30-feedback-overlay').remove();
            window.location.href = adsensei_deactivate_link_url;
        }
    });
    // close form without doing anything
    $('.adsenseib30-feedback-not-deactivate').click(function (e) {
        $('#adsenseib30-feedback-overlay').hide();
    });
    
    function adsensei_admin_get_cookie (name) {
	var i, x, y, adsensei_cookies = document.cookie.split( ";" );
	for (i = 0; i < adsensei_cookies.length; i++)
	{
		x = adsensei_cookies[i].substr( 0, adsensei_cookies[i].indexOf( "=" ) );
		y = adsensei_cookies[i].substr( adsensei_cookies[i].indexOf( "=" ) + 1 );
		x = x.replace( /^\s+|\s+$/g, "" );
		if (x === name)
		{
			return unescape( y );
		}
	}
}

}); // document ready