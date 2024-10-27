<?php
/**
 * Scripts
 *
 * @package     ADSENSEI
 * @subpackage  Functions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

//add_action( 'wp_enqueue_scripts', 'adsensei_register_styles', 10 );
add_action( 'wp_print_styles', 'adsensei_inline_styles', 9999 );
add_action('amp_post_template_css','adsensei_inline_styles_amp', 11);

add_action( 'admin_enqueue_scripts', 'adsensei_load_admin_scripts', 100 );
add_action( 'admin_enqueue_scripts', 'adsensei_load_plugins_admin_scripts', 100 );
add_action( 'admin_enqueue_scripts', 'adsensei_load_all_admin_scripts', 100 );
add_action( 'admin_enqueue_scripts', 'adsensei_load_admin_fonts', 100 );
add_action( 'admin_print_footer_scripts', 'adsensei_check_ad_blocker' );
add_action( 'wp_enqueue_scripts', 'click_fraud_protection' );
add_action( 'wp_enqueue_scripts', 'tcf_2_integration' );

function tcf_2_integration(){
    if(adsensei_is_amp_endpoint()){
        return;
    }
    global $adsensei_options;
    if(isset($adsensei_options['tcf_2_integration']) && !empty($adsensei_options['tcf_2_integration']) && $adsensei_options['tcf_2_integration']){



        $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';
        wp_enqueue_script( 'adsensei-tcf-2-scripts', ADSENSEI_PLUGIN_URL . 'assets/js/tcf_2_integration' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );

        wp_localize_script( 'adsensei-tcf-2-scripts', 'adsensei_tcf_2',array( ) );

    }

}


function click_fraud_protection(){

    global $adsensei_options,$adsensei_mode;
    if($adsensei_mode == 'new'){
        $allowed_click = isset( $adsensei_options['allowed_click'] )? $adsensei_options['allowed_click'] : 3;
        $ban_duration = isset( $adsensei_options['ban_duration'] )? $adsensei_options['ban_duration'] : 7;
        $click_limit = isset( $adsensei_options['click_limit'] )? absint( $adsensei_options['click_limit'] ) : 3;

           if (isset($adsensei_options['click_fraud_protection']) && !empty($adsensei_options['click_fraud_protection']) && $adsensei_options['click_fraud_protection']  ) {
                $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';
                if ( (function_exists( 'ampforwp_is_amp_endpoint' ) && !ampforwp_is_amp_endpoint()) || function_exists( 'is_amp_endpoint' ) && !is_amp_endpoint() ) {
              wp_enqueue_script( 'adsensei-scripts', ADSENSEI_PLUGIN_URL . 'assets/js/fraud_protection' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );
                }

            }
                wp_localize_script( 'adsensei-scripts', 'adsensei', array(
                    'version'               => ADSENSEI_VERSION,
                    'allowed_click'         => esc_attr($allowed_click),
                    'adsensei_click_limit'     => esc_attr($click_limit),
                    'adsensei_ban_duration'    => esc_attr($ban_duration),
                ) );
    }
}

/**
 *  Determines whether the current admin page is an ADSENSEI admin page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook.
 *
 *  @since 1.9.6
 *  @return bool True if ADSENSEI admin page.
 */
if(!function_exists('adsensei_is_admin_page')){
    function adsensei_is_admin_page() {
        $currentpage = isset($_GET['page']) ? $_GET['page'] : '';
        if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
            return false;
        }
        if ( 'adsensei-settings' == $currentpage ) {
            return true;
        }
    }
}
/**
 * Create ad blocker admin script
 *
 * @return mixed boolean | string
 */
function adsensei_check_ad_blocker() {
    if( !adsensei_is_admin_page() ) {
        return false;
    }
    ?>
    <script type="text/javascript">
        window.onload = function(){
        if (typeof wpadsensei_adblocker_check === 'undefined' || false === wpadsensei_adblocker_check) {
        if (document.getElementById('wpadsensei-adblock-notice')){
        document.getElementById('wpadsensei-adblock-notice').style.display = 'block';
                console.log('adblocker detected');
        }
        }
        }
    </script>
    <?php
}

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function adsensei_load_admin_scripts( $hook ) {

$adsensei_mode = get_option('adsensei-mode');
$screens = get_current_screen();

$currentScreen = '';
if(is_object($screens)){
    $currentScreen = $screens->base;

    if($currentScreen == 'toplevel_page_adsensei-settings'){
        remove_all_actions('admin_notices');
        if($adsensei_mode == 'new'){
             add_action( 'admin_notices', 'adsensei_show_rate_div' );
             add_action( 'admin_notices', 'adsensei_admin_messages_new' );
        }
        wp_enqueue_media();
        //To add page
        if ( ! class_exists( '_WP_Editors', false ) ) {
            require( ABSPATH . WPINC . '/class-wp-editor.php' );
        }
    }

}


          if($adsensei_mode != 'new'){
            add_action( 'admin_notices', 'adsensei_admin_messages' );
          }

    global $current_user,$wp_version, $adsensei;
    $dismissed = explode (',', get_user_meta (wp_get_current_user ()->ID, 'dismissed_wp_pointers', true));
    $do_tour   = !in_array ('wpadsensei_subscribe_pointer', $dismissed);

    if ($do_tour) {
        wp_enqueue_style ('wp-pointer');
        wp_enqueue_script ('wp-pointer');
        $js_dir  = ADSENSEI_PLUGIN_URL . 'assets/js/';
        wp_register_script( 'adsensei-newsletter', $js_dir . 'adsensei-newsletter.js', array('jquery'), ADSENSEI_VERSION, false );
        wp_localize_script( 'adsensei-newsletter', 'adsenseinewsletter', array(
        'current_user_email' => $current_user->user_email,
        'current_user_name' => $current_user->display_name,
        'do_tour'           => $do_tour,
        'path'           => get_site_url()

        ) );
        wp_enqueue_script('adsensei-newsletter');
    }
    $js_dir  = ADSENSEI_PLUGIN_URL . 'assets/js/';
    $css_dir = ADSENSEI_PLUGIN_URL . 'assets/css/';
        // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';
    wp_enqueue_script( 'adsensei-admin-scripts', $js_dir . 'adsensei-admin' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );
    $signupURL = $adsensei->vi->getSettings()->data->signupURL;
         $adsensei_import_classic_ads_popup = false;
        $classic_ads_status = get_option( 'adsensei_import_classic_ads_popup' );
        if($classic_ads_status === false && $adsensei_mode === false){
            update_option('adsensei_import_classic_ads_popup', 'yes');
            $adsensei_import_classic_ads_popup = true;
        }elseif($classic_ads_status == 'yes'){
            $adsensei_import_classic_ads_popup = true;
        }
    wp_localize_script( 'adsensei-admin-scripts', 'adsensei', array(
        'nonce'         => wp_create_nonce( 'adsensei_ajax_nonce' ),
        'error'         => __( "error", 'adsenseib30' ),
        'path'          => get_option( 'siteurl' ),
        'plugin_url'    => ADSENSEI_PLUGIN_URL,
        'vi_revenue'    => !empty( $adsensei->vi->getRevenue()->mtdReport ) ? $adsensei->vi->getRevenue()->mtdReport : '',
        'vi_login_url'  => $adsensei->vi->getLoginURL(),
        'vi_signup_url' => !empty( $signupURL ) ? $signupURL : '',
        'domain'        => $adsensei->vi->getDomain(),
        'email'         => get_option( 'admin_email' ),
        'aid'           => 'WP_Adsensei',
        'adsensei_import_classic_ads_popup' => $adsensei_import_classic_ads_popup,
        'adsensei_get_active_ads' => adsensei_get_active_ads_backup()
    ) );
    if( !apply_filters( 'adsensei_load_admin_scripts', adsensei_is_admin_page(), $hook ) ) {
        return;
    }

    // These have to be global
    wp_enqueue_script( 'adsensei-admin-ads', $js_dir . 'ads.js', array('jquery'), ADSENSEI_VERSION, false );
    wp_enqueue_script( 'adsensei-jscolor', $js_dir . 'jscolor' . $suffix . '.js', array(), ADSENSEI_VERSION, false );
    wp_enqueue_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );
    wp_enqueue_script( 'jquery-form' );

    $vi_dir = ADSENSEI_PLUGIN_URL . 'includes/vendor/vi/public/js/';
    wp_enqueue_script( 'adsensei-vi', $vi_dir . 'vi.js', array(), ADSENSEI_VERSION, false );
    wp_enqueue_style( 'adsensei-admin', $css_dir . 'adsensei-admin' . $suffix . '.css',array(), ADSENSEI_VERSION );
    wp_enqueue_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css',array(), ADSENSEI_VERSION );


}

/**
 * Load Admin Scripts available on plugins page
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function adsensei_load_plugins_admin_scripts( $hook ) {
    if( !apply_filters( 'adsensei_load_plugins_admin_scripts', adsensei_is_plugins_page(), $hook ) ) {
        return;
    }

    $js_dir  = ADSENSEI_PLUGIN_URL . 'assets/js/';
    $css_dir = ADSENSEI_PLUGIN_URL . 'assets/css/';

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';

    wp_enqueue_script( 'adsensei-plugins-admin-scripts', $js_dir . 'adsensei-plugins-admin' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );
    wp_enqueue_style( 'adsensei-plugins-admin', $css_dir . 'adsensei-plugins-admin' . $suffix . '.css', array(),ADSENSEI_VERSION );
}

/**
 * Load Admin Scripts available on all admin pages
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.6.1
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function adsensei_load_all_admin_scripts( $hook ) {


    $css_dir = ADSENSEI_PLUGIN_URL . 'assets/css/';

    wp_enqueue_style( 'adsensei-admin-all', $css_dir . 'adsensei-admin-all.css',array(), ADSENSEI_VERSION );
}

function adsensei_load_admin_fonts( $hook ) {

    $font_url = ADSENSEI_PLUGIN_URL.'admin/assets/js';
    $font_styles= '<style>
    @font-face {
    font-family: "adsensei-icomoon";
    src: url("../fonts/icomoon.eot");
    src: url("../fonts/icomoon.eot?#iefix") format("embedded-opentype"), url("'.$font_url.'/fonts/icomoon.woff") format("woff"), url("../fonts/icomoon.ttf") format("truetype"), url("../fonts/icomoon.svg#icomoon") format("svg");
    font-weight: normal;
    font-style: normal;
}
[class^="adsensei-icon-"]:before,
[class*=" adsensei-icon-"]:after,
[class^="adsensei-icon-"]:after,
[class*=" adsensei-icon-"]:before,
[id^="adsensei-nav-"]:before,
[id*=" adsensei-nav-"]:after,
[id^="adsensei-nav-"]:after,
[id*=" adsensei-nav-"]:before {
    font-family: "adsensei-icomoon";
    speak: none;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
[class^="adsensei-icon-"]:before, [class*=" adsensei-icon-"]:after, [class^="adsensei-icon-"]:after, [class*=" adsensei-icon-"]:before, [id^="adsensei-nav-"]:before, [id*=" adsensei-nav-"]:after, [id^="adsensei-nav-"]:after, [id*=" adsensei-nav-"]:before {
    font-family: \'adsensei-icomoon\';
    speak: none;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

    </style>';

    if( isset($hook) && $hook == "admin.php" ) {
        echo $font_styles ;
    }
}


/**
 * Register CSS Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @global $mashsb_options
 * @return void
 */
//function adsensei_register_styles( $hook ) {
//    global $adsensei_options;
//
//    // Register empty adsensei.css to be able to register adsensei_inline_styles()
//    //$url = ADSENSEI_PLUGIN_URL . 'assets/css/adsensei.css';
//
//    //wp_enqueue_style( 'adsensei-styles', $url, array(), ADSENSEI_VERSION );
//    wp_enqueue_style( 'adsensei-styles', false );
//}

/**
 * Add dynamic CSS to write media queries for removing unwanted ads without the need to use any cache busting method
 * (Cache busting could affect performance and lead to lot of support tickets so lets follow the css approach)
 *
 * @since 1.0
 * @global1 array options
 * @global2 $adsensei_css dynamic build css
 *
 * @return string
 */
function adsensei_inline_styles() {
    global $adsensei_options;

    $css = '';

    if( isset( $adsensei_options['ads'] ) ) {
        foreach ( $adsensei_options['ads'] as $key => $value ) {
            $css .= adsensei_render_media_query( $key, $value );
        }
    }
    $css .="
    .adsensei-location ins.adsbygoogle {
        background: transparent !important;
    }

    .adsensei.adsensei_ad_container { display: grid; grid-template-columns: auto; grid-gap: 10px; padding: 10px; }
    .grid_image{animation: fadeIn 0.5s;-webkit-animation: fadeIn 0.5s;-moz-animation: fadeIn 0.5s;
        -o-animation: fadeIn 0.5s;-ms-animation: fadeIn 0.5s;}
    .adsensei-ad-label { font-size: 12px; text-align: center; color: #333;}
    .adsensei-text-around-ad-label-text_around_left {
        width: 50%;
        float: left;
    }
    .adsensei-text-around-ad-label-text_around_right {
        width: 50%;
        float: right;
    }
    .adsensei-popupad {
        position: fixed;
        top: 0px;
        left:0px;
        width: 100%;
        height: 100em;
        background-color: rgba(0,0,0,0.6);
        z-index: 999;
        max-width: 100em !important;
        margin: 0 auto;
    }
    .adsensei.adsensei_ad_container_ {
        position: fixed;
        top: 40%;
        left: 36%;
    }
    #btn_close{
		background-color: #fff;
		width: 25px;
		height: 25px;
		text-align: center;
		line-height: 22px;
		position: absolute;
		right: -10px;
		top: -10px;
		cursor: pointer;
		transition: all 0.5s ease;
		border-radius: 50%;
	}
    #btn_close_video{
		background-color: #fff;
		width: 25px;
		height: 25px;
		text-align: center;
		line-height: 22px;
		position: absolute;
		right: -10px;
		top: -10px;
		cursor: pointer;
		transition: all 0.5s ease;
		border-radius: 50%;
        z-index:100;
	}
    @media screen and (max-width: 480px) {
        .adsensei.adsensei_ad_container_ {
            left: 10px;
        }
    }

    .adsensei-video {
        position: fixed;
        bottom: 0px;
        z-index: 9999999;
    }
    adsensei_ad_container_video{
        max-width:220px;
    }
    .adsensei_click_impression { display: none;}

    .adsensei-sticky {
        width: 100% !important;
        background-color: hsla(0,0%,100%,.7);
        position: fixed;
        max-width: 100%!important;
        bottom:0;
        margin:0;
        text-align: center;
    }.adsensei-sticky .adsensei-location {
        text-align: center;
    }.adsensei-sticky .wp_adsensei_dfp {
        display: contents;
    }
    a.adsensei-sticky-ad-close {
        background-color: #fff;
        width: 25px;
        height: 25px;
        text-align: center;
        line-height: 22px;
        position: absolute;
        right: 0px;
        top: -15px;
        cursor: pointer;
        transition: all 0.5s ease;
        border-radius: 50%;
    }
    ";
    // Register empty style so we do not need an external css file
    wp_register_style( 'adsensei-styles', false );
    // Enque empty style
    wp_enqueue_style( 'adsensei-styles' );
    // Add inline css to that style
    wp_add_inline_style( 'adsensei-styles', $css );
}
function adsensei_inline_styles_amp() {
    global $adsensei_options;

    $css = '';

    if( isset( $adsensei_options['ads'] ) ) {
        foreach ( $adsensei_options['ads'] as $key => $value ) {
            $css .= adsensei_render_media_query( $key, $value );
        }
    }
    $css .=".adsensei-ad-label { font-size: 12px; text-align: center; color: #333;}";

    if (adsensei_is_amp_endpoint()){
        echo $css;
    }
}


/**
 * Render Media Queries
 *
 * @param string $key
 * @param string $value
 * @return string
 */
function adsensei_render_media_query( $key, $value ) {
    $html = '';

    if( isset( $value['desktop'] ) ) {
        //$html .= '/* Hide on desktop */';
        $html .= '@media only screen and (min-width:1140px){#adsensei-' . $key . ', .adsensei-' . $key . ' {display:none;}}' . "\n";
    }
    if( isset( $value['tablet_landscape'] ) ) {
        //$html .= '/* Hide on tablet landscape */';
        $html .= '@media only screen and (min-width:1024px) and (max-width:1140px) {#adsensei-' . $key . ', .adsensei-' . $key . ' {display:none;}}' . "\n";
    }
    if( isset( $value['tablet_portrait'] ) ) {
        //$html .= '/* Hide on tablet portrait */';
        $html .= '@media only screen and (min-width:768px) and (max-width:1023px){#adsensei-' . $key . ', .adsensei-' . $key . ' {display:none;}}' . "\n";
    }
    if( isset( $value['phone'] ) ) {
        //$html .= '/* Hide on mobile device */';
        $html .= '@media only screen and (max-width:767px){#adsensei-' . $key . ', .adsensei-' . $key . ' {display:none;}}' . "\n";
    }

    return $html;
}

/*
 * Check if debug mode is enabled
 *
 * @since 0.9.0
 * @return bool true if Mashshare debug mode is on
 */

function adsenseiIsDebugMode() {
    global $adsensei_options;

    $debug_mode = (isset( $adsensei_options['debug_mode'] ) && $adsensei_options['debug_mode'] ) ? true : false;
    return $debug_mode;
}

/**
 * Create ad buttons for editor
 * @since 0.9.0
 */
$wpvcomp = ( bool ) (version_compare( get_bloginfo( 'version' ), '3.1', '>=' ));

function adsensei_ads_head_script() {
    global $adsensei_options, $wpvcomp;

    if( isset( $adsensei_options['quicktags']['QckTags'] ) ) {
        ?>
        <script type="text/javascript">
        wpvcomp = <?php echo (($wpvcomp == 1) ? "true " : "false"); ?>;
        edaddID = new Array();
        edaddNm = new Array();
        if (typeof (edButtons) != 'undefined') {         edadd = edButtons.length;
        var dynads = {"all":[
        <?php
        for ( $i = 1; $i <= count( adsensei_get_ads() ) - 1; $i++ ) {
            if( isset( $adsensei_options['ads']['ad' . $i]['code'] ) && $adsensei_options['ads']['ad' . $i]['code'] != '' ) {
                echo('"1",');
            } else {
                echo('"0",');
            };
        }
        ?>
        "0"] };
        for (i = 1; i <=<?php echo count( adsensei_get_ads() ) - 1; ?>; i++) {
        if (dynads.all[ i - 1 ] == "1") {
        edButtons [edButtons.length] = new edButton("ads" + i.toString(), " Ads" + i.toString(), "\n<!--Ads"+i.toString()+"-->\n", "", "", - 1);
        edaddID[edaddID.length] = " ads" + i.toString();
        edaddNm[edaddNm.length] = "Ads" + i.toString();
        }
        }
        <?php if( !isset( $adsensei_options['quicktags']['QckRnds'] ) ) { ?>
            edButtons[edButtons.length] = new edButton("random_ads", " RndAds", "\n<!--RndAds-->\n", "", "", - 1);
            edaddID[edaddID.length] = "random_ads";
            edaddNm[edaddNm.length] = "RndAds";
        <?php } ?>
        edButtons[edButtons.length] = new edButton("no_ads", "NoAds", "\n<!--NoAds-->\n","","",-1);
            edaddID[edaddID.length] = "no_ads";
                            edaddNm[edaddNm.length] = "NoAds";
        };
        (function(){
        if(typeof(edButtons)!='undefined' && typeof(jQuery)!='undefined' && wpvcomp){
            jQuery(document).ready(function(){
                    for(i=0;i<edaddID.length;i++) {
                            jQuery("#ed_toolbar").append('<input type="button" value="' + edaddNm[i] +'" id="' + edaddID[i] +'" class="ed_button" onclick="edInsertTag(edCanvas, ' + (edadd+i) + ');" title="' + edaddNm[i] +'" />');
                    }
            });
        }
        }());
        </script>
        <?php
    }
}

if( $wpvcomp ) {
    add_action( 'admin_print_footer_scripts', 'adsensei_ads_head_script' );
} else {
    add_action( 'admin_head', 'adsensei_ads_head_javascript_script' );
}
