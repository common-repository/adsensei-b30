;
(function($) {
    var adsensei_ad_click = 'adsensei_ad_clicks';
    var adsensei_click_fraud_protection = function() {
        this.$elements = {};
        this.currentIFrame = false;
        this.focusLost = false;
        this.init();
    }

    adsensei_click_fraud_protection.prototype = {
        constructor: adsensei_click_fraud_protection,
        init: function() {
            var that = this;
            $(document).on('click', '.adsensei-location', function() {
                var currentid = $(this).attr('id');
                currentid = currentid.replace(/[^0-9]/g, '');
                that.onClick(parseInt(currentid));
            });
            $(window).on('blur', function() {
                if (false !== that.currentIFrame) {
                    that.onClick(that.currentIFrame);
                    that.currentIFrame = false;
                    that.focusLost = true;
                }
            });
        },
        onClick: function(ID) {
            var cookie_val = {};
            var C = false,
                C_vc = false;
            if ($('#adsensei-ad' + ID)) {
                var cookie = adsenseigetCookie(adsensei_ad_click);
                if (cookie) {
                    try {
                        C_vc = JSON.parse( cookie );
                    } catch( Ex ) {
                        C_vc= false;
                    }
                }
            }
            var d = new Date();
            var now = new Date();
            var expires =  d.toUTCString();
            cookie_val['exp'] = expires;

            if (C_vc) {
                var old_date = new Date(C_vc['exp']);
                var click_limit_time = old_date.setHours(old_date.getHours() +adsensei.adsensei_click_limit );
                if(click_limit_time < now ){
                    cookie_val['count'] = 0;
                }else{
                    cookie_val['count'] = C_vc['count']+1;
                }
                cookie_val['exp'] = expires;
                adsenseisetCookie(adsensei_ad_click, JSON.stringify( cookie_val, 'false', false ), adsensei.adsensei_ban_duration);
            } else {
                cookie_val['count'] = 0;
                adsenseisetCookie(adsensei_ad_click, JSON.stringify( cookie_val, 'false', false ), adsensei.adsensei_ban_duration);
            }
        }
    }
    $(document).on('mouseenter', '.adsensei-location', function() {
        var ID = $(this).attr('id');
        var currentid = ID.replace(/[^0-9]/g, '');
        adsensei_click_fraud.currentIFrame = currentid;
    }).on('mouseleave', '.adsensei-location', function() {
        adsensei_click_fraud.currentIFrame = false;
        if (adsensei_click_fraud.focusLost) {
            adsensei_click_fraud.focusLost = false;
            $(window).focus();
        }
    });
    $(function() {

        window.adsensei_click_fraud = new adsensei_click_fraud_protection();

    });

})(window.jQuery);


if (typeof adsenseigetCookie !== "function"){

    function adsenseigetCookie(cname) {
        var name = cname + '=';
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i].trim();
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return false;
    }
}
if (typeof adsenseisetCookie !== "function") {

    function adsenseisetCookie(cname, cvalue, exdays, path) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
}