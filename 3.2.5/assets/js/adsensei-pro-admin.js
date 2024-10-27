var strict;

jQuery(document).ready(function ($) {

    // path to admin-ajax.php
    ajaxurl = ("undefined" !== typeof adsensei.ajaxurl) ? adsensei.ajaxurl : ajaxurl;

    urlSpinner = ajaxurl.replace("/admin-ajax.php", '') + "/images/spinner";

    if (2 < window.devicePixelRatio)
    {
        urlSpinner += "-2x";
    }

    urlSpinner += ".gif";
    

    $('#adsensei_select_tags').ajaxChosen({
        dataType: 'json',
        type: 'POST',
        url: ajaxurl,
        data: {'action': 'adsensei_get_tags', 'keyboard': 'tag'}, //Or can be [{'name':'keyboard', 'value':'cat'}]. chose your favorite, it handles both.
        success: function (data, textStatus, jqXHR) {
            console.log('success');
        },
        error: function () {
            console.log('error');
        }
    }, {
        useAjax: true,
        loadingImg: urlSpinner,
        minLength: 2
    });


    /**
     * Add new ad
     */
    var newAddCount = 1;
    jQuery('#adsensei-add-new-ad').click(function (e) {
        e.preventDefault();
        var data = {
            action: 'adsensei_ajax_add_ads',
            nonce: adsensei.nonce,
            count: newAddCount++

        };
        $.post(ajaxurl, data, function (resp, status, xhr) {
            //console.log(resp);
            jQuery('#adsensei_settingsadsense_header table tbody tr').last().before(resp);
            //console.log('success:' + resp + status + xhr);

        }).fail(function (xhr) { // Will be executed when $.post() fails
            //console.log('error: ' + xhr.statusText);
        });
    });
    /**
     * Remove ad
     */

    function adsensei_delete_new_design_ad(ad_number){

        var data = {
            action: 'adsensei_delete_new_design_ad',
            nonce: adsensei.nonce,
            ad_number: ad_number
        };

        jQuery.post(ajaxurl, data, function (resp, status, xhr) {
                  
        }).fail(function (xhr) { // Will be executed when $.post() fails            
            console.log('error: ' + xhr.statusText);
        });
    
    } 

    jQuery('.adsensei-form-table').on('click', '.adsensei-delete-ad', function (e) {
        e.preventDefault();

        var parentContainerID = $(this).parents('.adsensei-ad-toggle-container').attr('id');
        var ad_number         = parentContainerID.replace("adsensei-togglead", '');
        if(ad_number){
            adsensei_delete_new_design_ad(ad_number);
        }
        // Remove Header
        $('#' + parentContainerID).remove();
        // Remove content
        $(".adsensei-ad-toggle-header[data-box-id=" + parentContainerID + "]").hide('slow', function () {
            this.remove()
        })
    });


    /**
     * Ajax Requests
     * @param {Object} data
     * @param {Function} callback
     * @param {String} dataType
     * @param {Boolean} showErrors
     */
    var ajax = function (data, callback, dataType, showErrors)
    {


        if ("undefined" === typeof (dataType))
        {
            dataType = "json";
        }

        if (false !== showErrors)
        {
            showErrors = true;
        }

        $.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: dataType,
            cache: false,
            data: data,
            error: function (xhr, textStatus, errorThrown) {
                console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus);
                console.log(textStatus);

                if (false === showErrors)
                {
                    return false;
                }

                showError(
                        "Fatal Unknown Error." +
                        "Please try again. If this does not help, " +
                        "<a href='https://adsplugin.net/help/' target='_blank'>open a support ticket</a> "
                        );
            },
            success: function (data) {
                if ("function" === typeof (callback))
                {
                    callback(data);
                }
            },
            statusCode: {
                404: function () {
                    showError("Something went wrong; can't find ajax request URL!");
                },
                500: function () {
                    showError("Something went wrong; internal server error while processing the request!");
                }
            }
        });
    };

    /**
     * Show Error Popup
     * @param string message
     * @returns obj
     */
    var showError = function (message) {
        alert(message);
    }

});