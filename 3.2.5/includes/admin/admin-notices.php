<?php
/**
 * Admin Notices
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Notices
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.9
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;


function adsensei_admin_messages() {
    global $adsensei_options;

    if( !current_user_can( 'update_plugins' ) || adsensei_is_addon_page() ) {
        return;
    }

    $screen = get_current_screen();
    if( $screen->parent_base == 'edit' ) {
        return;
    }


    adsensei_show_vi_api_error();

    echo adsensei_show_vi_notices();

    adsensei_show_ads_txt_notice();

    if( adsensei_is_admin_page() ) {
        echo '<div class="notice notice-error" style="background-color:#ffebeb;display:none;" id="wpadsensei-adblock-notice">' . sprintf( __( '<strong><p>Please disable your browser AdBlocker to resolve problems with WP ADSENSEI ad setup</strong></p>', 'adsenseib30' ), admin_url() . 'admin.php?page=adsensei-settings#adsensei_settingsgeneral_header' ) . '</div>';
    }

    if( isset( $_GET['adsensei-action'] ) && $_GET['adsensei-action'] === 'validate' && adsensei_is_admin_page() && adsensei_is_any_ad_activated() && adsensei_is_post_type_activated() && adsensei_get_active_ads() > 0 ) {
        echo '<div class="notice notice-success">' . sprintf( __( '<strong>No errors detected in WP ADSENSEI settings.</strong> If ads are still not shown read the <a href="%s" target="_blank">troubleshooting guide</a>' ), 'http://wpadsensei.com/docs/adsense-ads-are-not-showing/?utm_source=plugin&utm_campaign=wpadsensei-settings&utm_medium=website&utm_term=toplink' ) . '</div>';
    }
adsensei_show_rate_div();

}
function adsensei_admin_messages_new(){
       if( adsensei_is_admin_page() ) {
        echo '<div class="notice notice-error" style="background-color:#ffebeb;display:none;" id="wpadsensei-adblock-notice">' . sprintf( __( '<strong><p>Please disable your browser AdBlocker to resolve problems with WP ADSENSEI ad setup</strong></p>', 'adsenseib30' ), admin_url() . 'admin.php?page=adsensei-settings#adsensei_settingsgeneral_header' ) . '</div>';
    }
}
function adsensei_show_rate_div(){


    $install_date = get_option( 'adsensei_install_date' );
    $display_date = date( 'Y-m-d h:i:s' );
    $datetime1    = new DateTime( $install_date );
    $datetime2    = new DateTime( $display_date );
    $diff_intrval = round( ($datetime2->format( 'U' ) - $datetime1->format( 'U' )) / (60 * 60 * 24) );

    $rate = get_option( 'adsensei_rating_div', false );
    if( $diff_intrval >= 7 && ($rate === "no" || false === $rate || adsensei_rate_again() ) ) {
        echo '<div class="adsensei_fivestar updated " style="box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);background-color:white;">
        <p>Awesome, you\'ve been using <strong>WP ADSENSEI</strong> for more than 1 week. <br> May i ask you to give it a <strong>5-star rating</strong> on Wordpress? </br>
        This will help to spread its popularity and to make this plugin a better one.
        <br><br>Your help is much appreciated. Thank you very much
        <ul>
            <li><a href="https://wordpress.org/support/plugin/adsenseib30/reviews/?filter=5#new-post" class="thankyou" target="_new" title="Ok, you deserved it" style="font-weight:bold;">Ok, you deserved it</a></li>
            <li><a href="javascript:void(0);" class="adsenseiHideRating" title="I already did" style="font-weight:bold;">I already did</a></li>
            <li><a href="javascript:void(0);" class="adsenseiHideRating" title="No, not good enough" style="font-weight:bold;">No, not good enough</a></li>
            <br>
            <li><a href="javascript:void(0);" class="adsenseiHideRatingWeek" title="No, not good enough" style="font-weight:bold;">I want to rate it later. Ask me again in a week!</a></li>
            <li class="spinner" style="float:none;display:list-item;margin:0px;"></li>
</ul>

    </div>
    <script>
    jQuery( document ).ready(function( $ ) {

    jQuery(\'.adsenseiHideRating\').click(function(){
    jQuery(".spinner").addClass("is-active");
        var data={\'action\':\'adsensei_hide_rating\'}
             jQuery.ajax({

        url: "' . admin_url( 'admin-ajax.php' ) . '",
        type: "post",
        data: data,
        dataType: "json",
        async: !0,
        success: function(e) {
            if (e=="success") {
               jQuery(".spinner").removeClass("is-active");
               jQuery(\'.adsensei_fivestar\').slideUp(\'fast\');

            }
        }
         });
        })

        jQuery(\'.adsenseiHideRatingWeek\').click(function(){
        jQuery(".spinner").addClass("is-active");
        var data={\'action\':\'adsensei_hide_rating_week\'}
             jQuery.ajax({

        url: "' . admin_url( 'admin-ajax.php' ) . '",
        type: "post",
        data: data,
        dataType: "json",
        async: !0,
        success: function(e) {
            if (e=="success") {
               jQuery(".spinner").removeClass("is-active");
               jQuery(\'.adsensei_fivestar\').slideUp(\'fast\');

            }
        }
         });
        })

    });
    </script>
    ';
    }
}

// add_action( 'admin_notices', 'adsensei_admin_messages' );


/* Hide the rating div
 * @return json string
 */

function adsensei_hide_rating_div() {
    update_option( 'adsensei_rating_div', 'yes' );
    delete_option( 'adsensei_date_next_notice' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_adsensei_hide_rating', 'adsensei_hide_rating_div' );

/**
 * Write the timestamp when rating notice will be opened again
 */
function adsensei_hide_rating_notice_week() {
    $nextweek   = time() + (7 * 24 * 60 * 60);
    $human_date = date( 'Y-m-d h:i:s', $nextweek );
    update_option( 'adsensei_date_next_notice', $human_date );
    update_option( 'adsensei_rating_div', 'yes' );
    echo json_encode( array("success") );
    exit;
}

add_action( 'wp_ajax_adsensei_hide_rating_week', 'adsensei_hide_rating_notice_week' );

/**
 * Check if admin notice will open again after one week of closing
 * @return boolean
 */
function adsensei_rate_again() {
    $rate_again_date = get_option( 'adsensei_date_next_notice' );

    if( false === $rate_again_date ) {
        return false;
    }

    $current_date = date( 'Y-m-d h:i:s' );
    $datetime1    = new DateTime( $rate_again_date );
    $datetime2    = new DateTime( $current_date );
    $diff_intrval = round( ($datetime2->format( 'U' ) - $datetime1->format( 'U' )) / (60 * 60 * 24) );

    if( $diff_intrval >= 0 ) {
        return true;
    }
}

/**
 * Show a message when pro or free plugin gets disabled
 *
 * @return void
 * @not used
 */
function adsensei_plugin_deactivated_notice() {
    if( false !== ( $deactivated_notice_id = get_transient( 'adsensei_deactivated_notice_id' ) ) ) {
        if( '1' === $deactivated_notice_id ) {
            $message = __( "WP ADSENSEI and WP ADSENSEI Pro cannot be activated both. We've automatically deactivated WP ADSENSEI.", 'wpstg' );
        } else {
            $message = __( "WP ADSENSEI and WP ADSENSEI Pro cannot be activated both. We've automatically deactivated WP ADSENSEI Pro.", 'wpstg' );
        }
        ?>
        <div class="updated notice is-dismissible" style="border-left: 4px solid #ffba00;">
            <p><?php echo esc_html( $message ); ?></p>
        </div> <?php
        delete_transient( 'adsensei_deactivated_notice_id' );
    }
}

/**
 * Check if any ad is activated and assigned in general settings
 *
 * @global array $adsensei_options
 * @return boolean
 */
function adsensei_is_any_ad_activated() {
    global $adsensei_options;

    // Check if custom positions location_settings is empty or does not exists
    $check = array();
    if( isset( $adsensei_options['location_settings'] ) ) {
        foreach ( $adsensei_options['location_settings'] as $location_array ) {
            if( isset( $location_array['status'] ) ) {
                $check[] = $location_array['status'];
            }
        }
    }

    // ad activated with api (custom position)
    if( count( $check ) > 0 ) {
        return true;
    }
    // check if any other ad is assigned and activated
    if( isset( $adsensei_options['pos1']['BegnAds'] ) ||
            isset( $adsensei_options['pos2']['MiddAds'] ) ||
            isset( $adsensei_options['pos3']['EndiAds'] ) ||
            isset( $adsensei_options['pos4']['MoreAds'] ) ||
            isset( $adsensei_options['pos5']['LapaAds'] ) ||
            isset( $adsensei_options['pos6']['Par1Ads'] ) ||
            isset( $adsensei_options['pos7']['Par2Ads'] ) ||
            isset( $adsensei_options['pos8']['Par3Ads'] ) ||
            isset( $adsensei_options['pos9']['Img1Ads'] )
    ) {
        return true;
    }
    // no ad is activated
    return false;
}

/**
 * Check if any post type is enabled
 *
 * @global array $adsensei_options
 * @return boolean
 */
function adsensei_is_post_type_activated() {
    global $adsensei_options;

    if( empty( $adsensei_options['post_types'] ) ) {
        return false;
    }
    return true;
}

/**
 * Check if ad codes are populated
 *
 * @global array $adsensei_options
 * @return booleantrue if ads are empty
 */
function adsensei_ads_empty() {
    global $adsensei_options;

    $check = array();

    for ( $i = 1; $i <= 10; $i++ ) {
        if( !empty( $adsensei_options['ads']['ad' . $i]['code'] ) ) {
            $check[] = 'true';
        }
    }
    if( count( $check ) === 0 ) {
        return true;
    }
    return false;
}

/**
 * Return VI admin notice
 * @return string
 */
function adsensei_get_vi_notice() {
    global $adsensei;

    if( false !== get_option( 'adsensei_close_vi_welcome_notice' ) || !adsensei_is_admin_page() ) {
        return false;
    }

    $mail   = get_option( 'admin_email' );
    $domain = $adsensei->vi->getDomain();


    $white = '<div class="adsensei-banner-wrapper">
  <section class="adsensei-banner-content">
    <div class="adsensei-banner-columns">
      <main class="adsensei-banner-main"><p>' .
            sprintf(
                    __( 'This update features vi stories from <strong>video intelligence</strong>. This video player will supply you with both video
content and video advertising.<br>
To begin earning, visit the WP ADSENSEI plugin page, <a href="%1$s" target="_blank" class="adsensei-vi-welcome-white" style="text-decoration: none;border-bottom:3px solid yellow;font-weight: bold;color:black;">sign up</a> to vi stories and <a href="%2$s" class="adsensei-vi-welcome-white" style="text-decoration: none;border-bottom:3px solid yellow;font-weight: bold;color:black;">place the ad live now!</a> Read the <a href="%3$s" target="_blank">FAQ</a>.
<p style="font-size:10px;">By clicking <strong>sign up</strong> you agree to send your current domain, email and affiliate ID to video intelligence & WP ADSENSEI</p>', 'quick-adsense-reloaed' ), 'https://www.vi.ai/publisher-registration/?aid=WP_Adsensei&domain=' . $domain . '&email=' . $mail . '&utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=white', admin_url() . 'admin.php?page=adsensei-settings#adsensei_settingsvi_header', 'https://www.vi.ai/publisherfaq/?aid=WP_Adsensei&utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=white'
            )
            . '</p></main>
      <!--<aside class="adsensei-banner-sidebar-first"><p><a href="https://www.vi.ai/?utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=white"><img src="' . ADSENSEI_PLUGIN_URL . 'assets/images/vi_adsensei_logo.png" width="168" height="72"></a></p></aside>//-->
      <aside class="adsensei-banner-sidebar-second"><p style="text-align:center;"><a href="https://www.vi.ai/?aid=WP_Adsensei&utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=white"><img src="' . ADSENSEI_PLUGIN_URL . 'assets/images/vi-logo-white.png" width="168" height="72"></a></p></aside>
    </div>
          <aside class="adsensei-banner-close"><div style="margin-top:5px;"><a href="' . admin_url() . 'admin.php?page=adsensei-settings&adsensei-action=close_vi_welcome_notice" class="adsensei-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a></div></aside>
  </section>
</div>';


    $black = '<div class="adsensei-banner-wrapper" style="background-color:black;">
  <section class="adsensei-banner-content">
    <div class="adsensei-banner-columns">
      <main class="adsensei-banner-main" style="color:white;"><p>' .
            sprintf(
                    __( 'This update features vi stories from <strong>video intelligence</strong>. This video player will supply you with both video
content and video advertising.<br>
To begin earning, visit the WP ADSENSEI plugin page, <a href="%1$s" target="_blank" class="adsensei-vi-welcome-black" style="text-decoration: none;border-bottom:3px solid yellow;font-weight: bold;color:white;">sign up</a> to vi stories and <a href="%2$s" class="adsensei-vi-welcome-black" style="text-decoration: none;border-bottom:3px solid yellow;font-weight: bold;color:white;">place the ad live now!</a> Read the <a href="%3$s" target="_blank">FAQ</a>.
<p style="font-size:10px;">By clicking <strong>sign up</strong> you agree to send your current domain, email and affiliate ID to video intelligence & WP ADSENSEI</p>', 'adsenseib30' ), 'https://www.vi.ai/publisher-registration/?aid=WP_Adsensei&domain=' . $domain . '&email=' . $mail . '&utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=black', admin_url() . 'admin.php?page=adsensei-settings#adsensei_settingsvi_header', 'https://www.vi.ai/publisherfaq/?aid=WP_Adsensei&utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=black'
            )
            . '</p></main>
      <!--<aside class="adsensei-banner-sidebar-first"><p><a href="https://www.vi.ai/?utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=black"><img src="' . ADSENSEI_PLUGIN_URL . 'assets/images/vi_adsensei_logo.png" width="168" height="72"></a></p></aside>//-->
      <aside class="adsensei-banner-sidebar-second"><p style="text-align:center;"><a href="https://www.vi.ai/?aid=WP_Adsensei&utm_source=Wordpress&utm_medium=wp%20adsensei&utm_campaign=black"><img src="' . ADSENSEI_PLUGIN_URL . 'assets/images/vi-logo-black.png" width="168" height="72"></a></p></aside>
    </div>
          <aside class="adsensei-banner-close"><div style="margin-top:5px;"><a href="' . admin_url() . 'admin.php?page=adsensei-settings&adsensei-action=close_vi_welcome_notice" class="adsensei-notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a></div></aside>
  </section>
</div>';

    $variant = get_option( 'adsensei_vi_variant' );

    switch ( $variant ) {
        case 'a':
            return $white;
            break;
        case 'b':
            return $black;
            break;
        default:
            return $white;
            break;
    }
}

/**
 * Check if vi admin notice should be opened again again one week after closing
 * @return boolean
 */
function adsensei_show_vi_notice_again() {

    $show_again_date = get_option( 'adsensei_show_vi_notice_later' );

    if( false === $show_again_date ) {
        return false;
    }

    $current_date = date( 'Y-m-d h:i:s' );
    $datetime1    = new DateTime( $show_again_date );
    $datetime2    = new DateTime( $current_date );
    $diff_intrval = round( ($datetime2->format( 'U' ) - $datetime1->format( 'U' )) / (60 * 60 * 24) );

    if( $diff_intrval >= 0 ) {
        return true;
    }
}

/**
 * Show all vi notices
 */
function adsensei_show_vi_notices() {
    global $adsensei, $adsensei_options;

    if( !adsensei_is_admin_page() ) {
        return false;
    }


    // adsense ads.txt content
    $adsense             = new wpadsensei\adsense( $adsensei_options );
    $adsensePublisherIds = $adsense->getPublisherIds();

    $adsenseAdsTxtText = '';
    if( !empty( $adsensePublisherIds ) ) {
        foreach ( $adsensePublisherIds as $adsensePublisherId ) {
            $adsenseAdsTxtText .= "google.com, " . str_replace( 'ca-', '', $adsensePublisherId ) . ", DIRECT, f08c47fec0942fa0\r\n";
        }
    }

    // vi ads.txt content
    $viAdsTxtText = '';
    if( $adsensei->vi->getPublisherId() ) {
        $viAdsTxtText = $adsensei->vi->getAdsTxtContent();
    }

    // Show ads.txt warning if logged into vi and ads.txt option is disabled
    if( get_transient( 'adsensei_vi_ads_txt_disabled' ) && get_option( 'adsensei_vi_token' ) ) {
        // ads.txt content
        $notice['message'] = sprintf( '<p><strong>ADS.TXT couldn\'t be updated automatically.</strong><br><br>You need the ads.txt to display vi video ads. <br>If you want WP ADSENSEI to create an ads.txt automatically you can enable the ads.txt option at <a href="%1$s">General & Position</a>. Alternatively you can also enter the following line manually into <strong>' . get_site_url() . '/ads.txt</strong>:'
                . "<p>"
                . "<pre>" . $viAdsTxtText . "<br>"
                . $adsenseAdsTxtText
                . "</pre></p>"
                . 'If the file does not exist you need to create it first. <a href="%2$s" target="_blank">Learn More</a></p>'
                , admin_url() . 'admin.php?page=adsensei-settings#adsensei_settingsgeneral_header'
                , 'https://adsplugin.net/make-more-revenue-by-using-an-ads-txt-in-your-website-root-domain/'
        );
        $notice['type']    = 'update-nag';
        $adsTxtDisabled    = new wpadsensei\template( '/includes/vendor/vi/views/notices', $notice );
        echo $adsTxtDisabled->render();
        return false;
    }

    // show ad.txt update notice
    if( get_transient( 'adsensei_vi_ads_txt_notice' ) ) {
        $notice['message'] = '<strong>ADS.TXT has been added</strong><br><br><strong>WP ADSENSEI</strong> has updated your ads.txt '
                . 'file with lines that declare video inteligence as a legitmate seller of your inventory and enables you to make more money through video inteligence. <a href="https://www.vi.ai/publisher-video-monetization/?utm_source=WordPress&utm_medium=Plugin%20blurb&utm_campaign=wpadsensei" target="blank" rel="external nofollow">FAQ</a>';
        $notice['type']    = 'update-nag';
        $notice['transient']    = 'adsensei_vi_ads_txt_notice';
        $adsUpdated        = new wpadsensei\template( '/includes/vendor/vi/views/notices', $notice );
        // echo $adsUpdated->render();
    }

    // show ad.txt update notice
    if( get_transient( 'adsensei_vi_ads_txt_error' ) ) {


        // ads.txt content
        $notice['message'] = "<p><strong>ADS.TXT couldn't be added</strong><br><br>Important note: WP ADSENSEI hasn't been able to update your ads.txt file automatically. Please make sure to enter the following line manually into <br><strong>" . get_home_path() . "ads.txt</strong>:"
                . "<p>"
                . "<pre>vi.ai " . $adsensei->vi->getPublisherId() . " DIRECT # 41b5eef6<br>"
                . $adsenseAdsTxtText
                . "</pre></p>"
                . "Only by doing so you are able to make more money through video inteligence.</p>";
        $notice['type']    = 'error';

        // render blurb
        $adsTxtError = new wpadsensei\template( '/includes/vendor/vi/views/notices', $notice );
        echo $adsTxtError->render();
    }
}

/**
 * Show a ads.txt notices if WP ADSENSEI has permission to update or create an ads.txt
 */
function adsensei_show_ads_txt_notice() {
    global $adsensei, $adsensei_options;

    if( !adsensei_is_admin_page() )
        return false;


    // show ads.txt error notice
    if( get_transient( 'close_ads_txt_error' ) && isset( $adsensei_options['adsTxtEnabled'] ) ) {

        // Check if adsense is used and add the adsense publisherId to ads.txt blurb as well
        $adsense             = new wpadsensei\adsense( $adsensei_options );
        $adsensePublisherIds = $adsense->getPublisherIds();


        $adsenseAdsTxtText = '';
        if( !empty( $adsensePublisherIds ) ) {
            foreach ( $adsensePublisherIds as $adsensePublisherId ) {
                $adsenseAdsTxtText .= "google.com, " . str_replace( 'ca-', '', $adsensePublisherId ) . ", DIRECT, f08c47fec0942fa0\n\r";
            }
        }

        $viAdsTxtText = '';
        if( $adsensei->vi->getPublisherId() ) {
            $viAdsTxtText = $adsensei->vi->getAdsTxtContent();
        }

        // ads.txt content
        $notice['message'] = "<p><strong>ADS.TXT couldn't be updated automatically</strong><br><br>Important note: WP ADSENSEI hasn't been able to update your ads.txt file automatically. Please make sure to enter the following line manually into <strong>" . get_home_path() . "ads.txt</strong>:"
                . "<p>"
                . "<pre>" . $viAdsTxtText . "<br>"
                . $adsenseAdsTxtText
                . "</pre></p>"
                . "Only by doing so AdSense ads are shown on your site.</p>";
        $notice['type']    = 'error';
        $notice['action']  = 'adsensei_ads_txt_error';

        // render blurb
        $adsTxtError = new wpadsensei\template( '/includes/admin/views/notices', $notice );
        echo $adsTxtError->render();
    }
}

/**
 * Show api errors
 */
function adsensei_show_vi_api_error() {
    if( !adsensei_is_admin_page() ) {
        return false;
    }

    if( false !== get_option( 'adsensei_vi_api_error' ) ) {
        $notice['message'] = 'WP ADSENSEI - Can not retrive ad settings from vi API. Error: ' . get_option( 'adsensei_vi_api_error' );
        $notice['type']    = 'error';
        $notice['action']  = '';
        // render blurb
        $blurb             = new wpadsensei\template( '/includes/admin/views/notices', $notice );
        echo $blurb->render();
    }
}

/**
 * Store the transient for 30 days
 */
function adsensei_hide_license_expired_notice() {
    set_transient( 'adsensei_notice_lic_expired', 'hide', 60 * 60 * 24 * 30 );
}

add_action( 'adsensei_hide_license_expired_notice', 'adsensei_hide_license_expired_notice' );

/**
 * Return update notice for Google Auto Ads
 * @since 3.5.3.0
 */
function adsensei_show_update_auto_ads() {


    $message = sprintf( __( '<h2 style="color:white;">WP ADSENSEI & Google Auto Ads</h2>'
                    . 'WP ADSENSEI Pro adds support for Google Auto Ads<br><br> Get the Pro plugin from <a href="https://adsplugin.net/?utm_source=wp-admin&utm_medium=autoads-notice&utm_campaign=autoads-notice" target="_blank" style="color:#87c131;font-weight:500;">wpadsensei.com</a>'
                    , 'mashsb' ), admin_url() . 'admin.php?page=adsensei-settings'
    );

    if( get_option( 'adsensei_show_notice_auto_ads' ) === 'no' ) {
        return false;
    }

    // admin notice after updating wp adsensei
    echo '<div class="adsensei-notice-gdpr update-nag" style="background-color: black;color: #87c131;padding: 20px;margin-top: 20px;border: 3px solid #87c131;display:block;">' . $message .
    '<p><a href="' . admin_url() . 'admin.php?page=adsensei-settings&adsensei-action=hide_auto_ads_notice" class="adsensei_hide_gdpr" title="I got it" style="text-decoration:none;color:white;">- I Understand! Do Not Show This Hint Again -</a></a>' .
    '</div>';
}

/**
 * Hide GDPR notice
 *
 * @global array $mashsb_options
 */
function mashsb_hide_auto_ads_notice() {
    global $adsensei_options;
    // Get all settings
    update_option( 'adsensei_show_notice_auto_ads', 'no' );
}

add_action( 'adsensei_hide_auto_ads_notice', 'mashsb_hide_auto_ads_notice' );
