<?php
/**
* Template Functions
*
* @package     ADSENSEI
* @subpackage  Functions/Templates
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       0.9.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// we need to hook into the_content on lower than default priority (that's why we use separate hook)
add_filter('the_content', 'adsensei_post_settings_to_quicktags', adsensei_get_load_priority());
add_filter('the_content', 'adsensei_process_content', adsensei_get_load_priority());
add_filter('the_content', 'adsenseiv2_process_content', adsensei_get_load_priority());
add_filter('rest_prepare_post', 'adsensei_classic_to_gutenberg', 10, 1);
add_filter('the_content', 'adsensei_change_adsbygoogle_to_amp',11);
add_action('wp_head',  'adsensei_common_head_code');
add_action( 'the_post', 'adsensei_in_between_loop' , 20, 2 );
add_action( 'init', 'adsensei_background_ad' );
add_action('amp_post_template_head','adsensei_adsense_auto_ads_amp_script',1);
add_action('amp_post_template_footer','adsensei_adsense_auto_ads_amp_tag');
add_action( 'plugins_loaded', 'adsensei_plugins_loaded_bbpress', 20 );

add_action( 'init', 'remove_ads_for_wp_shortcodes',999 );
function adsensei_get_complete_html( $content_buffer ) {
  $content_buffer = apply_filters('wp_adsensei_content_html_last_filter', $content_buffer);
  return  $content_buffer;
}
add_action('wp', function(){ ob_start('adsensei_get_complete_html'); }, 999);
function adsensei_plugins_loaded_bbpress(){
  global $adsensei_mode;
  if($adsensei_mode != 'new' || !class_exists( 'bbPress' )){
    return ;
  }
  add_action( 'bbp_template_after_replies_loop', 'adsensei_bbp_template_after_Ads' );
  add_action( 'bbp_template_before_replies_loop', 'adsensei_bbp_template_before_Ads' );
  add_action( 'bbp_theme_after_reply_content', 'adsensei_bbp_template_after_replies_loop' );
  add_action( 'bbp_theme_before_reply_content', 'adsensei_bbp_template_before_replies_loop' );
}
add_filter('wp_adsensei_content_html_last_filter','wpadsensei_content_modifier');
function wpadsensei_content_modifier( $content_buffer ){
  $data =    adsensei_load_ads_common('newspaper_theme',$content_buffer);
  return $data;
  if(empty($data)){
    return $content_buffer;
  }
  return $data;
}

function adsensei_bbp_template_after_Ads(){
  adsensei_load_ads_common('bbpress_after_ad');
}

function adsensei_bbp_template_before_Ads(){
  adsensei_load_ads_common('bbpress_before_ad');
}
function adsensei_bbp_template_after_replies_loop(){
  adsensei_load_ads_common('bbpress_after_reply');
}

function adsensei_bbp_template_before_replies_loop(){
  adsensei_load_ads_common('bbpress_before_reply');
}

// Global $adsensei_ads variable to reduce db calls #631
require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
$api_service = new ADSENSEI_Ad_Setup_Api_Service();
$adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');


function adsensei_load_ads_common($user_position,$html=''){
  if(!isset($adsensei_ads)|| empty($adsensei_ads))
  {
    require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
    $api_service = new ADSENSEI_Ad_Setup_Api_Service();
    $adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');
  }

  if(isset($adsensei_ads['posts_data'])){
    foreach($adsensei_ads['posts_data'] as $key => $value){
      $ads =$value['post_meta'];
      if($value['post']['post_status']== 'draft'){
        continue;
      }

      if(!isset($ads['position'])){
        continue;
      }
      if(isset($ads['ad_id']))
      $post_status = get_post_status($ads['ad_id']);
      else
      $post_status =  'publish';
      if(isset($ads['random_ads_list']))
      $ads['random_ads_list'] = unserialize($ads['random_ads_list']);
      if(isset($ads['visibility_include']))
      $ads['visibility_include'] = unserialize($ads['visibility_include']);
      if(isset($ads['visibility_exclude']))
      $ads['visibility_exclude'] = unserialize($ads['visibility_exclude']);

      if(isset($ads['targeting_include']))
      $ads['targeting_include'] = unserialize($ads['targeting_include']);

      if(isset($ads['targeting_exclude']))
      $ads['targeting_exclude'] = unserialize($ads['targeting_exclude']);
      $is_on         = adsensei_is_visibility_on($ads);
      $is_visitor_on = adsensei_is_visitor_on($ads);
      if($is_on && $is_visitor_on && $post_status == 'publish'){
        if(($ads['position'] == 'bbpress_after_ad' && $user_position == 'bbpress_after_ad' )|| ($ads['position'] == 'bbpress_before_ad' && $user_position == 'bbpress_before_ad')){
          $tag= '<!--CusAds'.esc_html($ads['ad_id']).'-->';
          echo   adsensei_replace_ads_new( $tag, 'CusAds' . $ads['ad_id'], $ads['ad_id'] );
        }else if(($ads['position'] == 'bbpress_before_reply' && $user_position == 'bbpress_before_reply' )|| ($ads['position'] == 'bbpress_after_reply' && $user_position == 'bbpress_after_reply')){
          if((did_action( 'bbp_theme_before_reply_content' ) % $ads['paragraph_number'] == 0  && $user_position == 'bbpress_before_reply' )|| (did_action( 'bbp_theme_after_reply_content' ) % $ads['paragraph_number'] == 0 && $user_position == 'bbpress_after_reply')){
            $tag= '<!--CusAds'.esc_html($ads['ad_id']).'-->';
            echo   adsensei_replace_ads_new( $tag, 'CusAds' . $ads['ad_id'], $ads['ad_id'] );
          }
        }elseif( $ads['position'] == 'before_header' && $user_position == 'newspaper_theme'){
          $tag= '<!--CusAds'.esc_html($ads['ad_id']).'-->';
          $html = preg_replace('/<div\sclass=\"td-header-menu-wrap-full td-container-wrap(.*?)\">(.*?)<div class=\"td-main-content-wrap /s', '<div class="td-header-menu-wrap-full td-container-wrap$1"> '.adsensei_replace_ads_new( $tag, 'CusAds' . $ads['ad_id'], $ads['ad_id'] ).'$2<div class="td-main-content-wrap' , $html);

        }elseif( $ads['position'] == 'after_header' && $user_position == 'newspaper_theme'){
          $tag= '<!--CusAds'.esc_html($ads['ad_id']).'-->';
          $html = preg_replace('/<div\sclass=\"td-header-menu-wrap-full td-container-wrap(.*?)<div class=\"td-main-content-wrap/s', '<div class="td-header-menu-wrap-full td-container-wrap$1 '.adsensei_replace_ads_new( $tag, 'CusAds' . $ads['ad_id'], $ads['ad_id'] ).'<div class="td-main-content-wrap' , $html);

        }
      }
    }
  }
  if($user_position == 'newspaper_theme'){
    return $html;
  }
}
function remove_ads_for_wp_shortcodes() {
  $adsensei_settings = get_option( 'adsensei_settings' );
  if(isset($adsensei_settings['adsforwp_adsensei_shortcode']) && $adsensei_settings['adsforwp_adsensei_shortcode']){
    remove_shortcode( 'adsforwp' );
    add_shortcode('adsforwp', 'adsensei_from_adsforwp_manual_ads',1);
  }
  if(isset($adsensei_settings['advance_ads_to_adsensei']) && $adsensei_settings['advance_ads_to_adsensei']){
    remove_shortcode( 'the_ad_placement' );
    remove_shortcode( 'the_ad' );
    add_shortcode('the_ad_placement', 'adsensei_from_advance_manual_ads',1);
    add_shortcode( 'the_ad', 'adsensei_from_advance_manual_ads',1);

  }
}
//Ad blocker
add_action('wp_head', 'adsensei_adblocker_detector');
add_action('wp_footer', 'adsensei_adblocker_popup_notice');
add_action('wp_footer', 'adsensei_adblocker_notice_jsondata');
add_action('wp_body_open', 'adsensei_adblocker_notice_bar');
add_action('wp_footer', 'adsensei_adblocker_ad_block');

function adsensei_from_advance_manual_ads($atts ){
  global $adsensei_options;

  // Display Condition is false and ignoreShortcodeCond is empty or not true
  if( !adsensei_ad_is_allowed() && !isset($adsensei_options['ignoreShortcodeCond']) )
  return;


  //return adsensei_check_meta_setting('NoAds');
  if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
    return;
  }
  $id = '';
  // The ad id
  // $advance_ads_id = isset( $atts['id'] ) ? ( int ) $atts['id'] : 0;
  $atts = is_array( $atts ) ? $atts : array();
  $advance_ads_id   = isset( $atts['id'] ) ? (string) $atts['id'] : '';
  $advanced_ads_placements       = get_option('advads-ads-placements');
  $args = array(
    'post_type' => 'advanced_ads',
    'post_status' => 'publish'
  );
  $get_Advanced_Ads = get_posts($args);

  foreach ($get_Advanced_Ads  as $advanced_Ad) {

    $name = 'shortcode_'.$advanced_Ad->ID;
    $advanced_ads_placements[$name] = array('item' => 'ad_'.$advanced_Ad->ID,'advanced_ads'=>true);
  }
  foreach ($advanced_ads_placements as $key => $value) {
    $idArray =  (isset($value['item']) && !empty($value['item'])) ?  explode('ad_', $value['item']) : array('1'=>'');

    if($idArray['1'] == $advance_ads_id){

      $id = $idArray['1'];

    }
  }
  if(empty($id)){
    return '';
  }
  $args = array(
    'post_type'      => 'adsensei-ads',
    'meta_key'       => 'advance_ads_id',
    'meta_value'     => $id
  );

  $event_query = new WP_Query( $args );

  if(!isset($event_query->post->ID)){
    return '';
  }
  $adsensei_post_id =$event_query->post->ID;
  $id_name = get_post_meta ( $adsensei_post_id, 'adsensei_ad_old_id', true );
  $id_array = explode('ad', $id_name );
  $id = $id_array[1];
  $arr = array(
    'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
    'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
    'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
    'float:none;margin:%1$dpx;');

    $adsalign = isset($adsensei_options['ads']['ad' . $id]['align']) ? $adsensei_options['ads']['ad' . $id]['align'] : 3; // default
    $adsmargin = isset( $adsensei_options['ads']['ad' . $id]['margin'] ) ? $adsensei_options['ads']['ad' . $id]['margin'] : '3'; // default
    $margin = sprintf( $arr[( int ) $adsalign], $adsmargin );


    // Do not create any inline style on AMP site
    $style = !adsensei_is_amp_endpoint() ? apply_filters( 'adsensei_filter_margins', $margin, 'ad' . $id ) : '';

    $code = "\n" . '<!-- WP ADSENSEI v. ' . ADSENSEI_VERSION . '  Shortcode Ad -->' . "\n" .
    '<div class="adsensei-location adsensei-ad' . $id . '" id="adsensei-ad' . $id . '" style="' . $style . '">' . "\n";
    $code .= do_shortcode( adsensei_get_ad( $id ) );
    $code .= '</div>' . "\n";

    return $code;
  }

  function adsensei_from_adsforwp_manual_ads($atts ){
    global $adsensei_options;

    // Display Condition is false and ignoreShortcodeCond is empty or not true
    if( !adsensei_ad_is_allowed() && !isset($adsensei_options['ignoreShortcodeCond']) )
    return;


    //return adsensei_check_meta_setting('NoAds');
    if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
      return;
    }

    // The ad id
    $adsforwpid = isset( $atts['id'] ) ? ( int ) $atts['id'] : 0;

    $args = array(
      'post_type'      => 'adsensei-ads',
      'meta_key'   => 'adsforwp_ads_id',
      'meta_value' => $adsforwpid
    );

    $event_query = new WP_Query( $args );
    if(!isset($event_query->post->ID)){
      return '';
    }
    $adsensei_post_id =$event_query->post->ID;
    $id_name = get_post_meta ( $adsensei_post_id, 'adsensei_ad_old_id', true );
    $id_array = explode('ad', $id_name );
    $id = $id_array[1];
    $arr = array(
      'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
      'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
      'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
      'float:none;margin:%1$dpx;');

      $adsalign = isset($adsensei_options['ads']['ad' . $id]['align']) ? $adsensei_options['ads']['ad' . $id]['align'] : 3; // default
      $adsmargin = isset( $adsensei_options['ads']['ad' . $id]['margin'] ) ? $adsensei_options['ads']['ad' . $id]['margin'] : '3'; // default
      $margin = sprintf( $arr[( int ) $adsalign], $adsmargin );


      // Do not create any inline style on AMP site
      $style = !adsensei_is_amp_endpoint() ? apply_filters( 'adsensei_filter_margins', $margin, 'ad' . $id ) : '';
      $code =
      "\n".'<div style="'.esc_html($style).'">'."\n".
      do_shortcode(adsensei_get_ad($id)).
      '</div>'. "\n";

      return $code;
    }
    function adsensei_adblocker_detector(){
      if(!adsensei_is_amp_endpoint())
      {
        $js_dir  = ADSENSEI_PLUGIN_URL . 'assets/js/';
        wp_enqueue_script( 'adsensei-admin-ads', $js_dir . 'ads.js', array(), ADSENSEI_VERSION, false );
      }

    }
    /**
    * It is default settings value, if value is not set for any option in setting section
    * @return type
    */
    function adsensei_defaultSettings(){

      $defaults = array(
        'app_blog_name'       => get_bloginfo( 'name' ),
        'advnc_ads_import_check'  => 1,
        'ad_blocker_support'      => 1,
        'notice_type'    => 'bar',
        'page_redirect'  => 0,
        'allow_cookies'    => 2,
        'notice_title'    => 'Adblock Detected!',
        'notice_description'    => 'Our website is made possible by displaying online advertisements to our visitors. Please consider supporting us by whitelisting our website.',
        'notice_close_btn' => 1,
        'btn_txt' => 'X',
        'notice_txt_color' => '#ffffff',
        'notice_bg_color' => '#1e73be',
        'notice_btn_txt_color' => '#ffffff',
        'notice_btn_bg_color' => '#f44336',
        'ad_sponsorship_label' => 0,
        'ad_sponsorship_label_text' => 'Advertisement',
        'ad_label_postion' => 'above',
        'ad_label_txt_color' => '#cccccc'
      );

      $settings = get_option( 'adsensei_settings', $defaults );

      return $settings;
    }
    function adsensei_adblocker_popup_notice(){

      $settings = adsensei_defaultSettings();

      if( isset($settings['ad_blocker_support']) && $settings['ad_blocker_support']){

        if($settings['notice_type'] == 'popup'){


          $content_color = sanitize_hex_color($settings['notice_txt_color']);
          $notice_title = esc_attr($settings['notice_title']);
          $notice_description = esc_attr($settings['notice_description']);
          $button_txt = esc_attr($settings['btn_txt']);
          $background_color = sanitize_hex_color($settings['notice_bg_color']);
          $btn_txt_color = sanitize_hex_color($settings['notice_btn_txt_color']);
          $btn_background_color = sanitize_hex_color($settings['notice_btn_bg_color']);

          ?>
          <div id="adsensei-myModal_" class="adsensei-modal" style="display:none">
            <!-- Modal content -->
            <div class="adsensei-modal-content">
              <?php if( isset($settings['notice_close_btn']) && $settings['notice_close_btn'] && empty($button_txt) ){
                ?>
                <span class="adsensei-close adsensei-cls-notice">&times;</span>
                <?php
              }
              ?>
              <h2 style="text-align: center;padding-top:0;color: <?php echo $content_color;?>;"><?php echo $notice_title;?></h2>
              <p style="margin:0 0 1.5em;padding: 0;text-align: center;color: <?php echo $content_color;?>;"><?php echo $notice_description;?></p>
              <?php if( isset($settings['notice_close_btn']) && $settings['notice_close_btn'] &&  !empty($button_txt) ){
                ?>
                <button class="adsensei-button adsensei-closebtn adsensei-cls-notice"><?php echo $button_txt;?></button>
                <?php
              }
              ?>
            </div>
          </div>
          <style type="text/css">
          .adsensei-modal {
            display: block; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 999; /* Sit on top */
            padding-top: 200px; /* Location of the box */
            left: 0;
            right:0;
            top: 50%;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            -webkit-transform:translateY(-50%);
            -moz-transform:translateY(-50%);
            -ms-transform:translateY(-50%);
            -o-transform:translateY(-50%);
            transform:translateY(-50%);
          }

          /* Modal Content */
          .adsensei-modal-content {
            background-color: <?php echo $background_color;?>;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
            border-radius: 10px;
            text-align: center;
          }

          /* The Close Button */
          .adsensei-close{
            color: <?php echo $btn_txt_color;?>;
            float: right;
            font-size: 28px;
            font-weight: bold;
          }

          .adsensei-close:hover,
          .adsensei-close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
          }
          .adsensei-button {
            background-color: <?php echo $btn_background_color;?>; /* Green */
            border: none;
            color: <?php echo $btn_txt_color;?>;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
          }
          @media screen and (max-width: 1024px) {
            .adsensei-modal-content {
              width: 80%;
              font-size: 14px;
            }
            .adsensei-modal {
              padding-top: 100px;
            }
          }
          </style>
          <?php
        }
      }
    }
    function adsensei_adblocker_notice_jsondata(){
      if(!adsensei_is_amp_endpoint())
      {
        $settings = adsensei_defaultSettings();
        $output = '';
        $adsensei_mode = get_option('adsensei-mode');
        if( isset($settings['ad_blocker_support']) && $settings['ad_blocker_support'] && !empty($settings['notice_type']) || ($adsensei_mode && $adsensei_mode == 'old' && isset($settings['ad_blocker_message'])  && $settings['ad_blocker_message'])){
          $output    .= '<script type="text/javascript">';
          $output    .= '/* <![CDATA[ */';
          $output    .= 'var adsenseiOptions =' .
          json_encode(
            array(
              'adsenseiChoice'          => esc_attr($settings['notice_type']),
              'page_redirect'          => (isset($settings['page_redirect_path']['value']) && !empty($settings['page_redirect_path']['value'])) ? get_permalink($settings['page_redirect_path']['value'] ):'',
              'allow_cookies'         => esc_attr($settings['notice_behaviour'])
              )
            );
            $output    .= '/* ]]> */';
            $output    .= '</script>';
            echo $output;
          }
        }
      }
      function adsensei_adblocker_notice_bar(){
        $settings = adsensei_defaultSettings();

        if( isset($settings['ad_blocker_support']) && $settings['ad_blocker_support']){
          if($settings['notice_type'] == 'bar' ){

            $notice_description = esc_attr($settings['notice_description']);
            $button_txt = esc_attr($settings['btn_txt']);
            $content_color = sanitize_hex_color($settings['notice_txt_color']);
            $background_color = sanitize_hex_color($settings['notice_bg_color']);
            $btn_txt_color = sanitize_hex_color($settings['notice_btn_txt_color']);
            $btn_background_color = sanitize_hex_color($settings['notice_btn_bg_color']);
            ?>

            <div id="adsensei-myModal" class="adsensei-adblocker-notice-bar">
              <div class="enb-textcenter">
                <?php if( isset($settings['notice_close_btn'])&& $settings['notice_close_btn'] && empty($button_txt)){?>
                  <span class="adsensei-close adsensei-cls-notice">&times;</span>
                <?php } ?>
                <div class="adsensei-adblocker-message">
                  <?php echo $notice_description;?>
                </div>
                <?php if( isset($settings['notice_close_btn'])&& $settings['notice_close_btn'] && !empty($button_txt)){?>
                  <button class="adsensei-button adsensei-closebtn adsensei-cls-notice"><?php echo $button_txt;?></button>
                <?php } ?>
              </div>
            </div>
            <style type="text/css">
            .adsensei-adblocker-message{
              display: inline-block;
            }
            .adsensei-adblocker-notice-bar {
              display: none;
              width: 100%;
              background: <?php echo $background_color;?>;
              color: <?php echo $content_color;?>;
              padding: 0.5em 1em;
              font-size: 16px;
              line-height: 1.8;
              position: relative;
              z-index: 99;
            }
            .adsensei-adblocker-notice-bar strong {
              color: inherit; /* some themes change strong tag to make it darker */
            }
            /* Alignments */
            .adsensei-adblocker-notice-bar .enb-textcenter {
              text-align: center;
            }
            .adsensei-close{
              color: <?php echo $btn_txt_color;?>;
              float: right;
              font-size: 20px;
              font-weight: bold;
            }
            .adsensei-close:hover,
            .adsensei-close:focus {
              color: #000;
              text-decoration: none;
              cursor: pointer;
            }
            .adsensei-button {
              background-color: <?php echo $btn_background_color;?>; /* Green */
              border: none;
              color: <?php echo $btn_txt_color;?>;
              padding: 5px 10px;
              text-align: center;
              text-decoration: none;
              display: inline-block;
              font-size: 14px;
              margin: 0px 2px;
              cursor: pointer;
              float: right;
            }
            @media screen and (max-width: 1024px) {
              .adsensei-modal-content {
                font-size: 14px;
              }
              .adsensei-button{
                padding:5px 10px;
                font-size: 14px;
                float:none;
              }
            }
            </style>
            <?php
          }
        }
      }
      function adsensei_adblocker_ad_block(){
        if(!adsensei_is_amp_endpoint())
        {
          $settings = adsensei_defaultSettings();
          $adsensei_mode = get_option('adsensei-mode');
          if( isset($settings['ad_blocker_support']) && $settings['ad_blocker_support'] && !empty($settings['notice_type']) || ($adsensei_mode && $adsensei_mode == 'old' && isset($settings['ad_blocker_message'])  && $settings['ad_blocker_message'])){

            ?>
            <script type="text/javascript">

            if(typeof adsenseiOptions !== 'undefined' && typeof wpadsensei_adblocker_check_2
            === 'undefined' && adsenseiOptions.adsenseiChoice == 'ad_blocker_message'){
              var addEvent1 = function (obj, type, fn) {
                if (obj.addEventListener)
                obj.addEventListener(type, fn, false);
                else if (obj.attachEvent)
                obj.attachEvent('on' + type, function () {
                  return fn.call(obj, window.event);
                });
              };
              addEvent1(window, 'load', function () {
                if (typeof wpadsensei_adblocker_check_2 === "undefined" || wpadsensei_adblocker_check_2 === false) {

                  highlight_adblocked_ads();
                }
              });

              function highlight_adblocked_ads() {
                try {
                  var ad_wrappers = document.querySelectorAll('div[id^="adsensei-ad"]')
                } catch (e) {
                  return;
                }

                for (i = 0; i < ad_wrappers.length; i++) {
                  ad_wrappers[i].className += ' adsensei-highlight-adblocked';
                  ad_wrappers[i].className = ad_wrappers[i].className.replace('adsensei-location', '');
                  ad_wrappers[i].setAttribute('style', 'display:block !important');
                }
              }
            }

            (function() {
              //Adblocker Notice Script Starts Here
              var curr_url = window.location.href;
              var red_ulr = localStorage.getItem('curr');
              var modal = document.getElementById("adsensei-myModal");
              var adsenseiAllowedCookie =  adsenseigetCookie('adsenseiAllowedCookie');

              if(typeof adsenseiOptions !== 'undefined' && typeof wpadsensei_adblocker_check_2
              === 'undefined' ){

                var adsensei_model_  = document.getElementById("adsensei-myModal_");
                if(adsensei_model_){ adsensei_model_.style.display = "block"; }

                if(adsenseiAllowedCookie!=adsenseiOptions.allow_cookies){
                  adsenseisetCookie('adsenseiCookie', '', -1, '/');
                  adsenseisetCookie('adsenseiAllowedCookie', adsenseiOptions.allow_cookies, 1, '/');
                }

                if(adsenseiOptions.allow_cookies == 2){
                  if( adsenseiOptions.adsenseiChoice == 'bar' || adsenseiOptions.adsenseiChoice == 'popup'){
                    modal.style.display = "block";
                    adsenseisetCookie('adsenseiCookie', '', -1, '/');
                  }

                  if(adsenseiOptions.adsenseiChoice == 'page_redirect' && adsenseiOptions.page_redirect !="undefined"){
                    if(red_ulr==null || curr_url!=adsenseiOptions.page_redirect){
                      window.location = adsenseiOptions.page_redirect;
                      localStorage.setItem('curr',adsenseiOptions.page_redirect);
                    }
                  }
                }else{
                  var adsCookie = adsenseigetCookie('adsenseiCookie');
                  if(adsCookie==false) {
                    if( adsenseiOptions.adsenseiChoice == 'bar' || adsenseiOptions.adsenseiChoice == 'popup'){
                      modal.style.display = "block";
                    }
                    if(adsenseiOptions.adsenseiChoice == 'page_redirect' && adsenseiOptions.page_redirect !="undefined"){
                      window.location = adsenseiOptions.page_redirect;
                      adsenseisetCookie('adsenseiCookie', true, 1, '/');
                    }
                  }else{
                    modal.style.display = "none";
                  }
                }
              }



              var span = document.getElementsByClassName("adsensei-cls-notice")[0];
              if(span){
                span.onclick = function() {
                  modal.style.display = "none";
                  document.cookie = "adsensei_prompt_close="+new Date();
                  adsenseisetCookie('adsenseiCookie', 'true', 1, '/');
                }
              }

              var adsensei_closebtn = document.getElementsByClassName("adsensei-closebtn")[0]
              var adsensei_modal = document.getElementById("adsensei-myModal")
              if (adsensei_closebtn) {
                adsensei_closebtn.addEventListener('click', function(){
                  if( adsensei_closebtn ){
                    adsensei_modal.style.display = "none"
                  }
                } )
              }

              window.onclick = function(event) {
                if (event.target == modal) {
                  // modal.style.display = "none";
                  document.cookie = "adsensei_prompt_close="+new Date();
                  adsenseisetCookie('adsenseiCookie', 'true', 1, '/');
                }
              }
            })();
            function adsenseigetCookie(cname){
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
            function adsenseisetCookie(cname, cvalue, exdays, path){
              var d = new Date();
              d.setTime(d.getTime() + (exdays*24*60*60*1000));
              var expires = "expires="+ d.toUTCString();
              document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            }
            //Adblocker Notice Script Ends Here
          </script>

          <?php
        }
      }
    }
    /**
    * Show ads before posts
    * @not used at the moment
    */
    //add_action('loop_start', 'adsensei_inject_ad');

    //function adsensei_inject_ad() {
    //   global $adsensei_options, $post;
    //
    //   // Ads are deactivated via post meta settings
    //    if( adsensei_check_meta_setting( 'NoAds' ) === '1' || adsensei_check_meta_setting( 'OffBegin' ) === '1'){
    //        return false;
    //    }
    //
    //   if( !adsensei_ad_is_allowed( '' ) || !is_main_query() ) {
    //      return false;
    //   }
    //   // Array of ad codes ids
    //   $adsArray = adsensei_get_active_ads();
    //
    //   // Return no ads are defined
    //   if( count($adsArray) === 0 ) {
    //      return false;
    //   }
    //
    //   $id = 1;
    //
    //   $code = !empty($adsensei_options['ads']['ad' . $id ]['code']) ? $adsensei_options['ads']['ad' . $id ]['code'] : '';
    //   echo adsensei_render_ad(1, $code, false);
    //
    //}

    function adsensei_classic_to_gutenberg($data)
    {
      if (isset($data->data['content']['raw'])) {
        $data->data['content']['raw'] =  preg_replace('/<!--Ads(\d+)-->/','[adsensei id=$1]', $data->data['content']['raw']);
        $data->data['content']['raw'] =  str_replace('<!--RndAds-->', '[adsensei id=RndAds]', $data->data['content']['raw']);
      }
      return $data;
    }
    function adsensei_change_adsbygoogle_to_amp($content){
      if (adsensei_is_amp_endpoint()){
        $dom = new DOMDocument();
        if( function_exists( 'mb_convert_encoding' ) ){
          $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
        }
        else{
          $content =  preg_replace( '/&.*?;/', 'x', $content ); // multi-byte characters converted to X
        }
        if(empty($content)){
          return $content;
        }
        @$dom->loadHTML($content);
        $nodes = $dom->getElementsByTagName( 'ins' );

        $num_nodes  = $nodes->length;
        for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
          $url = $width = $height = '';
          $node   = $nodes->item( $i );
          if($node->getAttribute('class') == 'adsbygoogle'){
            $adclient= $node->getAttribute('data-ad-client');
            $adslot= $node->getAttribute('data-ad-slot');
            $adformat= $node->getAttribute('data-ad-format');
            $adfullwidth= $node->getAttribute('data-full-width-responsive');

            $new_node= $dom->createElement('amp-ad');
            $new_node->setAttribute('type', 'adsense');
            $new_node->setAttribute('data-ad-client', $adclient);
            $new_node->setAttribute('data-ad-slot', $adslot);
            if($node->getAttribute('data-full-width-responsive')){
              $new_node->setAttribute('data-ad-format', $adformat);
              $new_node->setAttribute('data-full-width-responsive', $adfullwidth);
            }
            $styletag= $node->getAttribute('style');
            $widthreg = "/width:(?<width>\\d+)/";
            $heightreg = "/height:(?<height>\\d+)/";
            preg_match($widthreg, $styletag, $width);
            preg_match($heightreg, $styletag, $height);
            if(isset($width['width'])){
              $new_node->setAttribute('width', $width['width']);
            }else{
              $new_node->setAttribute('width', '100vw');
            }
            if(isset($height['height'])){
              $new_node->setAttribute('height', $height['height']);
            }else{
              $new_node->setAttribute('height', '320');
            }
            $child_element= $dom->createElement('div');
            $child_element->setAttribute('overflow', '');
            $new_node->appendChild( $child_element );

            $node->parentNode->replaceChild($new_node, $node);
          }
        }
        $content = $dom->saveHTML();
      }
      return $content;
    }

    /**
    * Adds quicktags, defined via post meta options, to content.
    *
    * @param $content Post content
    *
    * @return string
    */
    function adsensei_post_settings_to_quicktags ( $content ) {

      // Return original content if ADSENSEI is not allowed
      if ( !adsensei_ad_is_allowed($content)){
        return $content;
      }

      $quicktags_str = adsensei_get_visibility_quicktags_str();

      return $content . $quicktags_str;
    }
    /**
    * Returns quicktags based on post meta options.
    * These quicktags define which ads should be hidden on current page.
    *
    * @param null $post_id Post id
    *
    * @return string
    */
    function adsensei_get_visibility_quicktags_str( $post_id = null ) {

      if( !$post_id ) {
        $post_id = get_the_ID();
      }

      $str = '';
      if( false === $post_id ) {
        return $str;
      }

      $config = get_post_meta( $post_id, '_adsensei_config_visibility', true );

      if( !empty( $config ) && is_array($config) ) {
        foreach ( $config as $qtag_id => $qtag_label ) {
          $str .= '<!--' . $qtag_id . '-->';
        }
      }

      return $str;
    }

    /**
    * Get load priority
    *
    * @global arr $adsensei_options
    * @return int
    */
    function adsensei_get_load_priority(){
      global $adsensei_options;

      if (!empty($adsensei_options['priority'])){
        return intval($adsensei_options['priority']);
      }
      return 20;
    }

    /**
    *
    * @global arr $adsensei_options
    * @global type $adsArray
    * @param type $content
    * @return type
    */
    function adsensei_process_content( $content ) {
      global $adsensei_mode, $adsensei_options, $adsArray, $adsArrayCus, $visibleContentAds, $ad_count_widget, $visibleShortcodeAds;

      // Array of ad codes ids
      $adsArray = adsensei_get_active_ads();

      // Return is no ads are defined
      if ($adsArray === 0 && $adsensei_mode != 'new'){
        return $content;
      }

      // Do nothing if maximum ads are reached in post content
      if( $visibleContentAds >= adsensei_get_max_allowed_post_ads( $content )  ) {
        $content = adsensei_clean_tags( $content );
        return $content;
      }

      // Do not do anything if ads are not allowed or process is not in the main query
      if( !adsensei_ad_is_allowed( $content ) || !is_main_query()) {
        $content = adsensei_clean_tags( $content );
        return $content;
      }

      $content = adsensei_sanitize_content( $content );

      if($adsensei_mode == 'new'){
        $content = adsensei_filter_default_ads_new( $content );
        $content = '<!--EmptyClear-->' . $content . "\n";
        $content = adsensei_clean_tags( $content, true );
        $content = adsensei_parse_default_ads_new( $content );
        $content = adsensei_parse_quicktags( $content );
        $content = adsensei_parse_random_quicktag_ads($content);
        $content = adsensei_parse_random_ads_new( $content );
        $content = adsensei_clean_tags( $content );
        $content = adsensei_parse_popup_ads( $content );
        $content = adsensei_parse_video_ads( $content );
        return do_shortcode( $content );
      }else{
        $content = adsensei_filter_default_ads( $content );
        $content = '<!--EmptyClear-->' . $content . "\n";
        $content = adsensei_clean_tags( $content, true );
        $content = adsensei_parse_default_ads( $content );
        $content = adsensei_parse_quicktags( $content );
        $content = adsensei_parse_random_quicktag_ads($content);
        $content = adsensei_parse_random_ads( $content );
        $content = adsensei_clean_tags( $content );
        return do_shortcode( $content );
      }
    }


    /**
    * Return number of active widget ads
    * @param string the_content
    * @return int amount of widget ads
    */
    function adsensei_get_number_widget_ads() {
      $number_widgets = 0;
      $maxWidgets = 10;
      // count active widget ads
      for ( $i = 1; $i <= $maxWidgets; $i++ ) {
        $AdsWidName = 'AdsWidget%d (Quick Adsense Reloaded)';
        $wadsid = sanitize_title( str_replace( array('(', ')'), '', sprintf( $AdsWidName, $i ) ) );
        $number_widgets += (is_active_widget( '', '', $wadsid )) ? 1 : 0;
      }

      return $number_widgets;
    }

    /**
    * Get list of valid ad ids's where either the plain text code field or the adsense ad slot and the ad client id is populated.
    * @global arr $adsensei_options
    */
    function adsensei_get_active_ads() {
      global $adsensei_options;


      // Return early
      if (empty($adsensei_options['ads'])){
        return 0;
      }

      // count valid ads
      $i = 1;
      foreach ( $adsensei_options['ads'] as $ads) {
        $tmp = isset( $adsensei_options['ads']['ad' . $i]['code'] ) ? trim( $adsensei_options['ads']['ad' . $i]['code'] ) : '';
        // id is valid if there is either the plain text field populated or the adsense ad slot and the ad client id
        if( !empty( $tmp ) || (!empty( $adsensei_options['ads']['ad' . $i]['g_data_ad_slot'] ) && !empty( $adsensei_options['ads']['ad' . $i]['g_data_ad_client'] ) ) ) {
          $adsArray[] = $i;
        }
        $i++;
      }
      return (isset($adsArray) && count($adsArray) > 0) ? $adsArray : 0;
    }

    /**
    * Get list of valid ad ids's where either the plain text code field or the adsense ad slot and the ad client id is populated.
    * @global arr $adsensei_options
    */
    function adsensei_get_active_ads_backup() {

      $adsensei_settings_backup = get_option( 'adsensei_settings_backup' );


      // Return early
      if (empty($adsensei_settings_backup['ads'])){
        return 0;
      }

      // count valid ads
      $i = 1;
      foreach ( $adsensei_settings_backup['ads'] as $ads) {
        $tmp = isset( $adsensei_settings_backup['ads']['ad' . $i]['code'] ) ? trim( $adsensei_settings_backup['ads']['ad' . $i]['code'] ) : '';
        // id is valid if there is either the plain text field populated or the adsense ad slot and the ad client id
        if( !empty( $tmp ) || (!empty( $adsensei_settings_backup['ads']['ad' . $i]['g_data_ad_slot'] ) && !empty( $adsensei_settings_backup['ads']['ad' . $i]['g_data_ad_client'] ) ) ) {
          $adsArray[] = $i;
        }
        $i++;
      }
      return (isset($adsArray) && count($adsArray) > 0) ? $adsArray : 0;
    }

    /**
    * Get max allowed numbers of ads
    *
    * @param string $content
    * @return int maximum number of ads
    */
    function adsensei_get_max_allowed_post_ads( $content ) {
      global $adsensei_options;

      // Maximum allowed general number of ads
      $maxAds = isset( $adsensei_options['maxads'] ) ? $adsensei_options['maxads'] : 10;

      $numberWidgets = 10;

      $AdsWidName = 'AdsWidget%d (Quick Adsense Reloaded)';

      // count number of active widgets and subtract them
      if( strpos( $content, '<!--OffWidget-->' ) === false &&  !adsensei_is_amp_endpoint() ) {
        for ( $i = 1; $i <= $numberWidgets; $i++ ) {
          $wadsid = sanitize_title( str_replace( array('(', ')'), '', sprintf( $AdsWidName, $i ) ) );
          $maxAds -= (is_active_widget( '', '', $wadsid )) ? 1 : 0;
        }
      }

      return $maxAds;
    }


    /**
    * Filter default ads
    *
    * @global array $adsensei_options global settings
    * @global array $adsArrayCus List of ad id'S
    * @param string $content
    * @return string content
    */
    function adsensei_filter_default_ads_new( $content ) {

      global $adsensei_options, $adsArrayCus;

      $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);

      if( $off_default_ads ) { // If default ads are disabled
        return $content;
      }
      if(!isset($adsensei_ads)|| empty($adsensei_ads))
      {
        require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
        $api_service = new ADSENSEI_Ad_Setup_Api_Service();
        $adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');
      }
      // Default Ads
      $adsArrayCus = array();
      if(isset($adsensei_ads['posts_data'])){

        $i = 1;
        foreach($adsensei_ads['posts_data'] as $key => $value){
          $ads =$value['post_meta'];
          if($value['post']['post_status']== 'draft'){
            continue;
          }
          if(isset($ads['random_ads_list']))
          $ads['random_ads_list'] = unserialize($ads['random_ads_list']);
          if(isset($ads['visibility_include']))
          $ads['visibility_include'] = unserialize($ads['visibility_include']);
          if(isset($ads['visibility_exclude']))
          $ads['visibility_exclude'] = unserialize($ads['visibility_exclude']);

          if(isset($ads['targeting_include']))
          $ads['targeting_include'] = unserialize($ads['targeting_include']);

          if(isset($ads['targeting_exclude']))
          $ads['targeting_exclude'] = unserialize($ads['targeting_exclude']);
          $is_on         = adsensei_is_visibility_on($ads);
          $is_visitor_on = adsensei_is_visitor_on($ads);
          $is_click_fraud_on = adsensei_click_fraud_on();
          if(isset($ads['ad_id']))
          $post_status = get_post_status($ads['ad_id']);
          else
          $post_status =  'publish';
          if($is_on && $is_visitor_on && $is_click_fraud_on && $post_status=='publish'){
            $ads  = apply_filters( 'adsensei_default_filter_position_data', $ads);
            $ads  = apply_filters( 'adsensei_default_filter_position_data_ab_testing', $ads);

            $position     = (isset($ads['position']) && $ads['position'] !='') ? $ads['position'] : '';
            $cls_btn     = (isset($ads['cls_btn']) && $ads['cls_btn'] !='') ? $ads['cls_btn'] : '';
            $paragraph_no = (isset($ads['paragraph_number']) && $ads['paragraph_number'] !='') ? $ads['paragraph_number'] : 1;
            $word_count_number = (isset($ads['word_count_number']) && $ads['word_count_number'] !='') ? $ads['word_count_number'] : 1;
            $imageNo      = (isset($ads['image_number']) && $ads['image_number'] !='') ? $ads['image_number'] : 1;
            $imageCaption = isset($ads['image_caption']) ? $ads['image_caption'] : false;
            $end_of_post  = isset($ads['enable_on_end_of_post']) ? $ads['enable_on_end_of_post'] : false;

            // placeholder string for custom ad spots
            if(isset($ads['random_ads_list']) && !empty($ads['random_ads_list'])){
              $cusads = '<!--CusRnd'.esc_html($ads['ad_id']).'-->';
            }else if($ads['ad_type']== 'rotator_ads' &&isset($ads['ads_list']) && !empty($ads['ads_list'])){
              $cusads = '<!--CusRot'.esc_html($ads['ad_id']).'-->';
            }else if($ads['ad_type']== 'popup_ads' &&isset($ads['ads_list']) && !empty($ads['ads_list'])){
              $cusads = '<!--pop_up_ads'.esc_html($ads['ad_id']).'-->';
            }else if($ads['ad_type']== 'video_ads'){
              $cusads = '<!--video_ad'.esc_html($ads['ad_id']).'-->';
            }else{
              $cusads = '<!--CusAds'.esc_html($ads['ad_id']).'-->';
            }
            switch ($position) {

              case 'beginning_of_post':
              if(strpos( $content, '<!--OffBegin-->' ) === false ) {
                $content = $cusads.$content;
              }
              break;

              case 'middle_of_post':

              // Check if ad is middle one
              if(strpos( $content, '<!--OffMiddle-->' ) === false ) {
                $closing_p        = '</p>';
                $paragraphs       = explode( $closing_p, $content );
                $total_paragraphs = count($paragraphs);
                $paragraph_id     = floor($total_paragraphs /2);
                if( strpos($content, "</blockquote>") || strpos($content, "</table>")){
                  $ads_data['after_the_percentage_value'] = 50;
                  $content =  remove_ad_from_content($content,$cusads,$ads_data);

                }else{
                  foreach ($paragraphs as $index => $paragraph) {
                    if ( trim( $paragraph ) ) {
                      $paragraphs[$index] .= $closing_p;
                    }
                    if ( $paragraph_id == $index + 1 ) {
                      $paragraphs[$index] .= $cusads;
                    }
                  }
                  $content = implode('', $paragraphs );
                }
              }

              break;
              case 'end_of_post':
              if(strpos( $content, '<!--OffEnd-->' ) === false ) {
                $content = $content.$cusads;
              }
              # code...
              break;

              case 'ad_sticky_ad':
              $sticky_cookie =   (isset( $_COOKIE['adsensei_sticky'] ) && $_COOKIE['adsensei_sticky']!== NULL ) ? $_COOKIE['adsensei_sticky'] : '' ;
              if( $sticky_cookie !== "sticky_ad" ){
                if(strpos( $content, '<!--OffEnd-->' ) === false ) {
                  $a_tag = '';
                  if( isset($cls_btn) && $cls_btn == 1 ){
                    $a_tag = '<a class="adsensei-sticky-ad-close">x</a>';
                  }
                  $q_main_open = '<div class="adsensei-sticky">'.$a_tag.'';
                  $q_close = '</div>';
                  $content = $content.$q_main_open.$cusads.$q_close;}
                }
                break;
                case 'after_more_tag':
                // Check if ad is after "More Tag"
                if(strpos( $content, '<!--OffAfMore-->' ) === false ) {
                  $postid  = get_the_ID();
                  $content = str_replace( '<span id="more-' . $postid . '"></span>', $cusads, $content );
                }
                break;
                case 'before_last_paragraph':

                  if(strpos( $content, '<!--OffBfLastPara-->' ) === false ) {
                    $closing_p        = '</p>';
                    $paragraphs       = explode( $closing_p, $content );
                    $p_count          = count($paragraphs);
                    $paragraph_no     = ($p_count - 2);
                    if($paragraph_no <= $p_count){

                      foreach ($paragraphs as $index => $paragraph) {
                        if ( trim( $paragraph ) ) {
                          $paragraphs[$index] .= $closing_p;
                        }
                        if ( $paragraph_no == $index + 1 ) {
                          $paragraphs[$index] .= $cusads;
                        }
                      }
                      $content = implode( '', $paragraphs );
                    }
                  }

                  break;
                  case 'after_word_count':

                  if(strpos( $content, '<!--OffBfLastPara-->' ) === false ) {


                    $paragraphs       =  explode( ' ', $content );
                    $p_count          = count($paragraphs);
                    $original_paragraph_no = $paragraph_no;
                    ;

                    $flag= false;
                    if($word_count_number <= $p_count){
                      if( strpos($content, "</blockquote>") || strpos($content, "</table>")){
                        $content =  remove_ad_from_content($content,$cusads,'',$paragraph_no);
                      }else{

                        foreach ($paragraphs as $index => $paragraph) {

                          if ( $word_count_number == $index + 1 ) {
                            $flag= true;
                          }
                          if($flag && preg_match("/<[^<]+>/",$paragraphs[$index])){
                            $pattern = "#<\s*?li\b[^>]*>(.*?)#s"; //  to find the tag name
                            preg_match($pattern, $paragraphs[$index], $matches);
                            if(isset($matches[0])){
                              $tagname =$matches[0];
                              $stringarray= explode($tagname,$paragraphs[$index]);
                              if(isset($stringarray[0])){
                                $stringarray[0]=$stringarray[0].$cusads;
                                $paragraphs[$index] =   implode($tagname,$stringarray);
                              }
                            }else{
                              $paragraphs[$index] .= $cusads;
                            }
                            $flag= false;
                          }
                        }
                        $content = implode( ' ', $paragraphs );
                      }

                    }
                  }

                  break;
                  case 'after_paragraph':

                  if(strpos( $content, '<!--OffBfLastPara-->' ) === false ) {
                    $repeat_paragraph = (isset($ads['repeat_paragraph']) && !empty($ads['repeat_paragraph'])) ? $ads['repeat_paragraph'] : false;
                    $paragraph_limit         = isset($ads['paragraph_limit']) ? $ads['paragraph_limit'] : '';
                    $insert_after         = isset($ads['insert_after']) ? $ads['insert_after'] : 1;

                    $closing_p        = '</p>';
                    $paragraphs       = explode( $closing_p, $content );
                    $p_count          = count($paragraphs);
                    $original_paragraph_no = $paragraph_no;

                    if($paragraph_no <= $p_count){
                      if($ads['ad_type']== 'group_insertion'){
                        $p_count =$p_count -1;
                        $cusads = '<!--CusGI'.$ads['ad_id'].'-->';
                        $next_insert_val = $insert_after;
                        $displayed_ad =1;
                        foreach ($paragraphs as $index => $paragraph) {
                          $addstart = false;
                          if ( trim( $paragraph ) ) {
                            $paragraphs[$index] .= $closing_p;
                          }

                          if((!empty($paragraph_limit) && $paragraph_limit < $displayed_ad) || ($index == $p_count )){
                            break;
                          }
                          if($index+1 == $next_insert_val){
                            $displayed_ad +=1;
                            $next_insert_val = $next_insert_val+$insert_after;
                            $addstart = true;
                          }
                          if($addstart){
                            $paragraphs[$index] .= $cusads;
                          }
                        }
                      }else if($ads['ad_type']== 'sticky_scroll'){
                        $p_count =$p_count -1;
                        $cusads = '<!--CusSS'.$ads['ad_id'].'-->';
                        $next_insert_val = $insert_after;
                        $displayed_ad =1;
                        foreach ($paragraphs as $index => $paragraph) {
                          $addstart = false;
                          if ( trim( $paragraph ) ) {
                            $paragraphs[$index] .= $closing_p;
                          }

                          if((!empty($paragraph_limit) && $paragraph_limit < $displayed_ad) || ($index == $p_count )){
                            break;
                          }
                          if($index+1 == $next_insert_val){
                            $displayed_ad +=1;
                            $next_insert_val = $next_insert_val+$insert_after;
                            $addstart = true;
                          }
                          if($addstart){
                            $paragraphs[$index] .= $cusads;
                          }
                          if(!$repeat_paragraph)
                          {
                            break;
                          }
                        }
                      }else{

                        foreach ($paragraphs as $index => $paragraph) {
                          if ( trim( $paragraph ) ) {
                            $paragraphs[$index] .= $closing_p;
                          }
                          if ( $paragraph_no == $index + 1 ) {
                            $paragraphs[$index] .= $cusads;
                            if($repeat_paragraph){
                              $paragraph_no =  $original_paragraph_no+$paragraph_no;
                            }
                          }
                        }
                      }
                      $content = implode( '', $paragraphs );
                    }else{
                      if($end_of_post){
                        $content = $content.$cusads;
                      }
                    }
                  }
                  break;

                  case 'after_image':

                  // Sanitation
                  $imgtag = "<img";
                  $delimiter = ">";
                  $caption = "[/caption]";
                  $atag = "</a>";
                  $content = str_replace( "<IMG", $imgtag, $content );
                  $content = str_replace( "</A>", $atag, $content );

                  // Get all images in content
                  $imagesArray = explode( $imgtag, $content );
                  // Modify Image ad
                  if( ( int ) $imageNo < count( $imagesArray ) ) {
                    //Get all tags
                    $tagsArray = explode( $delimiter, $imagesArray[$imageNo] );
                    if( count( $tagsArray ) > 1 ) {
                      $captionArray = explode( $caption, $imagesArray[$imageNo] );
                      $ccp = ( count( $captionArray ) > 1 ) ? strpos( strtolower( $captionArray[0] ), '[caption ' ) === false : false;
                      $imagesArrayAtag = explode( $atag, $imagesArray[$imageNo] );
                      $cdu = ( count( $imagesArrayAtag ) > 1 ) ? strpos( strtolower( $imagesArrayAtag[0] ), '<a href' ) === false : false;
                      // Show ad after caption
                      if( $imageCaption && $ccp ) {
                        $imagesArray[$imageNo] = implode( $caption, array_slice( $captionArray, 0, 1 ) ) . $caption . "\r\n" .$cusads. "\r\n" . implode( $caption, array_slice( $captionArray, 1 ) );
                      } else if( $cdu ) {
                        $imagesArray[$imageNo] = implode( $atag, array_slice( $imagesArrayAtag, 0, 1 ) ) . $atag . "\r\n" . $cusads . "\r\n" . implode( $atag, array_slice( $imagesArrayAtag, 1 ) );
                      } else {
                        $imagesArray[$imageNo] = implode( $delimiter, array_slice( $tagsArray, 0, 1 ) ) . $delimiter . "\r\n" .$cusads . "\r\n" . implode( $delimiter, array_slice( $tagsArray, 1 ) );
                      }
                    }
                    $content = implode( $imgtag, $imagesArray );
                  }

                  break;
                  case 'after_the_percentage':

                  $content =  remove_ad_from_content($content,$cusads,$ads);

                  break;
                  case 'ad_after_html_tag':
                  $tag = 'p';
                  switch ( $ads['count_as_per']) {
                    case 'p_tag':
                    $tag = 'p';
                    break;
                    case 'div_tag':
                    $tag = 'div';
                    break;
                    case 'img_tag':
                    $tag = 'img';
                    break;
                    case 'custom_tag':
                    $tag = $ads['enter_your_tag'];
                    break;

                    default:
                    $tag = $ads['count_as_per'];
                    break;
                  }


                  $repeat_paragraph = (isset($ads['repeat_paragraph']) && !empty($ads['repeat_paragraph'])) ? $ads['repeat_paragraph'] : false;
                  if( strpos($content, "</blockquote>") || strpos($content, "</table>")){
                    $content =  remove_ad_from_content($content,$cusads,'',$paragraph_no,$repeat_paragraph);
                  }else{
                    $closing_p        = '</'.$tag.'>';
                    $paragraphs       = explode( $closing_p, $content );
                    $p_count          = count($paragraphs);
                    $original_paragraph_no = $paragraph_no;
                    if($paragraph_no <= $p_count){

                      foreach ($paragraphs as $index => $paragraph) {
                        if($p_count==($index+1)){
                          continue;
                        }
                        if ( trim( $paragraph ) ) {
                          $paragraphs[$index] .= $closing_p;
                        }
                        if ( $paragraph_no == $index + 1 ) {
                          $paragraphs[$index] .= $cusads;
                          if($repeat_paragraph){
                            $paragraph_no =  $original_paragraph_no+$paragraph_no;
                          }
                        }
                      }
                      $content = implode( '', $paragraphs );
                    }else{
                      if($end_of_post){
                        $content = $content.$cusads;
                      }
                    }
                  }
                  break;

                  case 'ad_before_html_tag':
                    $tag = 'p';
                    switch ( $ads['count_as_per']) {
                      case 'p_tag':
                      $tag = 'p';
                      break;
                      case 'div_tag':
                      $tag = 'div';
                      break;
                      case 'img_tag':
                      $tag = 'img';
                      break;
                      case 'custom_tag':
                      $tag = $ads['enter_your_tag'];
                      break;

                      default:
                      $tag = $ads['count_as_per'];
                      break;
                    }


                    $repeat_paragraph = (isset($ads['repeat_paragraph']) && !empty($ads['repeat_paragraph'])) ? $ads['repeat_paragraph'] : false;
                    if( strpos($content, "</blockquote>") || strpos($content, "</table>")){
                      $content =  remove_ad_from_content($content,$cusads,'',$paragraph_no,$repeat_paragraph);
                    }else{
                      $string_data = $content;
                      $pattern_ = "/<".$tag."(.*?)>/i";
                      if($pattern_){
                        if(preg_match_all($pattern_, $string_data, $matches)) {
                          $p_reg_match = $matches;
                        }
                        $finalmatch = $p_reg_match;
                        foreach ($finalmatch[0] as $key => $value) {
                          $openingtag =   $value;
                        }
                        $opening_p        = $openingtag;
                        $paragraphs       = explode( $opening_p, $content );
                        $p_count          = count($paragraphs);
                        $original_paragraph_no = $paragraph_no;
                        if($paragraph_no <= $p_count){
                          foreach ($paragraphs as $index => $paragraph) {
                            $opening_p        = isset($finalmatch[0][$index]) ? $finalmatch[0][$index] : null;
                            if ( trim( $paragraph ) || $index==0) {
                              $paragraphs[$index] .= '<'.$tag.'>';
                            }
                            if ( $paragraph_no == $index+1  ) {
                              //$index = ($index>0) ? $index-1 : $index;
                              if( strpos( $paragraphs[$index] , $opening_p ) > -1 ) {
                                $ad_c = $cusads.$opening_p;
                                $paragraphs[$index] = str_replace($opening_p,$ad_c,$paragraphs[$index]);
                              }else{
                                $paragraphs[$index] .= $opening_p;
                              }
                              if($repeat_paragraph){
                                $paragraph_no =  $original_paragraph_no+$paragraph_no;
                              }
                            }
                          }
                          $content = implode( '', $paragraphs );
                        }
                        else{
                          if($end_of_post){
                            $content = $content.$cusads;
                          }
                        }
                      }
                    }
                    break;
                    case 'amp_after_paragraph':
                    if( function_exists('adsensei_is_amp_endpoint') && adsensei_is_amp_endpoint()){
                      if(strpos( $content, '<!--OffBfLastPara-->' ) === false ) {
                        $repeat_paragraph = (isset($ads['repeat_paragraph']) && !empty($ads['repeat_paragraph'])) ? $ads['repeat_paragraph'] : false;
                        $paragraph_limit         = isset($ads['paragraph_limit']) ? $ads['paragraph_limit'] : '';
                        $insert_after         = isset($ads['insert_after']) ? $ads['insert_after'] : 1;

                        $closing_p        = '</p>';
                        $paragraphs       = explode( $closing_p, $content );
                        $p_count          = count($paragraphs);
                        $original_paragraph_no = $paragraph_no;

                        if($paragraph_no <= $p_count){
                          if($ads['ad_type']== 'group_insertion'){
                            $p_count =$p_count -1;
                            $cusads = '<!--CusGI'.$ads['ad_id'].'-->';
                            $next_insert_val = $insert_after;
                            $displayed_ad =1;
                            foreach ($paragraphs as $index => $paragraph) {
                              $addstart = false;
                              if ( trim( $paragraph ) ) {
                                $paragraphs[$index] .= $closing_p;
                              }

                              if((!empty($paragraph_limit) && $paragraph_limit < $displayed_ad) || ($index == $p_count )){
                                break;
                              }
                              if($index+1 == $next_insert_val){
                                $displayed_ad +=1;
                                $next_insert_val = $next_insert_val+$insert_after;
                                $addstart = true;
                              }
                              if($addstart){
                                $paragraphs[$index] .= $cusads;
                              }
                            }
                          }else{

                            foreach ($paragraphs as $index => $paragraph) {
                              if ( trim( $paragraph ) ) {
                                $paragraphs[$index] .= $closing_p;
                              }
                              if ( $paragraph_no == $index + 1 ) {
                                $paragraphs[$index] .= $cusads;
                                if($repeat_paragraph){
                                  $paragraph_no =  $original_paragraph_no+$paragraph_no;
                                }
                              }
                            }
                          }
                          $content = implode( '', $paragraphs );
                        }else{
                          if($end_of_post){
                            $content = $content.$cusads;
                          }
                        }
                      }
                    }
                    break;
                  }

                  $adsArrayCus[] = $i;
                }
                $i++;
              }

            }

            return $content;
          }

          /**
          * Filter default ads
          *
          * @global array $adsensei_options global settings
          * @global array $adsArrayCus List of ad id'S
          * @param string $content
          * @return string content
          */
          function adsensei_filter_default_ads( $content ) {

            global $adsensei_options, $adsArrayCus;

            $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);

            if( $off_default_ads ) { // If default ads are disabled
              return $content;
            }
            // Default Ads
            $adsArrayCus = array();

            // placeholder string for random ad
            $cusrnd = 'CusRnd';

            // placeholder string for custom ad spots
            $cusads = 'CusAds';

            // Beginning of Post
            $beginning_position_status = isset( $adsensei_options['pos1']['BegnAds'] ) ? true : false;
            $beginning_position_ad_id = isset( $adsensei_options['pos1']['BegnRnd'] ) ? $adsensei_options['pos1']['BegnRnd'] : 0;

            // Middle of Post
            $middle_position_status = isset( $adsensei_options['pos2']['MiddAds'] ) ? true : false;
            $middle_position_ad_id = isset( $adsensei_options['pos2']['MiddRnd'] ) ? $adsensei_options['pos2']['MiddRnd'] : 0;

            // End of Post
            $end_position_status = isset( $adsensei_options['pos3']['EndiAds'] ) ? true : false;
            $end_position_ad_id = isset( $adsensei_options['pos3']['EndiRnd'] ) ? $adsensei_options['pos3']['EndiRnd'] : 0;

            // After the more tag
            $more_position_status = isset( $adsensei_options['pos4']['MoreAds'] ) ? true : false;
            $more_position_ad_id = isset( $adsensei_options['pos4']['MoreRnd'] ) ? $adsensei_options['pos4']['MoreRnd'] : 0;

            // Right before the last paragraph
            $last_paragraph_position_status = isset( $adsensei_options['pos5']['LapaAds'] ) ? true : false;
            $last_paragraph_position_ad_id = isset( $adsensei_options['pos5']['LapaRnd'] ) ? $adsensei_options['pos5']['LapaRnd'] : 0;

            // After Paragraph option 1 - 3
            $number = 3; // number of paragraph ads | default value 3.
            $default = 5; // Position. Let's start with id 5
            for ( $i = 1; $i <= $number; $i++ ) {
              $key = $default + $i; // 6,7,8

              $paragraph['status'][$i] = isset( $adsensei_options['pos' . $key]['Par' . $i . 'Ads'] ) ? $adsensei_options['pos' . $key]['Par' . $i . 'Ads'] : 0; // Status - active | inactive
              $paragraph['id'][$i] = isset( $adsensei_options['pos' . $key]['Par' . $i . 'Rnd'] ) ? $adsensei_options['pos' . $key]['Par' . $i . 'Rnd'] : 0; // Ad id
              $paragraph['position'][$i] = isset( $adsensei_options['pos' . $key]['Par' . $i . 'Nup'] ) ? $adsensei_options['pos' . $key]['Par' . $i . 'Nup'] : 0; // Paragraph No
              $paragraph['end_post'][$i] = isset( $adsensei_options['pos' . $key]['Par' . $i . 'Con'] ) ? $adsensei_options['pos' . $key]['Par' . $i . 'Con'] : 0; // End of post - yes | no
            }
            // After Image ad
            $imageActive = isset( $adsensei_options['pos9']['Img1Ads'] ) ? $adsensei_options['pos9']['Img1Ads'] : false;
            $imageAdNo = isset( $adsensei_options['pos9']['Img1Rnd'] ) ? $adsensei_options['pos9']['Img1Rnd'] : false;
            $imageNo = isset( $adsensei_options['pos9']['Img1Nup'] ) ? $adsensei_options['pos9']['Img1Nup'] : false;
            $imageCaption = isset( $adsensei_options['pos9']['Img1Con'] ) ? $adsensei_options['pos9']['Img1Con'] : false;


            if( $beginning_position_ad_id == 0 ) {
              $b1 = $cusrnd;
            } else {
              $b1 = $cusads . $beginning_position_ad_id;
              array_push( $adsArrayCus, $beginning_position_ad_id );
            };

            if( $more_position_ad_id == 0 ) {
              $r1 = $cusrnd;
            } else {
              $r1 = $cusads . $more_position_ad_id;
              array_push( $adsArrayCus, $more_position_ad_id );
            };

            if( $middle_position_ad_id == 0 ) {
              $m1 = $cusrnd;
            } else {
              $m1 = $cusads . $middle_position_ad_id;
              array_push( $adsArrayCus, $middle_position_ad_id );
            };
            if( $last_paragraph_position_ad_id == 0 ) {
              $g1 = $cusrnd;
            } else {
              $g1 = $cusads . $last_paragraph_position_ad_id;
              array_push( $adsArrayCus, $last_paragraph_position_ad_id );
            };
            if( $end_position_ad_id == 0 ) {
              $b2 = $cusrnd;
            } else {
              $b2 = $cusads . $end_position_ad_id;
              array_push( $adsArrayCus, $end_position_ad_id );
            };
            for ( $i = 1; $i <= $number; $i++ ) {
              if( $paragraph['id'][$i] == 0 ) {
                $paragraph[$i] = $cusrnd;
              } else {
                $paragraph[$i] = $cusads . $paragraph['id'][$i];
                array_push( $adsArrayCus, $paragraph['id'][$i] );
              };
            }
            //wp_die(print_r($adsArrayCus));

            // Create the arguments for filter adsensei_filter_paragraphs
            $adsensei_args = array(
              'paragraph' => $paragraph,
              'cusads' => $cusads,
              'cusrnd' => $cusrnd,
              'AdsIdCus' => $adsArrayCus,
            );

            // Execute filter to add more paragraph ad spots
            $adsensei_filtered = apply_filters( 'adsensei_filter_paragraphs', $adsensei_args );

            // The filtered arguments
            $paragraph = $adsensei_filtered['paragraph'];

            // filtered list of ad spots
            $adsArrayCus = $adsensei_filtered['AdsIdCus'];

            // Create paragraph ads
            $number = 11;

            for ( $i = $number; $i >= 1; $i-- ) {
              if( !empty( $paragraph['status'][$i] ) ) {
                $sch = "</p>";
                $content = str_replace( "</P>", $sch, $content );


                /**
                * Get all blockquote if there are any
                */

                preg_match_all("/<blockquote>(.*?)<\/blockquote>/s", $content, $blockquotes);

                /**
                * Replace blockquotes with placeholder
                */
                if(!empty($blockquotes)){
                  $bId = 0;
                  foreach($blockquotes[0] as $blockquote){
                    $replace = "#ADSENSEIBLOCKQUOTE" . $bId . '#';
                    $content = str_replace(trim($blockquote), $replace, $content);
                    $bId++;
                  }
                }

                // Get paragraph tags
                $paragraphsArray = explode( $sch, $content );

                /**
                * Check if last element is empty and remove it
                */
                if(trim($paragraphsArray[count($paragraphsArray)-1]) == "") array_pop($paragraphsArray);

                if( ( int ) $paragraph['position'][$i] <= count( $paragraphsArray ) ) {
                  $content = implode( $sch, array_slice( $paragraphsArray, 0, $paragraph['position'][$i] ) ) . $sch . '<!--' . $paragraph[$i] . '-->' . implode( $sch, array_slice( $paragraphsArray, $paragraph['position'][$i] ) );
                } elseif( $paragraph['end_post'][$i] ) {
                  $content = implode( $sch, $paragraphsArray ) . '<!--' . $paragraph[$i] . '-->';
                }

                /**
                * Put back blockquotes into content
                */

                if(!empty($blockquotes)){
                  $bId = 0;
                  foreach($blockquotes[0] as $blockquote){
                    $search = '#ADSENSEIBLOCKQUOTE' . $bId . '#';
                    $content = str_replace($search, trim($blockquote), $content);
                    $bId++;
                  }
                }
              }
            }

            // Check if image ad is random one
            if( $imageAdNo == 0 ) {
              $imageAd = $cusrnd;
            } else {
              $imageAd = $cusads . $imageAdNo;
              array_push( $adsArrayCus, $imageAdNo );
            };


            // Beginning of post ad
            if( $beginning_position_status && strpos( $content, '<!--OffBegin-->' ) === false ) {
              $content = '<!--' . $b1 . '-->' . $content;
            }

            // Check if ad is middle one
            if( $middle_position_status && strpos( $content, '<!--OffMiddle-->' ) === false ) {
              if( substr_count( strtolower( $content ), '</p>' ) >= 2 ) {
                $closingTagP = "</p>";
                $content = str_replace( "</P>", $closingTagP, $content );
                $paragraphsArray = explode( $closingTagP, $content );

                /**
                * Check if last element is empty and remove it
                */
                if(trim($paragraphsArray[count($paragraphsArray)-1]) == "") array_pop($paragraphsArray);

                $nn = 0;
                $mm = strlen( $content ) / 2;
                for ( $i = 0; $i < count( $paragraphsArray ); $i++ ) {
                  $nn += strlen( $paragraphsArray[$i] ) + 4;
                  if( $nn > $mm ) {
                    if( ($mm - ($nn - strlen( $paragraphsArray[$i] ))) > ($nn - $mm) && $i + 1 < count( $paragraphsArray ) ) {
                      $paragraphsArray[$i + 1] = '<!--' . $m1 . '-->' . $paragraphsArray[$i + 1];
                    } else {
                      $paragraphsArray[$i] = '<!--' . $m1 . '-->' . $paragraphsArray[$i];
                    }
                    break;
                  }
                }

                $content = implode( $closingTagP, $paragraphsArray );
              }
            }

            // End of Post ad
            if( $end_position_status && strpos( $content, '<!--OffEnd-->' ) === false ) {
              $content = $content . '<!--' . $b2 . '-->';
            }



            // Check if ad is after "More Tag"
            if( $more_position_status && strpos( $content, '<!--OffAfMore-->' ) === false ) {
              $mmr = '<!--' . $r1 . '-->';
              $postid = get_the_ID();
              $content = str_replace( '<span id="more-' . $postid . '"></span>', $mmr, $content );
            }

            // Right before last paragraph ad
            if( $last_paragraph_position_status && strpos( $content, '<!--OffBfLastPara-->' ) === false ) {
              $closingTagP = "</p>";
              $content = str_replace( "</P>", $closingTagP, $content );
              $paragraphsArray = explode( $closingTagP, $content );


              /**
              * Check if last element is empty and remove it
              */
              if(trim($paragraphsArray[count($paragraphsArray)-1]) == "") array_pop($paragraphsArray);


              //if( count( $paragraphsArray ) > 2 && !strpos($paragraphsArray[count( $paragraphsArray ) - 1], '</blockquote>')) {
              if( count( $paragraphsArray ) > 2) {
                $content = implode( $closingTagP, array_slice( $paragraphsArray, 0, count( $paragraphsArray ) - 1 ) ) . '<!--' . $g1 . '-->' . $closingTagP . $paragraphsArray[count( $paragraphsArray ) - 1];
              }

            }

            // After Image ad
            if( $imageActive ) {

              // Sanitation
              $imgtag = "<img";
              $delimiter = ">";
              $caption = "[/caption]";
              $atag = "</a>";
              $content = str_replace( "<IMG", $imgtag, $content );
              $content = str_replace( "</A>", $atag, $content );

              // Get all images in content
              $imagesArray = explode( $imgtag, $content );
              // Modify Image ad
              if( ( int ) $imageNo < count( $imagesArray ) ) {
                //Get all tags
                $tagsArray = explode( $delimiter, $imagesArray[$imageNo] );
                if( count( $tagsArray ) > 1 ) {
                  $captionArray = explode( $caption, $imagesArray[$imageNo] );
                  $ccp = ( count( $captionArray ) > 1 ) ? strpos( strtolower( $captionArray[0] ), '[caption ' ) === false : false;
                  $imagesArrayAtag = explode( $atag, $imagesArray[$imageNo] );
                  $cdu = ( count( $imagesArrayAtag ) > 1 ) ? strpos( strtolower( $imagesArrayAtag[0] ), '<a href' ) === false : false;
                  // Show ad after caption
                  if( $imageCaption && $ccp ) {
                    $imagesArray[$imageNo] = implode( $caption, array_slice( $captionArray, 0, 1 ) ) . $caption . "\r\n" . '<!--' . $imageAd . '-->' . "\r\n" . implode( $caption, array_slice( $captionArray, 1 ) );
                  } else if( $cdu ) {
                    $imagesArray[$imageNo] = implode( $atag, array_slice( $imagesArrayAtag, 0, 1 ) ) . $atag . "\r\n" . '<!--' . $imageAd . '-->' . "\r\n" . implode( $atag, array_slice( $imagesArrayAtag, 1 ) );
                  } else {
                    $imagesArray[$imageNo] = implode( $delimiter, array_slice( $tagsArray, 0, 1 ) ) . $delimiter . "\r\n" . '<!--' . $imageAd . '-->' . "\r\n" . implode( $delimiter, array_slice( $tagsArray, 1 ) );
                  }
                }
                $content = implode( $imgtag, $imagesArray );
              }
            }

            return $content;
          }
          /**
          * Sanitize content and return it cleaned
          *
          * @param string $content
          * @return string
          */
          function adsensei_sanitize_content($content){

            /* ... Tidy up content ... */
            // Replace all <p></p> tags with placeholder ##QA-TP1##
            $content = str_replace( "<p></p>", "##QA-TP1##", $content );

            // Replace all <p>&nbsp;</p> tags with placeholder ##QA-TP2##
            $content = str_replace( "<p>&nbsp;</p>", "##QA-TP2##", $content );

            return $content;
          }



          /**
          * Parse random ads which are created from quicktag <!--RndAds-->
          *
          * @global array $adsArray
          * @global int $visibleContentAds
          * @return content
          */
          function adsensei_parse_random_quicktag_ads($content){
            global $adsArray, $visibleContentAds, $adsensei_options;
            $maxAds = isset($adsensei_options['maxads']) ? $adsensei_options['maxads'] : 10;
            /*
            * Replace RndAds Random Ads
            */
            if(!is_array($adsArray)) { $adsArray = [];}
            $content=  str_replace('[adsensei id=RndAds]', '<!--RndAds-->', $content);
            if( strpos( $content, '<!--RndAds-->' ) !== false && is_singular() ) {
              $adsArrayTmp = array();
              shuffle( $adsArray );
              for ( $i = 1; $i <= $maxAds - $visibleContentAds; $i++ ) {
                if( $i <= count( $adsArray ) ) {
                  array_push( $adsArrayTmp, $adsArray[$i - 1] );
                }
              }
              $tcx = count( $adsArrayTmp );
              $tcy = substr_count( $content, '<!--RndAds-->' );
              for ( $i = $tcx; $i <= $tcy - 1; $i++ ) {
                array_push( $adsArrayTmp, -1 );
              }
              shuffle( $adsArrayTmp );
              for ( $i = 1; $i <= $tcy; $i++ ) {
                $tmp = $adsArrayTmp[0];
                $content = adsensei_replace_ads( $content, 'RndAds', $adsArrayTmp[0] );
                $adsArrayTmp = adsensei_del_element( $adsArrayTmp, 0 );
                if( $tmp != -1 ) {
                  $visibleContentAds += 1;
                };
                //adsensei_set_ad_count_content();
                //if( adsensei_ad_reach_max_count() ) {
                if( $visibleContentAds >= adsensei_get_max_allowed_post_ads( $content )  ) {
                  $content = adsensei_clean_tags( $content );
                  return $content;
                }
              }
            }

            return $content;
          }

          /**
          * Parse random default ads which can be enabled from general settings
          *
          * @global array $adsArray
          * @global int $visibleContentAds
          * @return string
          */
          function adsensei_parse_random_ads_new($content) {
            $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);
            if( $off_default_ads ) {
              return $content;
            }
            $selected_ads =array();
            $random_ads_list_after =array();

            $number_rand_ads = substr_count( $content, '<!--CusRnd' );
            for ( $i = 0; $i <= $number_rand_ads - 1; $i++ ) {
              preg_match("#<!--CusRnd(.+?)-->#si", $content, $match);
              $ad_id = $match['1'];
              if(!empty($ad_id)){
                $ad_meta = get_post_meta($ad_id, '',true);
              }
              $random_ads_list = unserialize($ad_meta['random_ads_list']['0']);
              if (!is_array($random_ads_list)) return $content;
              $temp_array =array();
              foreach ($random_ads_list as $radom_ad ) {
                if (isset($radom_ad['value'])){
                  $temp_array[] = $radom_ad['value'];
                }
              }
              $random_ads_list_after =  array_diff($temp_array, $selected_ads);
              $keys = array_keys($random_ads_list_after);
              shuffle($keys);
              $randomid = $random_ads_list_after[$keys[0]];
              $selected_ads[] = $randomid;
              $enabled_on_amp = (isset($ad_meta['enabled_on_amp'][0]))? $ad_meta['enabled_on_amp'][0]: '';
              $content = adsensei_replace_ads_new( $content, 'CusRnd' . $ad_id, $randomid,$enabled_on_amp);
            }
            return $content;

          }



          /**
          * Parse random default ads which can be enabled from general settings
          *
          * @global array $adsArray
          * @global int $visibleContentAds
          * @return string
          */
          function adsensei_parse_random_ads($content) {
            global $adsRandom, $visibleContentAds;

            $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);
            if( $off_default_ads ) { // disabled default ads
              return $content;
            }

            if( strpos( $content, '<!--CusRnd-->' ) !== false && is_singular() ) {

              $tcx = count( $adsRandom );
              // How often is a random ad appearing in content
              $number_rand_ads = substr_count( $content, '<!--CusRnd-->' );

              for ( $i = $tcx; $i <= $number_rand_ads - 1; $i++ ) {
                array_push( $adsRandom, -1 );
              }
              shuffle( $adsRandom );
              //wp_die(print_r($adsRandom));
              //wp_die($adsRandom[0]);
              for ( $i = 1; $i <= $number_rand_ads; $i++ ) {
                $content = adsensei_replace_ads( $content, 'CusRnd', $adsRandom[0] );
                $adsRandom = adsensei_del_element( $adsRandom, 0 );
                $visibleContentAds += 1;
                //adsensei_set_ad_count_content();
                //if( adsensei_ad_reach_max_count() ) {
                if( $visibleContentAds >= adsensei_get_max_allowed_post_ads( $content )  ) {
                  $content = adsensei_clean_tags( $content );
                  return $content;
                }
              }
            }

            return $content;
          }

          /**
          * Parse Quicktags
          *
          * @global array $adsArray
          * @param string $content
          * @return string
          */
          function adsensei_parse_quicktags($content){
            global $adsArray, $visibleContentAds;
            //print_r(count($adsArray));
            if (!is_array($adsArray)){
              return $content;
            }
            $idx = 0;
            for ( $i = 1; $i <= count( $adsArray ); $i++ ) {
              if( strpos( $content, '<!--Ads' . $adsArray[$idx] . '-->' ) !== false ) {
                $content = adsensei_replace_ads( $content, 'Ads' . $adsArray[$idx], $adsArray[$idx] );
                //$adsArray = adsensei_del_element( $adsArray, $idx );
                $visibleContentAds += 1;
                $idx +=1;
                //adsensei_set_ad_count_content();
                if( $visibleContentAds >= adsensei_get_max_allowed_post_ads( $content )  ) {
                  $content = adsensei_clean_tags( $content );
                  return $content;
                }
              } else {
                $idx += 1;
              }
            }

            return $content;
          }

          /**
          * Parse default ads Beginning/Middle/End/Paragraph Ads1-10
          *
          * @param string $content
          * @return string
          */
          function adsensei_parse_default_ads( $content ) {
            global $adsArrayCus, $adsRandom, $adsArray, $visibleContentAds;

            $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);

            if( $off_default_ads ) { // disabled default ads
              return $content;
            }
            // Create the array which contains the random ads
            $adsRandom = $adsArray;

            //        echo '<pre>';
            //        echo 'adsArrayCus: ';
            //        print_r($adsArrayCus);
            //        echo 'adsArray: ';
            //        print_r( $adsArray );
            //        echo '</pre>';

            for ( $i = 0; $i <= count( $adsArrayCus ); $i++ ) {

              if( isset( $adsArrayCus[$i] ) && strpos( $content, '<!--CusAds' . $adsArrayCus[$i] . '-->' ) !== false && in_array( $adsArrayCus[$i], $adsArray ) ) {

                $content = adsensei_replace_ads( $content, 'CusAds' . $adsArrayCus[$i], $adsArrayCus[$i] );

                // Create array $adsRandom for adsensei_parse_random_ads() parsing functions to make sure that the random function
                // is never using ads that are already used on static ad spots which are generated with adsensei_parse_default_ads()
                if ($i == 0){
                  $adsRandom = adsensei_del_element($adsRandom, array_search($adsArrayCus[$i], $adsRandom));
                }else{
                  $adsRandom = adsensei_del_element($adsRandom, array_search($adsArrayCus[$i-1], $adsRandom));
                }

                $visibleContentAds += 1;
                //adsensei_set_ad_count_content();
                //if( adsensei_ad_reach_max_count() || $visibleContentAds >= adsensei_get_max_allowed_post_ads( $content )  ) {
                //wp_die(adsensei_get_max_allowed_post_ads( $content ));

                if( $visibleContentAds >= adsensei_get_max_allowed_post_ads( $content )  ) {

                  $content = adsensei_clean_tags( $content );
                }
              }
            }
            return $content;
          }
          function adsensei_parse_popup_ads($content) {
            if(!isset($_COOKIE['adsensei_popup'])){

              preg_match("#<!--pop_up_ads(.+?)-->#si", $content, $match);
              if (!isset($match['1'])) {
                return $content;
              }
              $ad_id = $match['1'];
              if(!empty($ad_id)){
                $ad_meta = get_post_meta($ad_id, '',true);
              }
              $ads_list = !empty($ad_meta['ads_list']['0']) ? unserialize($ad_meta['ads_list']['0']) : "" ;

              if (!is_array($ads_list)) return $content;
              $temp_array =array();
              foreach ($ads_list as $ad ) {
                if (isset($ad['value'])){
                  $temp_array[] = $ad['value'];
                }
              }

              $ad_code = array_rand($temp_array);

              $popup_type                    =  isset($ad_meta['popup_type'][0]) ? $ad_meta['popup_type'][0] : '';
              $everytime_popup       =  (isset($ad_meta['everytime_popup'][0]) && !empty($ad_meta['everytime_popup'][0])) ? $ad_meta['everytime_popup'][0] : 0;
              $specific_time_interval_sec       =  (isset($ad_meta['specific_time_interval_sec'][0]) && !empty($ad_meta['specific_time_interval_sec'][0])) ? $ad_meta['specific_time_interval_sec'][0] : 0;
              $on_scroll_popup_percentage       =  (isset($ad_meta['on_scroll_popup_percentage'][0]) && !empty($ad_meta['on_scroll_popup_percentage'][0])) ? $ad_meta['on_scroll_popup_percentage'][0] : 0;


              $adsresultset = array();
              if( $ads_list ){
                foreach ($temp_array as $post_ad_id){
                  $ad_meta_group = get_post_meta($post_ad_id, '',true);
                  if( get_post_status($post_ad_id) !== 'publish' ) {
                    continue;
                  }
                  $adsresultset[] = array(
                    'ad_id'                     => $post_ad_id,
                    'ad_type'                   => $ad_meta_group['ad_type'],
                    'ad_adsense_type'           => $ad_meta_group['adsense_type'],
                    'ad_data_client_id'         => $ad_meta_group['g_data_ad_client'][0],
                    'ad_data_ad_slot'           => $ad_meta_group['g_data_ad_slot'][0],
                    // 'ad_custom_code'            => $ad_meta_group['custom_code'],
                    'width'                     => $ad_meta_group['g_data_ad_width'],
                    'height'                    => $ad_meta_group['g_data_ad_height'],
                    'code'                      => $ad_meta_group['code'],
                    'network_code'              => $ad_meta_group['network_code'],
                    'ad_unit_name'              => $ad_meta_group['ad_unit_name'],
                    // 'block_id'                  => $ad_meta_group['block_id'],
                    'data_container'            => $ad_meta_group['data_container'],
                    'data_js_src'               => $ad_meta_group['data_js_src'],
                    'data_cid'                  => $ad_meta_group['data_cid'],
                    'data_crid'                 => $ad_meta_group['data_crid'],
                    'taboola_publisher_id'      => $ad_meta_group['taboola_publisher_id'],
                    'mediavine_site_id'         => $ad_meta_group['mediavine_site_id'],
                    'outbrain_widget_ids'       => $ad_meta_group['outbrain_widget_ids'],
                    'image_redirect_url'        => $ad_meta_group['image_redirect_url'],
                    'ad_image'                  => $ad_meta_group['image_src'],
                    'mobile_ad_image'           => $ad_meta_group['image_mobile_src'],

                  ) ;
                }
                $response['adsensei_group_id'] = $ad_id;
                $response['adsensei_popup_type']           = 'popupads';
                $response['specific_time_popup']           = $specific_time_interval_sec;
                $response['on_scroll_popup']           = $on_scroll_popup_percentage;
                $response['ads'] = $adsresultset;

                $arr = array(
                  'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
                  'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
                  'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
                  'float:none;margin:%1$dpx;');

                  $adsalign = isset($adsensei_options['ads']['ad' . $ad_id]['align']) ? $adsensei_options['ads']['ad' . $ad_id]['align'] : 0; // default
                  $adsmargin = isset( $adsensei_options['ads']['ad' . $ad_id]['margin'] ) ? $adsensei_options['ads']['ad' . $ad_id]['margin'] : '0'; // default
                  $margin = sprintf( $arr[( int ) $adsalign], $adsmargin );

                  // Do not create any inline style on AMP site
                  $style = '' ;
                  $popups_data = '';
                  if( $popup_type == "everytime_popup" ){
                    $style = "display:block";
                    $popups_data = '';
                  }
                  if( $popup_type == "specific_time_popup" ){
                    $style = "display:none";
                    $popups_data = "data-timer=".$specific_time_interval_sec."";
                  }
                  if( $popup_type == "on_scroll_popup" ){
                    $style = "display:none";
                    $popups_data = "data-percent=".$on_scroll_popup_percentage."";
                  }

                  $code = "\n" . '<!-- WP ADSENSEI v. ' . ADSENSEI_VERSION . '  popup Ad -->' . "\n" .
                  '<div class="adsensei-location adsensei-popupad ad_' . esc_attr($ad_id) . '" id="adsensei-ad'. esc_attr($ad_id) .'" '.$popups_data.' data-popuptype="'.$popup_type.'" style="' . $style . '">' . "\n";
                  $code .='<div class="adsensei-groups-ads-json"  data-json="'. esc_attr(json_encode($response)).'">';
                  $code .='</div>';

                  $code .='<div style="display:none;" class="adsensei_ad_container__pre"></div><div data-id="'.esc_attr($ad_id).'" class="adsensei adsensei_ad_container_">

                  </div>';

                  $code .= '</div>' . "\n";

                  $cont = explode('<!--CusRot'.$ad_id.'-->', $content, 2);

                  $content =  $cont[0].$code;
                  $js_dir = ADSENSEI_PLUGIN_URL . 'assets/js/';

                  // Use minified libraries if SCRIPT_DEBUG is turned off
                  $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';

                  // These have to be global
                  wp_enqueue_script( 'wp_qds_popup', $js_dir . 'wp_qds_popup' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );

                }else{
                  $content = adsensei_replace_ads_new( $content, 'CusRot' . $ad_id, $temp_array[$ad_code],$enabled_on_amp);
                }
              }
              return  $content ;
            }
            function adsensei_parse_video_ads($content) {
              if(!isset($_COOKIE['adsensei_video'])){

                preg_match("#<!--video_ad(.+?)-->#si", $content, $match);
                if (!isset($match['1'])) {
                  return $content;
                }
                $ad_id = $match['1'];

                if(!empty($ad_id)){
                  $ad_meta = get_post_meta($ad_id, '',true);
                }
                $video_ad_type                    =  isset($ad_meta['video_ad_type'][0]) ? $ad_meta['video_ad_type'][0] : '';
                $specific_time_interval_sec_video       =  (isset($ad_meta['specific_time_interval_sec_video'][0]) && !empty($ad_meta['specific_time_interval_sec_video'][0])) ? $ad_meta['specific_time_interval_sec_video'][0] : 0;
                $position =  (isset($ad_meta['video_ad_type_position'][0]) && !empty($ad_meta['video_ad_type_position'][0])) ? $ad_meta['video_ad_type_position'][0] : 0;
                $on_scroll_video_percentage       =  (isset($ad_meta['on_scroll_video_percentage'][0]) && !empty($ad_meta['on_scroll_video_percentage'][0])) ? $ad_meta['on_scroll_video_percentage'][0] : 0;
                $V_image_src       =  (isset($ad_meta['image_src'][0]) && !empty($ad_meta['image_src'][0])) ? $ad_meta['image_src'][0] : 0;
                $V_redirect       =  (isset($ad_meta['image_redirect_url'][0]) && !empty($ad_meta['image_redirect_url'][0])) ? $ad_meta['image_redirect_url'][0] : '';
                $V_image_width       =  (isset($ad_meta['video_width'][0]) && !empty($ad_meta['video_width'][0])) ? $ad_meta['video_width'][0] : '350';
                $V_image_height       =  (isset($ad_meta['video_height'][0]) && !empty($ad_meta['video_height'][0])) ? $ad_meta['video_height'][0] :'auto';


                $adsresultset = array();
                if( $ad_meta ){
                  foreach ($ad_meta as $post_ad_id){
                    $ad_meta_group = get_post_meta($post_ad_id, '',true);

                    if( get_post_status($post_ad_id) !== 'publish' ) {
                      continue;
                    }
                    $adsresultset[] = array(
                      'ad_id'                     => $post_ad_id,
                      'ad_type'                   => 'video_ads',
                    ) ;
                  }
                  $response['adsensei_group_id'] = $ad_id;
                  $response['adsensei_video_type']           = 'videoads';
                  $response['specific_time_interval_sec_video']           = $specific_time_interval_sec_video;
                  $response['on_scroll_video_percentage']           = $on_scroll_video_percentage;
                  $response['viedo_url']           = $V_image_src;
                  //$response['viedo_height']           = $V_image_height;
                  $response['viedo_width']           = $V_image_width;
                  $response['viedo_position']           = $position;
                  $response['ads'] = $adsresultset;

                  $arr = array(
                    'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
                    'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
                    'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
                    'float:none;margin:%1$dpx;');

                    $adsalign = isset($adsensei_options['ads']['ad' . $ad_id]['align']) ? $adsensei_options['ads']['ad' . $ad_id]['align'] : 0; // default
                    $adsmargin = isset( $adsensei_options['ads']['ad' . $ad_id]['margin'] ) ? $adsensei_options['ads']['ad' . $ad_id]['margin'] : '0'; // default
                    $margin = sprintf( $arr[( int ) $adsalign], $adsmargin );

                    // Do not create any inline style on AMP site
                    $style = '' ;
                    $videoad_data = '';
                    if( $video_ad_type == "specific_time_video" ){
                      $style = "display:none";
                      $videoad_data = "data-position=".$position." data-timer=".$specific_time_interval_sec_video."";
                    }
                    if( $video_ad_type == "after_scroll_video" ){
                      $style = "display:none";
                      $videoad_data = "data-position=".$position." data-percent=".$on_scroll_video_percentage."";
                    }

                    $code = "\n" . '<!-- WP ADSENSEI v. ' . ADSENSEI_VERSION . '  popup Ad -->' . "\n" .
                    '<div class="video_main"><div class="adsensei-location adsensei-video ad_' . esc_attr($ad_id) . '" id="adsensei-ad'. esc_attr($ad_id) .'" '.$videoad_data.' data-videotype="'.$video_ad_type.'" data-redirect="'.esc_url($V_redirect).'" style="' . $style . '">' . "\n";
                    $code .='<div class="adsensei-video-ads-json"  data-json="'. esc_attr(json_encode($response)).'">';
                    $code .='</div>';

                    $code .='<div data-id="'.esc_attr($ad_id).'" class="adsensei adsensei_ad_container_video">

                    </div>';

                    $code .= '</div>' . "\n";
                    $code .= '</div>' . "\n";

                    $cont = explode('<!--CusRot'.$ad_id.'-->', $content, 2);

                    $content =  $cont[0].$code;
                    $js_dir = ADSENSEI_PLUGIN_URL . 'assets/js/';

                    // Use minified libraries if SCRIPT_DEBUG is turned off
                    $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';

                    // These have to be global
                    wp_enqueue_script( 'wp_qds_video', $js_dir . 'wp_qds_video' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );

                  }
                }
                return  $content ;
              }
              function adsensei_parse_default_ads_new( $content ) {
                global $adsArrayCus, $adsRandom, $adsArray;

                $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);

                if( $off_default_ads ) { // disabled default ads
                  return $content;
                }

                $number_rand_ads = substr_count( $content, '<!--CusAds' );
                for ( $i = 0; $i <= $number_rand_ads - 1; $i++ ) {
                  preg_match("#<!--CusAds(.+?)-->#si", $content, $match);
                  $ad_id = isset($match['1'])?$match['1']:'';
                  if( strpos( $content, '<!--CusAds' . $ad_id . '-->' ) !== false )  {

                    $content = adsensei_replace_ads_new( $content, 'CusAds' . $ad_id, $ad_id );
                  }
                }
                return $content;
              }

              /**
              * Replace ad code in content
              *
              * @global type $adsensei_options
              * @param string $content
              * @param string $quicktag Quicktag
              * @param string $id id of the ad
              * @return type
              */
              function adsensei_replace_ads($content, $quicktag, $id) {
                global $adsensei_options;


                if( strpos($content,'<!--'.$quicktag.'-->')===false ) {
                  return $content;
                }


                if ($id != -1) {

                  $code = !empty($adsensei_options['ads']['ad' . $id ]['code']) ? $adsensei_options['ads']['ad' . $id ]['code'] : '';
                  $style = adsensei_get_inline_ad_style($id);

                  $adscode =
                  "\n".'<div style="'.esc_attr($style).'">'."\n".
                  adsensei_render_ad('ad'.$id, $code)."\n".
                  '</div>'. "\n";


                } else {
                  $adscode ='';
                }
                $cont = explode('<!--'.$quicktag.'-->', $content, 2);

                return $cont[0].$adscode.$cont[1];
              }

              /**
              * Replace ad code in content
              *
              * @global type $adsensei_options
              * @param string $content
              * @param string $quicktag Quicktag
              * @param string $id id of the ad
              * @return type
              */
              function adsensei_replace_ads_new($content, $quicktag, $id,$ampsupport='') {
                global $adsensei_options;

                if( strpos($content,'<!--'.$quicktag.'-->')===false ) {
                  return $content;
                }
                $flag = true;
                // if it was sticky ad return empty
                if (isset($ad_meta['adsense_ad_type'][0]) && $ad_meta['adsense_ad_type'][0] == 'adsense_sticky_ads' ){
                  $flag = false;
                }
                $ad_meta = get_post_meta($id, '',true);
                if (isset($ad_meta['code'][0])&& $flag) {
                  if(!empty($ad_meta['code'][0])){

                    $code = '';
                    if ( isset($adsensei_options['lazy_load_global']) && $adsensei_options['lazy_load_global']===true && strpos($ad_meta['code'][0], 'class="adsbygoogle"') !== false) {
                      $id_name = "adsensei-".esc_attr($id)."-place";
                      $code .= '<div id="'.esc_attr($id_name).'" class="adsensei-ll">' ;
                    }
                    $code .=   $ad_meta['code'][0];
                    if ( isset($adsensei_options['lazy_load_global']) && $adsensei_options['lazy_load_global']===true && strpos($ad_meta['code'][0], 'class="adsbygoogle"') !== false) {
                      $check_script_tag =    preg_match('#<script(.*?)src=(.*?)>(.*?)</script>#is', $code);
                      if($check_script_tag){
                        $code = preg_replace('#<script(.*?)src=(.*?)>(.*?)</script>#is', '', $code);
                      }
                      $code = str_replace( 'class="adsbygoogle"', '', $code );
                      $code = str_replace( '></ins>', '><span>Loading...</span></ins></div>', $code );
                      $code1 = 'instant= new adsenseLoader( \'#adsensei-' . esc_attr($id) . '-place\', {
                        onLoad: function( ad ){
                          if (ad.classList.contains("adsensei-ll")) {
                            ad.classList.remove("adsensei-ll");
                          }
                        }
                      });';
                      $code = str_replace( '(adsbygoogle = window.adsbygoogle || []).push({});', $code1, $code );
                      }
                    }else{
                      $code ='';
                    }
                    $style = adsensei_get_inline_ad_style_new($id);

                    $adscode =
                    "\n".'<div style="'.esc_attr($style).'">'."\n".
                    adsensei_render_ad($ad_meta['adsensei_ad_old_id'][0], $code,'',$ampsupport)."\n".
                    '</div>'. "\n";

                  } else {
                    $adscode ='';
                  }
                  $cont = explode('<!--'.$quicktag.'-->', $content, 2);
                  if(isset($adsensei_options['tcf_2_integration']) && !empty($adsensei_options['tcf_2_integration']) && $adsensei_options['tcf_2_integration'] && function_exists( 'run_qc_choice' ) ){
                    $adscode= sprintf(
                      '<script type="text/plain" data-tcf="waiting-for-consent" data-id="%d">%s</script>',
                      $id,
                      base64_encode( $adscode )
                    );
                  }
                  $content =  $cont[0].$adscode.$cont[1];
                  $adscode = apply_filters("wp_adsensei_final_ad_data", $adscode);

                  return  $content;
                }

                /**
                * Get ad inline style
                *
                * @global arr $adsensei_options
                * @param int $id id of the ad
                * @return string
                */
                function adsensei_get_inline_ad_style( $id ) {
                  global $adsensei_options;

                  if( empty($id) ) {
                    return '';
                  }

                  // Basic style
                  $styleArray = array(
                    'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
                    'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
                    'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
                    'float:none;margin:%1$dpx;');

                    // Alignment
                    $adsalign = ( int )$adsensei_options['ads']['ad' . $id]['align'];

                    // Margin
                    $adsmargin = isset( $adsensei_options['ads']['ad' . $id]['margin'] ) ? $adsensei_options['ads']['ad' . $id]['margin'] : '3'; // default option = 3
                    $margin = sprintf( $styleArray[$adsalign], $adsmargin );

                    //wp_die($adsensei_options['ads']['ad' . $id]['margin']);
                    //wp_die('ad'.$id);

                    // Do not create any inline style on AMP site
                    $style =   apply_filters( 'adsensei_filter_margins', $margin, 'ad' . $id );

                    return $style;
                  }

                  function adsensei_get_inline_ad_style_new( $id ) {
                    global $adsensei_options;

                    if( empty($id) ) {
                      return '';
                    }
                    $ad_meta = get_post_meta($id, '',true);

                    // Basic style
                    $styleArray = array(
                      'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
                      'float:none;text-align:center;',
                      'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
                      'float:none;margin:%1$dpx %2$dpx %3$dpx %4$dpx;');

                      $padding_styleArray = array(
                        'padding:%1$dpx %1$dpx %1$dpx 0;',
                        'padding:%1$dpx 0 %1$dpx 0;',
                        'padding:%1$dpx 0 %1$dpx %1$dpx;',
                        'padding:%1$dpx;');

                        // Alignment
                        $adsalign = ( int )$ad_meta['align'][0];


                        // Margin
                        $adsmargin = isset( $ad_meta['margin'][0] ) ? $ad_meta['margin'][0] : '3'; // default option = 3
                        $adsmargin_right = isset( $ad_meta['margin_right'][0] ) ? $ad_meta['margin_right'][0] : '3'; // default option = 3
                        $adsmargin_bottom = isset( $ad_meta['margin_bottom'][0] ) ? $ad_meta['margin_bottom'][0] : '3'; // default option = 3
                        $adsmargin_left = isset( $ad_meta['margin_left'][0] ) ? $ad_meta['margin_left'][0] : '3'; // default option = 3
                        $margin = sprintf( $styleArray[$adsalign], $adsmargin, $adsmargin_right, $adsmargin_bottom, $adsmargin_left );

                        // Padding
                        $adspadding = isset( $ad_meta['padding'][0] ) ? $ad_meta['padding'][0] : '0'; // default option = 0
                        $padding = sprintf( $padding_styleArray[$adsalign], $adspadding );

                        // Do not create any inline style on AMP site
                        $style =  apply_filters( 'adsensei_filter_margins', $margin, 'ad' . $id ) ;

                        return $style.$padding;
                      }

                      /**
                      * Revert content to original content any remove any processing helper strings
                      *
                      * @global int $visibleContentAds
                      * @global array $adsArray
                      * @global array $adsensei_options
                      * @global int $ad_count
                      * @param string $content
                      * @param boolean $trimonly
                      *
                      * @return string content
                      */
                      function adsensei_clean_tags($content, $trimonly = false) {
                        global $visibleContentAds;
                        global $adsArray;
                        global $adsensei_options;
                        global $ad_count;

                        $tagnames = array('EmptyClear','RndAds','NoAds','OffDef','OffAds','OffWidget','OffBegin','OffMiddle','OffEnd','OffBfMore','OffAfLastPara','CusRnd');

                        for($i=1;$i<=10;$i++) {
                          array_push($tagnames, 'CusAds'.$i);
                          array_push($tagnames, 'Ads'.$i);
                        };


                        foreach ($tagnames as $tags) {
                          if(strpos($content,'<!--'.$tags.'-->')!==false || $tags=='EmptyClear') {
                            if($trimonly) {
                              $content = str_replace('<p><!--'.$tags.'--></p>', '<!--'.$tags.'-->', $content);
                            }else{
                              $content = str_replace(array('<p><!--'.$tags.'--></p>','<!--'.$tags.'-->'), '', $content);
                              $content = str_replace("##QA-TP1##", "<p></p>", $content);
                              $content = str_replace("##QA-TP2##", "<p>&nbsp;</p>", $content);
                            }
                          }
                        }
                        if(!$trimonly && (is_single() || is_page()) ) {
                          $visibleContentAds = 0;
                          $adsArray = array();
                        }
                        return $content;
                      }



                      /**
                      * Remove element from array
                      *
                      * @param array $paragraphsArrayay
                      * @param int $idx key to remove from array
                      * @return array
                      */
                      function adsensei_del_element($array, $idx) {
                        $copy = array();
                        for( $i=0; $i<count($array) ;$i++) {
                          if ( $idx != $i ) {
                            array_push($copy, $array[$i]);
                          }
                        }
                        return $copy;
                      }

                      /**
                      * echo ad before/after posts in loops on archive pages
                      *
                      * @since 1.2.1
                      * @param arr $post post object
                      * @param WP_Query $wp_query query object
                      */
                      function adsensei_in_between_loop( $post, $wp_query = null ) {
                        global $adsensei_new_interface_ads;

                        $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

                        if ( ! $wp_query instanceof WP_Query || is_feed() || ( is_admin() && ! $is_ajax )  ) {
                          return;
                        }

                        if( ! isset( $wp_query->current_post )) {
                          return;
                        };

                        // don’t inject into main query on single pages.
                        if( $wp_query->is_main_query() && is_single() ){
                          return;
                        }
                        if ( $wp_query->is_singular() || ! $wp_query->in_the_loop   ) {
                          return;
                        }

                        // check if the loop is outside of wp_head, but only on non-AJAX calls.
                        if  ( ! is_admin() && ! did_action( 'wp_head' ) ) {
                          return;
                        }


                        $curr_index = $wp_query->current_post ; // normalize index
                        static $handled_indexes = array();
                        if ( $wp_query->is_main_query() ) {
                          if ( in_array( $curr_index, $handled_indexes ) ) {
                            return;
                          }
                          $handled_indexes[] = $curr_index;
                        }
                        if(empty($adsensei_new_interface_ads)){
                          if(isset($adsensei_ads) && !empty($adsensei_ads))
                          {
                            $adsensei_new_interface_ads = $adsensei_ads;
                          }
                          else
                          {
                            require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
                            $api_service = new ADSENSEI_Ad_Setup_Api_Service();
                            $adsensei_new_interface_ads = $api_service->getAdDataByParam('adsensei-ads');
                          }

                        }else{
                          $adsensei_ads = $adsensei_new_interface_ads;
                        }

                        if(isset($adsensei_ads['posts_data'])){
                          foreach($adsensei_ads['posts_data'] as $key => $value){
                            $ads =$value['post_meta'];
                            if($value['post']['post_status']== 'draft'){
                              continue;
                            }
                            $display_after_every = (isset($ads['display_after_every']) && !empty($ads['display_after_every'])) ? $ads['display_after_every'] : false;
                            if( isset($ads['position'] ) && $ads['position'] == 'amp_ads_in_loops' && (isset($ads['ads_loop_number']) && ($ads['ads_loop_number'] == $curr_index || ($display_after_every && $curr_index!== 0 && ($curr_index % $ads['ads_loop_number'] == 0))))){
                              $tag= '<!--CusAds'.$ads['ad_id'].'-->';
                              if(isset($ads['visibility_include']))
                              $ads['visibility_include'] = unserialize($ads['visibility_include']);
                              if(isset($ads['visibility_exclude']))
                              $ads['visibility_exclude'] = unserialize($ads['visibility_exclude']);

                              if(isset($ads['targeting_include']))
                              $ads['targeting_include'] = unserialize($ads['targeting_include']);

                              if(isset($ads['targeting_exclude']))
                              $ads['targeting_exclude'] = unserialize($ads['targeting_exclude']);
                              $is_on         = adsensei_is_visibility_on($ads);
                              $is_visitor_on = adsensei_is_visitor_on($ads);
                              if($is_on && $is_visitor_on ){
                                echo   adsensei_replace_ads_new( $tag, 'CusAds' . $ads['ad_id'], $ads['ad_id'] );

                              }
                            }

                          }
                        }
                      }

                      function adsensei_background_ad(){
                        if(!is_admin()){
                          ob_start( "adsensei_background_ad_last");
                        }

                      }


                      function adsensei_background_ad_last($content){

                        if(!isset($adsensei_ads)|| empty($adsensei_ads))
                        {
                          require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
                          $api_service = new ADSENSEI_Ad_Setup_Api_Service();
                          $adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');
                        }

                        if(isset($adsensei_ads['posts_data'])){
                          foreach($adsensei_ads['posts_data'] as $key => $value){
                            $ads =$value['post_meta'];
                            if($value['post']['post_status']== 'draft'){
                              continue;
                            }

                            if(isset($ads['visibility_include']))
                            $ads['visibility_include'] = unserialize($ads['visibility_include']);
                            if(isset($ads['visibility_exclude']))
                            $ads['visibility_exclude'] = unserialize($ads['visibility_exclude']);

                            if(isset($ads['targeting_include']))
                            $ads['targeting_include'] = unserialize($ads['targeting_include']);

                            if(isset($ads['targeting_exclude']))
                            $ads['targeting_exclude'] = unserialize($ads['targeting_exclude']);
                            $is_on         = adsensei_is_visibility_on($ads);
                            $is_visitor_on = adsensei_is_visitor_on($ads);
                            if(isset($ads['ad_id']))
                            $post_status = get_post_status($ads['ad_id']);
                            else
                            $post_status =  'publish';

                            if(!isset($ads['position']) || isset($ads['ad_type']) && $ads['ad_type']== 'random_ads'){

                              $is_on = true;
                            }

                            if($is_on && $is_visitor_on && $post_status=='publish'){
                              if($ads['ad_type'] == 'background_ad'){

                                $after_body='<div class="adsensei-bg-wrapper">
                                <a style="background-image: url('.esc_attr($ads['image_src']).')" class="adsensei-bg-ad" target="_blank" href="'.esc_attr($ads['image_redirect_url']).'">'
                                . '</a>'
                                . '<div class="adsensei-bg-content">';
                                $style=' <style>     .adsensei-bg-ad{
                                  position: absolute;
                                  top: 0;
                                  left: 0;
                                  height: 100%;
                                  width: 100%;
                                  background-position: center;
                                  background-repeat: no-repeat;
                                  background-size: cover;
                                }
                                .adsensei-bg-content{
                                  margin: auto;
                                  position: inherit;
                                  top: 0;
                                  left: 0;
                                  bottom: 0;
                                  right: 0;
                                }
                                .h_m{
                                  z-index: 1;
                                  position: relative;
                                }
                                .content-wrapper{
                                  position: relative;
                                  z-index: 0;
                                  margin: 0 16%
                                }
                                .cntr, .amp-wp-article{
                                  background:#ffffff;
                                }
                                .footer{
                                  background:#ffffff;
                                }
                                @media(max-width:768px){
                                  .adsensei-bg-ad{
                                    position:relative;
                                  }
                                  .content-wrapper{
                                    margin:0;
                                  }
                                }</style>';
                                $before_body = $style.'</div></div>';
                                $content = preg_replace("/(\<body.*\>)/", $before_body."$1".$after_body, $content);
                              } else if($ads['ad_type'] == 'skip_ads'){

                                if(!isset($_COOKIE['skip_ads_delay'])) {
                                  setcookie('skip_ads_delay', esc_attr($ads['freq_page_view']),-1, "/"); // 86400 = 1 day
                                }else{
                                  if($_COOKIE['skip_ads_delay'] != 0){
                                    setcookie('skip_ads_delay', esc_attr($_COOKIE['skip_ads_delay']-1),-1, "/"); // 86400 = 1 day
                                    return $content;
                                  }

                                }

                                $html = '<div style="bottom: 0px; height: 8px; background: rgb(210, 210, 210);" id="progressContainer" class="progressContainer">
                                <div id="progressAd" class="progressAd" style="background-color: rgb(221, 51, 51); width: 0%; height: 8px;"></div>
                                </div>

                                <div style="background-color:#212121;" id="progressModal" class="progressModal">
                                <span class="pClose" style="right:0.8rem;bottom:1.2rem;background-color:#000;color:#ffffff" id="progressSkipper">Please wait..</span>
                                <div class="progresContentArea">';
                                if(isset($ads['skip_ads_type'])  && $ads['skip_ads_type'] == 'image_banner' ){

                                  if(isset($ads['image_redirect_url'])  && !empty($ads['image_redirect_url'])){
                                    $html .= '
                                    <a target="_blank" href="'.esc_attr($ads['image_redirect_url']). '" rel="nofollow">
                                    <img class="aligncenter" src="'.esc_attr($ads['image_src']). '" >
                                    </a>';
                                  }else{
                                    $html .= '<img class="aligncenter" src="'.esc_attr($ads['image_src']). '" >';
                                  }
                                }else{
                                  $html .= $ads['code'];
                                }

                                $html .= '</div>
                                </div>
                                <script>

                                if (typeof adsenseigetCookie !== "function"){

                                  function adsenseigetCookie(cname) {
                                    var name = cname + "=";
                                    var ca = document.cookie.split(";");
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

                                  function adsenseisetCookie(cName, cValue, exdays, path) {
                                    var d = new Date();
                                    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                                    var expires = "expires=" + d.toUTCString();
                                    document.cookie = cName + "=" + cValue + "; " + expires + "; path=/";
                                  }
                                }



                                function updateDCPAProgress(top, bottom, col1, col2, range, time, skip, remaining, type, modal, adstime, afterads) {
                                  var selectRange = range;
                                  var selectRange2 = selectRange + 30;

                                  var percent = Math.ceil(top / bottom * 100) + "%";
                                  var normal = Math.ceil(top / bottom * 100);

                                  document.getElementById("progressAd").style.width = percent;

                                  if (normal >= selectRange && normal <= selectRange2) { //if in range

                                    const element = document.querySelector("#progressModal");
                                    if(element.classList.contains("active") == false && element.classList.contains("clicked") == false){
                                      //if in ads
                                      element.classList.add("active");
                                      document.body.style.overflow = "hidden";
                                      document.querySelector("#progressAd").style.backgroundColor = col2;

                                      if (type == 2 ) {
                                        //if youtube style ad button
                                        var timeleft = time;
                                        var downloadTimer = setInterval(function(){
                                          document.getElementById("progressSkipper").innerHTML = timeleft + " " + remaining;
                                          timeleft--;
                                          if(timeleft == -2){
                                            clearInterval(downloadTimer);
                                            document.getElementById("progressSkipper").innerHTML = skip;
                                            document.querySelector(".pClose").onclick = function() {
                                              var count_skip =adsenseigetCookie("skip_ads_delay");
                                              adsenseisetCookie("skip_ads_delay",count_skip -1, 30, "/");

                                              document.querySelector("#progressContainer").style.display = "none";
                                              element.classList.remove("active");
                                              element.classList.add("clicked");
                                              document.body.style.overflow = "visible";
                                              document.querySelector("#progressAd").style.backgroundColor = col1;
                                            }
                                          }
                                        }, 1000);
                                      }
                                    }
                                  }
                                }
                                window.addEventListener("scroll", function () {
                                  var top = window.scrollY;
                                  var height = document.body.getBoundingClientRect().height - window.innerHeight;
                                  var color1 = "#dd3333";
                                  var color2 = "#eff700";
                                  var type = 2;
                                  var range = 30;
                                  var modal = 1;
                                  var time = '.esc_attr(isset($ads['ad_wt_time'])?$ads['ad_wt_time'] : 5). ';
                                  var skip = "Skip Ad >";
                                  var remaining = "seconds remaining";
                                  var freq = 0;
                                  var afterads = 1;
                                  updateDCPAProgress(top, height, color1, color2, range, time, skip, remaining, type, modal, freq, afterads);
                                });</script>
                                <style>
                                #progressCloser{z-index:999999;font-family:Arial;font-size:21px;position:absolute;cursor:pointer;padding:4px 11px;text-align:center;border-radius:100%}#progressSkipper{z-index:999999;font-family:Arial;font-size:21px;position:absolute;cursor:pointer;padding:8px 12px 8px;border:1px solid #484848;text-align:center;}.progressModal{z-index:999998;padding:2rem 4rem 2rem;background-color:#000;visibility:hidden;opacity:0;transition:opacity .5s,visibility 0s .5s}@media (max-width :768px){.progressModal{padding:2rem 1rem 1rem}}.progressModal.active{opacity:1;overflow-y:scroll!important;visibility:visible;transition:opacity .5s}.progresContentArea{padding:.4rem}.progressContainer{z-index:999999;position:fixed;left:0;width:100%}.progressAd{z-index:999999;transition:width .5s}.progressAdcontent,.progressModal{position:fixed;max-height:100%;overflow-y:auto;overflow:hidden;top:0;left:0;height:100%;width:100%}.progressAdcontent{z-index:999998}@keyframes progMove{from{background-position:0 0}to{background-position:220px 0}}.progressAd2{z-index:999999;float:left;box-sizing:border-box;background-size:40px 40px;border-radius:10px 0 0 10px;background-image:-webkit-linear-gradient(45deg,rgba(255,255,255,.2) 30%,rgba(0,0,0,.1) 30%,rgba(0,0,0,.1) 33%,transparent 33%,transparent 46%,rgba(0,0,0,.1) 46%,rgba(0,0,0,.1) 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 80%,rgba(0,0,0,.1) 80%,rgba(0,0,0,.1) 83%,transparent 83%,transparent 97%,rgba(0,0,0,.1) 97%,rgba(0,0,0,.1));background-image:linear-gradient(45deg,rgba(255,255,255,.2) 30%,rgba(0,0,0,.1) 30%,rgba(0,0,0,.1) 34%,transparent 34%,transparent 46%,rgba(0,0,0,.1) 46%,rgba(0,0,0,.1) 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 80%,rgba(0,0,0,.1) 80%,rgba(0,0,0,.1) 84%,transparent 84%,transparent 96%,rgba(0,0,0,.1) 96%,rgba(0,0,0,.1));-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,.1);-moz-box-shadow:inset 0 -1px 0 rgba(0,0,0,.1);box-shadow:inset 0 -1px 0 rgba(0,0,0,.1);-webkit-transition:width .2s ease;-moz-transition:width .2s ease;-o-transition:width .2s ease;transition:width .2s ease}.progressAd3{z-index:999999;-webkit-border-radius:3px;-moz-border-radius:3px;-ms-border-radius:3px;-o-border-radius:3px;border-radius:3px;-webkit-box-shadow:inset 0 3px 5px 0 rgba(0,0,0,.2);-moz-box-shadow:inset 0 3px 5px 0 rgba(0,0,0,.2);box-shadow:inset 0 3px 5px 0 rgba(0,0,0,.2);background-image:-webkit-gradient(linear,0 0,100% 100%,color-stop(.25,rgba(255,255,255,.2)),color-stop(.25,transparent),color-stop(.5,transparent),color-stop(.5,rgba(255,255,255,.2)),color-stop(.75,rgba(255,255,255,.2)),color-stop(.75,transparent),to(transparent));background-image:-webkit-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);background-image:-o-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);-webkit-background-size:45px 45px;-moz-background-size:45px 45px;-o-background-size:45px 45px;background-size:45px 45px}.progressAd4{z-index:999999;animation:progMove 4s linear infinite;-moz-animation:progMove 4s linear infinite;-webkit-animation:progMove 4s linear infinite;-o-animation:progMove 4s linear infinite;-webkit-border-radius:3px;-moz-border-radius:3px;-ms-border-radius:3px;-o-border-radius:3px;border-radius:3px;-webkit-box-shadow:inset 0 3px 5px 0 rgba(0,0,0,.2);-moz-box-shadow:inset 0 3px 5px 0 rgba(0,0,0,.2);box-shadow:inset 0 3px 5px 0 rgba(0,0,0,.2);background-image:-webkit-gradient(linear,0 0,100% 100%,color-stop(.25,rgba(255,255,255,.2)),color-stop(.25,transparent),color-stop(.5,transparent),color-stop(.5,rgba(255,255,255,.2)),color-stop(.75,rgba(255,255,255,.2)),color-stop(.75,transparent),to(transparent));background-image:-webkit-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);background-image:-o-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent);-webkit-background-size:45px 45px;-moz-background-size:45px 45px;-o-background-size:45px 45px;background-size:45px 45px}.progressAd5{z-index:999999;background-image:-webkit-linear-gradient(-45deg,transparent 33%,rgba(0,0,0,.1) 33%,rgba(0,0,0,.1) 55%,transparent 55%),-webkit-linear-gradient(top,rgba(255,255,255,.25),rgba(0,0,0,.25)),-webkit-linear-gradient(left,#09c,#f44);border-radius:2px;background-size:35px 20px,100% 100%,100% 100%}.progressAd6{z-index:999999;background-color:#fff;background-image:url("data:image/svg+xml,%3Csvg width="40" height="12" viewBox="0 0 40 12" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M0 6.172L6.172 0h5.656L0 11.828V6.172zm40 5.656L28.172 0h5.656L40 6.172v5.656zM6.172 12l12-12h3.656l12 12h-5.656L20 3.828 11.828 12H6.172zm12 0L20 10.172 21.828 12h-3.656z" fill="%23008386" fill-opacity="0.7" fill-rule="evenodd"/%3E%3C/svg%3E")!important}.progressAd7{z-index:999999;background-color:#383838;background-image:url("data:image/svg+xml,%3Csvg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z" fill="%23e6afff" fill-opacity="1" fill-rule="evenodd"/%3E%3C/svg%3E")!important}.progressAd8{z-index:999999;background-color:#72deff;background-image:url("data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 80 80"%3E%3Cg fill="%2392278f" fill-opacity="0.71"%3E%3Cpath fill-rule="evenodd" d="M0 0h40v40H0V0zm40 40h40v40H40V40zm0-40h2l-2 2V0zm0 4l4-4h2l-6 6V4zm0 4l8-8h2L40 10V8zm0 4L52 0h2L40 14v-2zm0 4L56 0h2L40 18v-2zm0 4L60 0h2L40 22v-2zm0 4L64 0h2L40 26v-2zm0 4L68 0h2L40 30v-2zm0 4L72 0h2L40 34v-2zm0 4L76 0h2L40 38v-2zm0 4L80 0v2L42 40h-2zm4 0L80 4v2L46 40h-2zm4 0L80 8v2L50 40h-2zm4 0l28-28v2L54 40h-2zm4 0l24-24v2L58 40h-2zm4 0l20-20v2L62 40h-2zm4 0l16-16v2L66 40h-2zm4 0l12-12v2L70 40h-2zm4 0l8-8v2l-6 6h-2zm4 0l4-4v2l-2 2h-2z"/%3E%3C/g%3E%3C/svg%3E")!important}.progressAd9{z-index:999999;background-color:#585858;background-image:url("data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 56 28" width="56" height="28"%3E%3Cpath fill="%23f0d519" fill-opacity="0.89" d="M56 26v2h-7.75c2.3-1.27 4.94-2 7.75-2zm-26 2a2 2 0 1 0-4 0h-4.09A25.98 25.98 0 0 0 0 16v-2c.67 0 1.34.02 2 .07V14a2 2 0 0 0-2-2v-2a4 4 0 0 1 3.98 3.6 28.09 28.09 0 0 1 2.8-3.86A8 8 0 0 0 0 6V4a9.99 9.99 0 0 1 8.17 4.23c.94-.95 1.96-1.83 3.03-2.63A13.98 13.98 0 0 0 0 0h7.75c2 1.1 3.73 2.63 5.1 4.45 1.12-.72 2.3-1.37 3.53-1.93A20.1 20.1 0 0 0 14.28 0h2.7c.45.56.88 1.14 1.29 1.74 1.3-.48 2.63-.87 4-1.15-.11-.2-.23-.4-.36-.59H26v.07a28.4 28.4 0 0 1 4 0V0h4.09l-.37.59c1.38.28 2.72.67 4.01 1.15.4-.6.84-1.18 1.3-1.74h2.69a20.1 20.1 0 0 0-2.1 2.52c1.23.56 2.41 1.2 3.54 1.93A16.08 16.08 0 0 1 48.25 0H56c-4.58 0-8.65 2.2-11.2 5.6 1.07.8 2.09 1.68 3.03 2.63A9.99 9.99 0 0 1 56 4v2a8 8 0 0 0-6.77 3.74c1.03 1.2 1.97 2.5 2.79 3.86A4 4 0 0 1 56 10v2a2 2 0 0 0-2 2.07 28.4 28.4 0 0 1 2-.07v2c-9.2 0-17.3 4.78-21.91 12H30zM7.75 28H0v-2c2.81 0 5.46.73 7.75 2zM56 20v2c-5.6 0-10.65 2.3-14.28 6h-2.7c4.04-4.89 10.15-8 16.98-8zm-39.03 8h-2.69C10.65 24.3 5.6 22 0 22v-2c6.83 0 12.94 3.11 16.97 8zm15.01-.4a28.09 28.09 0 0 1 2.8-3.86 8 8 0 0 0-13.55 0c1.03 1.2 1.97 2.5 2.79 3.86a4 4 0 0 1 7.96 0zm14.29-11.86c1.3-.48 2.63-.87 4-1.15a25.99 25.99 0 0 0-44.55 0c1.38.28 2.72.67 4.01 1.15a21.98 21.98 0 0 1 36.54 0zm-5.43 2.71c1.13-.72 2.3-1.37 3.54-1.93a19.98 19.98 0 0 0-32.76 0c1.23.56 2.41 1.2 3.54 1.93a15.98 15.98 0 0 1 25.68 0zm-4.67 3.78c.94-.95 1.96-1.83 3.03-2.63a13.98 13.98 0 0 0-22.4 0c1.07.8 2.09 1.68 3.03 2.63a9.99 9.99 0 0 1 16.34 0z"%3E%3C/path%3E%3C/svg%3E")!important}.progressAd10{z-index:999999;background-color:#f36b6b;background-image:url("data:image/svg+xml,%3Csvg width="100" height="20" viewBox="0 0 100 20" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M21.184 20c.357-.13.72-.264 1.088-.402l1.768-.661C33.64 15.347 39.647 14 50 14c10.271 0 15.362 1.222 24.629 4.928.955.383 1.869.74 2.75 1.072h6.225c-2.51-.73-5.139-1.691-8.233-2.928C65.888 13.278 60.562 12 50 12c-10.626 0-16.855 1.397-26.66 5.063l-1.767.662c-2.475.923-4.66 1.674-6.724 2.275h6.335zm0-20C13.258 2.892 8.077 4 0 4V2c5.744 0 9.951-.574 14.85-2h6.334zM77.38 0C85.239 2.966 90.502 4 100 4V2c-6.842 0-11.386-.542-16.396-2h-6.225zM0 14c8.44 0 13.718-1.21 22.272-4.402l1.768-.661C33.64 5.347 39.647 4 50 4c10.271 0 15.362 1.222 24.629 4.928C84.112 12.722 89.438 14 100 14v-2c-10.271 0-15.362-1.222-24.629-4.928C65.888 3.278 60.562 2 50 2 39.374 2 33.145 3.397 23.34 7.063l-1.767.662C13.223 10.84 8.163 12 0 12v2z" fill="%230d37c2" fill-opacity="0.4" fill-rule="evenodd"/%3E%3C/svg%3E")!important}.progressAd11{z-index:999999;background-color:#f3e092;background-image:url("data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%238fe1e7" fill-opacity="1" fill-rule="evenodd"%3E%3Cpath d="M0 40L40 0H20L0 20M40 40V20L20 40"/%3E%3C/g%3E%3C/svg%3E")!important}.progresContentArea .alignnone { margin: 5px 20px 20px 0; } .progresContentArea .aligncenter, .progresContentArea div.aligncenter { display: block; margin: 5px auto 5px auto; } .progresContentArea .alignright { float:right; margin: 5px 0 20px 20px; } .progresContentArea .alignleft { float: left; margin: 5px 20px 20px 0; } .progresContentArea a img.alignright { float: right; margin: 5px 0 20px 20px; } .progresContentArea a img.alignnone { margin: 5px 20px 20px 0; } .progresContentArea a img.alignleft { float: left; margin: 5px 20px 20px 0; } .progresContentArea a img.aligncenter { display: block; margin-left: auto; margin-right: auto; } .progresContentArea .wp-caption { background: #fff; border: 1px solid #f0f0f0; max-width: 96%; padding: 5px 3px 10px; text-align: center; } .progresContentArea .wp-caption.alignnone { margin: 5px 20px 20px 0; } .progresContentArea .wp-caption.alignleft { margin: 5px 20px 20px 0; } .progresContentArea .wp-caption.alignright { margin: 5px 0 20px 20px; } .progresContentArea .wp-caption img { border: 0 none; height: auto; margin: 0; max-width: 98.5%; padding: 0; width: auto; } .progresContentArea .wp-caption p.wp-caption-text { font-size: 11px; line-height: 17px; margin: 0; padding: 0 4px 5px; }
                                </style>';
                                $content = preg_replace("/(\<body.*\>)/", $html."$1".$after_body, $content);

                              }

                            }

                          }
                        }
                        return $content;
                      }


                      function remove_ad_from_content($content,$ads,$ads_data='',$position='',$repeat_paragraph=false){

                        $wp_charset = get_bloginfo( 'charset' );
                        $tag = 'p[not(parent::blockquote)]|p[not(parent::table)]';
                        $offsets = array();
                        $paragraphs = array();
                        $doc =  new DOMDocument( '1.0', $wp_charset );
                        libxml_use_internal_errors( true );
                        if($content)
                        {
                          $doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
                        }
                        else
                        {
                          return '';
                        }
                        $xpath = new DOMXPath( $doc );
                        $items = $xpath->query( '/html/body/' . $tag );
                        $whitespaces = json_decode( '"\t\n\r \u00A0"' );
                        foreach ( $items  as $item) {
                          if (  ( isset( $item->textContent ) && trim( $item->textContent, $whitespaces ) !== '' ) ) {
                            $paragraphs[] = $item;
                          }
                        }
                        $total_paragraphs = count($paragraphs);
                        if(isset($ads_data['after_the_percentage_value'])){
                          $percentage       = intval($ads_data['after_the_percentage_value']);
                          $position     = floor(($percentage / 100) * $total_paragraphs);
                        }

                        if($repeat_paragraph){
                          for ( $i = $position -1; $i < $total_paragraphs; $i++ ) {
                            // Select every X number.
                            if ( ( $i + 1 ) % $position === 0 )  {
                              $offsets[] = $i;
                            }
                          }
                          foreach ( $offsets as $offset ) {

                            $ref_node  = $paragraphs[$offset]->nextSibling;
                            $ad_dom =  new DOMDocument( '1.0', $wp_charset );
                            libxml_use_internal_errors( true );
                            $ad_dom->loadHTML(mb_convert_encoding('<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wp_charset . '" /><body>' . $ads, 'HTML-ENTITIES', 'UTF-8'));

                            foreach ( $ad_dom->getElementsByTagName( 'body' )->item( 0 )->childNodes as $importedNode ) {
                              $importedNode = $doc->importNode( $importedNode, true );
                              if($ref_node){
                                $ref_node->parentNode->insertBefore( $importedNode, $ref_node );
                              }
                            }
                          }
                        }else{
                          if(isset($paragraphs[$position])){
                            $ref_node  = $paragraphs[$position];

                            $ad_dom =  new DOMDocument( '1.0', $wp_charset );
                            libxml_use_internal_errors( true );
                            $ad_dom->loadHTML(mb_convert_encoding('<!DOCTYPE html><html><meta http-equiv="Content-Type" content="text/html; charset=' . $wp_charset . '" /><body>' . $ads, 'HTML-ENTITIES', 'UTF-8'));

                            foreach ( $ad_dom->getElementsByTagName( 'body' )->item( 0 )->childNodes as $importedNode ) {
                              $importedNode = $doc->importNode( $importedNode, true );
                              if($ref_node){
                                $ref_node->parentNode->insertBefore( $importedNode, $ref_node );
                              }
                            }
                          }
                        }
                        $content =$doc->saveHTML();
                        return $content;
                      }

                      function adsenseiv2_process_content( $content )
                      {
                        global $adsensei_mode, $adsensei_options, $adsArray, $adsArrayCus, $visibleContentAds, $ad_count_widget, $visibleShortcodeAds;

                        // Array of ad codes ids
                        $adsArray = adsensei_get_active_ads();

                        // Return is no ads are defined
                        if ($adsArray === 0 && $adsensei_mode != 'new') {
                          return $content;
                        }
                        $content = adsensei_parse_group_insert_ads( $content );
                        $content = adsensei_parse_rotator_ads( $content );
                        $content = adsensei_parse_sticky_scroll_ads( $content );
                        return $content;

                      }
                      add_filter( 'adsensei_default_filter_position_data', 'adsenseipro_default_filter_position_data',10 ,1 );

                      function adsenseipro_default_filter_position_data($ads){

                        if($ads['ad_type']== 'ad_blindness' ){
                          $cusads = '<!--CusRot'.esc_html($ads['ad_id']).'-->';

                          if(isset($ads['ad_blindness']))
                          $ads['ad_blindness'] = unserialize($ads['ad_blindness']);

                          $key = array_rand($ads['ad_blindness']);

                          $ad_blindness = $ads['ad_blindness'][$key];
                          $ads=array_merge($ads,$ad_blindness);

                          if(isset($ads['ads_list']))
                          $ads['ads_list'] = unserialize($ads['ads_list']);
                          $ads['ad_id'] = $ads['ads_list'][0]['value'];
                        }
                        return $ads;
                      }

                      add_filter( 'adsensei_default_filter_position_data_ab_testing', 'adsenseipro_default_filter_position_data2',10 ,1 );
                      function adsenseipro_default_filter_position_data2($ads){

                        if($ads['ad_type']== 'ab_testing' ){
                          $cusads = '<!--CusRot'.esc_html($ads['ad_id']).'-->';
                          if(isset($ads['ab_testing']))
                          $ads['ab_testing'] = unserialize($ads['ab_testing']);
                          $key = array_rand($ads['ab_testing']);

                          $ab_testing = $ads['ab_testing'][$key];

                          $ads=array_merge($ads,$ab_testing);
                          if( $ab_testing["position"] == 'beginning_of_post' ){
                            add_filter('wp_adsensei_content_html_last_filter','wpadsensei_content_pos_b_modifier');
                            function wpadsensei_content_pos_b_modifier( $content_buffer ){
                              $content_buffer = preg_replace("/class=\"adsensei-location(.*?)\"/", "class=\"adsensei-location$1\" data-attr=\"beginning_of_post\" "  , $content_buffer);
                              return $content_buffer;
                            }
                          }
                          if( $ab_testing["position"] == 'end_of_post' ){
                            add_filter('wp_adsensei_content_html_last_filter','wpadsensei_content_pos_e_modifier');
                            function wpadsensei_content_pos_e_modifier( $content_buffer ){
                              $content_buffer = preg_replace("/class=\"adsensei-location(.*?)\"/", "class=\"adsensei-location$1\" data-attr=\"end_of_post\" "  , $content_buffer);
                              return $content_buffer;
                            }
                          }
                          if( $ab_testing["position"] == 'middle_of_post' ){
                            add_filter('wp_adsensei_content_html_last_filter','wpadsensei_content_pos_mid_modifier');
                            function wpadsensei_content_pos_mid_modifier( $content_buffer ){
                              $content_buffer = preg_replace("/class=\"adsensei-location(.*?)\"/", "class=\"adsensei-location$1\" data-attr=\"middle_of_post\" "  , $content_buffer);
                              return $content_buffer;
                            }
                          }
                          if( $ab_testing["position"] == 'after_more_tag' ){
                            add_filter('wp_adsensei_content_html_last_filter','wpadsensei_content_pos_amt_modifier');
                            function wpadsensei_content_pos_amt_modifier( $content_buffer ){
                              $content_buffer = preg_replace("/class=\"adsensei-location(.*?)\"/", "class=\"adsensei-location$1\" data-attr=\"after_more_tag\" "  , $content_buffer);
                              return $content_buffer;
                            }
                          }

                          if(isset($ads['ads_list']))
                          $ads['ads_list'] = unserialize($ads['ads_list']);
                          $ads['ad_id'] = $ads['ads_list'][0]['value'];

                        }
                        return apply_filters( 'adsensei_ads_data' , $ads);
                      }

                      add_action('wp_ajax_nopriv_adsensei_insert_ad_clicks_beginning_of_post',  'adsensei_insert_ad_clicks_beginning_of_post');
                      add_action('wp_ajax_adsensei_insert_ad_clicks_beginning_of_post',  'adsensei_insert_ad_clicks_beginning_of_post');
                      function adsensei_insert_ad_clicks_beginning_of_post( ){
                        global $wpdb;
                        $stats = $wpdb->get_var($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}adsensei_stats`" ));
                        if($stats>0){
                          $wpdb->query(
                            $wpdb->prepare(
                                "UPDATE `{$wpdb->prefix}adsensei_stats` SET `Beginning_of_post` = `Beginning_of_post` + 1 WHERE `id` = %d;",
                                $stats
                            )
                        );
                        
                        }
                      }

                      add_action('wp_ajax_nopriv_adsensei_insert_ad_clicks_end_of_post',  'adsensei_insert_ad_clicks_end_of_post');
                      add_action('wp_ajax_adsensei_insert_ad_clicks_end_of_post',  'adsensei_insert_ad_clicks_end_of_post');
                      function adsensei_insert_ad_clicks_end_of_post( ){
                        global $wpdb;
                        $stats = $wpdb->get_var($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}adsensei_stats`" ));
                        if($stats>0){
                          $wpdb->query( "UPDATE `{$wpdb->prefix}adsensei_stats` SET `End_of_post` = `End_of_post` + 1 " );
                        }
                      }

                      add_action('wp_ajax_nopriv_adsensei_insert_ad_clicks_middle_of_post',  'adsensei_insert_ad_clicks_middle_of_post');
                      add_action('wp_ajax_adsensei_insert_ad_clicks_middle_of_post',  'adsensei_insert_ad_clicks_middle_of_post');
                      function adsensei_insert_ad_clicks_middle_of_post( ){
                        global $wpdb;
                        $stats = $wpdb->get_var($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}adsensei_stats`" ));
                        if($stats>0){
                          $wpdb->query( "UPDATE `{$wpdb->prefix}adsensei_stats` SET `Middle_of_post` = `Middle_of_post` + 1 " );
                        }
                      }

                      add_action('wp_ajax_nopriv_adsensei_insert_ad_clicks_after_more_tag',  'adsensei_insert_ad_clicks_after_more_tag');
                      add_action('wp_ajax_adsensei_insert_ad_clicks_after_more_tag',  'adsensei_insert_ad_clicks_after_more_tag');
                      function adsensei_insert_ad_clicks_after_more_tag( ){
                        global $wpdb;
                        $stats = $wpdb->get_var($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}adsensei_stats`" ));
                        if($stats>0){
                          $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}adsensei_stats` SET `After_more_tag` = `After_more_tag` + 1 WHERE `id` = %d", $stats ) );
                        }
                      }


                      /**
                      * Parse rotator default ads which can be enabled from general settings
                      *
                      * @global array $adsArray
                      * @global int $visibleContentAds
                      * @return string
                      */
                      function adsensei_parse_rotator_ads($content)
                      {
                        $off_default_ads = (strpos($content, '<!--OffDef-->') !== false);
                        if ($off_default_ads) {
                          return $content;
                        }
                        preg_match("#<!--CusRot(.+?)-->#si", $content, $match);
                        if (!isset($match['1'])) {
                          return $content;
                        }
                        $ad_id = $match['1'];
                        if(!empty($ad_id)){
                          $ad_meta = get_post_meta($ad_id, '',true);
                        }
                        $ads_list = !empty($ad_meta['ads_list']['0']) ? unserialize($ad_meta['ads_list']['0']) : "" ;

                        if (!is_array($ads_list)) return $content;
                        $temp_array =array();
                        foreach ($ads_list as $ad ) {
                          if (isset($ad['value'])){
                            $temp_array[] = $ad['value'];
                          }
                        }

                        $ad_code = array_rand($temp_array);

                        $enabled_on_amp = (isset($ad_meta['enabled_on_amp'][0]))? $ad_meta['enabled_on_amp'][0]: '';

                        $refresh_type                    =  isset($ad_meta['refresh_type'][0]) ? $ad_meta['refresh_type'][0] : '';
                        $refresh_type_interval_sec       =  (isset($ad_meta['refresh_type_interval_sec'][0]) && !empty($ad_meta['refresh_type_interval_sec'][0])) ? $ad_meta['refresh_type_interval_sec'][0] : 0;

                        $refresh_type_grid_column = isset( $ad_meta['grid_data_ad_column'][0] ) ? $ad_meta['grid_data_ad_column'][0] : 0 ;
                        $refresh_type_grid_row = isset( $ad_meta['grid_data_ad_row'][0] ) ? $ad_meta['grid_data_ad_row'][0] : 0 ;
                        // Number of Ads to Show
                        $refresh_type_nofadstoshow = isset( $ad_meta['num_ads_t_s'][0] ) ? $ad_meta['num_ads_t_s'][0] : 0 ;

                        $adsresultset = array();

                        if($refresh_type == 'on_interval'){
                          foreach ($temp_array as $post_ad_id){
                            $ad_meta_group = get_post_meta($post_ad_id, '',true);
                            if( get_post_status($post_ad_id) !== 'publish' ) {
                              continue;
                            }
                            $adsresultset[] = array(
                              'ad_id'                     => $post_ad_id,
                              'ad_type'                   => $ad_meta_group['ad_type'],
                              'ad_adsense_type'           => $ad_meta_group['adsense_type'],
                              'ad_data_client_id'         => $ad_meta_group['g_data_ad_client'][0],
                              'ad_data_ad_slot'           => $ad_meta_group['g_data_ad_slot'][0],
                              // 'ad_custom_code'            => $ad_meta_group['custom_code'],
                              'width'                     => $ad_meta_group['g_data_ad_width'],
                              'height'                    => $ad_meta_group['g_data_ad_height'],
                              'code'                      => $ad_meta_group['code'],
                              'network_code'              => $ad_meta_group['network_code'],
                              'ad_unit_name'              => $ad_meta_group['ad_unit_name'],
                              // 'block_id'                  => $ad_meta_group['block_id'],
                              'data_container'            => $ad_meta_group['data_container'],
                              'data_js_src'               => $ad_meta_group['data_js_src'],
                              'data_cid'                  => $ad_meta_group['data_cid'],
                              'data_crid'                 => $ad_meta_group['data_crid'],
                              'taboola_publisher_id'      => $ad_meta_group['taboola_publisher_id'],
                              'mediavine_site_id'         => $ad_meta_group['mediavine_site_id'],
                              'outbrain_widget_ids'       => $ad_meta_group['outbrain_widget_ids'],
                              'image_redirect_url'        => $ad_meta_group['image_redirect_url'],
                              'ad_image'                  => $ad_meta_group['image_src'],
                            ) ;
                          }
                          $response['adsensei_group_id'] = $ad_id;
                          $response['adsensei_refresh_type']           = 'on_interval';
                          $response['adsensei_group_ref_interval_sec'] = $refresh_type_interval_sec;
                          $response['adsensei_refresh_type_grid_column'] = $refresh_type_grid_column;
                          $response['adsensei_refresh_type_grid_row'] = $refresh_type_grid_row;
                          $response['adsensei_refresh_type_grid_num_of_ats'] = $refresh_type_nofadstoshow;
                          $response['ads'] = $adsresultset;

                          $arr = array(
                            'float:none;text-align:center;',
                            'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
                            'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
                            'float:none;margin:%1$dpx;');

                            $adsalign = isset($adsensei_options['ads']['ad' . $ad_id]['align']) ? $adsensei_options['ads']['ad' . $ad_id]['align'] : ''; // default
                            $adsmargin = isset( $adsensei_options['ads']['ad' . $ad_id]['margin'] ) ? $adsensei_options['ads']['ad' . $ad_id]['margin'] : ''; // default
                            $margin = sprintf( $arr[( int ) $adsalign], $adsmargin );

                            // Do not create any inline style on AMP site
                            $style = !adsensei_is_amp_endpoint() ? apply_filters( 'adsensei_filter_margins', $margin, 'ad' . $ad_id ) : '';

                            $code = "\n" . '<!-- WP ADSENSEI v. ' . ADSENSEI_VERSION . '  Rotator Ad -->' . "\n" .
                            '<div class="adsensei-location adsensei-rotatorad ad_' . esc_attr($ad_id) . '" id="adsensei-rotate" style="' . $style . '">' . "\n";

                            // $inserted_rotater_ads_data =  $response['ads'];
                            // foreach ($inserted_rotater_ads_data as $key =>  $value) {
                            //     $code .= '<span class="adsensei-location adsensei_click_impression ' . esc_attr($ad_id) . '" id="adsensei-ad'.esc_attr($value["ad_id"]).'" ></span>';
                            // }
                            $code .='<div class="adsensei-groups-ads-json" adsensei-group-id="'.esc_attr($ad_id).'" data-json="'. esc_attr(json_encode($response)).'">';
                            $code .='</div>';
                            $json_grid_column =  $response['adsensei_refresh_type_grid_column'] ;
                            $json_grid_row =  $response['adsensei_refresh_type_grid_row'] ;

                            $grid_row_css = '';
                            if($json_grid_column>=1 && $json_grid_row == 1 ){
                              $grid_row_css = 'style="grid-template-columns: auto"' ;
                            }
                            if($json_grid_column==1 && $json_grid_row==1 ){
                              $grid_row_css = 'style="grid-template-columns: auto"' ;
                            }
                            if($json_grid_column==2 && $json_grid_row==2 ){
                              $grid_row_css = 'style="grid-template-columns: auto auto"' ;
                            }
                            if($json_grid_column>=1 && $json_grid_row == 2 ){
                              $grid_row_css = 'style="grid-template-columns: auto auto"' ;
                            }
                            if($json_grid_column>=1 && $json_grid_row == 3 ){
                              $grid_row_css = 'style="grid-template-columns: auto auto auto"' ;
                            }
                            if($json_grid_column>=1 && $json_grid_row == 4 ){
                              $grid_row_css = 'style="grid-template-columns: auto auto auto auto"' ;
                            }

                            $code .='<div style="display:none;" data-id="'.esc_attr($ad_id).'" class="adsensei_ad_container_pre"></div><div grid-area="'.esc_attr($json_grid_column.'*'.$json_grid_row).'" data-id="'.esc_attr($ad_id).'" class="adsensei adsensei_ad_container" '.$grid_row_css.' ></div>';

                            $code .= '</div>' . "\n";

                            $cont = explode('<!--CusRot'.$ad_id.'-->', $content, 2);

                            $content =  $cont[0].$code.$cont[1];
                            $js_dir = ADSENSEI_PLUGIN_URL . 'assets/js/';

                            // Use minified libraries if SCRIPT_DEBUG is turned off
                            $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';

                            // These have to be global
                            wp_enqueue_script( 'adsensei-rotator_ads', $js_dir . 'rotator_ads' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );

                          }else{
                            $content = adsensei_replace_ads_new( $content, 'CusRot' . $ad_id, $temp_array[$ad_code],$enabled_on_amp);
                          }
                          return  $content ;
                        }

                        /**
                        * Parse rotator default ads which can be enabled from general settings
                        *
                        * @global array $adsArray
                        * @global int $visibleContentAds
                        * @return string
                        */
                        function adsensei_parse_group_insert_ads($content) {

                          $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);
                          if( $off_default_ads ) {
                            return $content;
                          }
                          preg_match("#<!--CusGI(.+?)-->#si", $content, $match);
                          if(!isset($match['1'])){
                            return $content;
                          }
                          $ad_id = $match['1'];
                          if(!empty($ad_id)){
                            $ad_meta = get_post_meta($ad_id, '',true);
                          }
                          $ads_list = unserialize($ad_meta['ads_list']['0']);

                          if (!is_array($ads_list)) return $content;
                          $temp_array =array();
                          foreach ($ads_list as $ad ) {
                            if (isset($ad['value'])){
                              $temp_array[] = $ad['value'];
                            }
                          }
                          $temp_array_count        =count($temp_array);
                          for($j=0; $j < $temp_array_count;$j++){
                            $enabled_on_amp = (isset($ad_meta['enabled_on_amp'][0]))? $ad_meta['enabled_on_amp'][0]: '';
                            $content = adsensei_replace_ads_new( $content, 'CusGI' . $ad_id, $temp_array[$j],$enabled_on_amp);
                            if($j == $temp_array_count-1){
                              $number_rand_ads = substr_count( $content, '<!--CusGI' );
                              if($number_rand_ads > 0){
                                $j = -1;
                              }
                            }
                          }
                          return $content;
                        }

                        /**
                        * Parse Sticky Scroll default ads which can be enabled from general settings
                        *
                        * @global array $adsArray
                        * @global int $visibleContentAds
                        * @return string
                        */
                        function adsensei_parse_sticky_scroll_ads($content) {

                          $off_default_ads = (strpos( $content, '<!--OffDef-->' ) !== false);
                          if( $off_default_ads ) {
                            return $content;
                          }
                          preg_match("#<!--CusSS(.+?)-->#si", $content, $match);
                          if(!isset($match['1'])){
                            return $content;
                          }
                          $ad_id = $match['1'];

                          if(!empty($ad_id)){
                            $ad_meta = get_post_meta($ad_id, '',true);
                          }
                          $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '' ;
                          if(isset($ad_meta) && !adsensei_is_amp_endpoint() && preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
                          {
                            $temp_con='<div class="wpadsensei-sticky-container"><div class="wpadsensei-sticky-row"><div class="wpadsensei-sticky-ads"><!--CusSS'.$ad_id.'--></div></div></div>';
                            $temp_con.='</div><style>.wpadsensei-sticky-row{display:flex;justify-content:space-around;align-items:flex-start;height:'.$ad_meta['sticky_scroll_height'][0].'px}.wpadsensei-sticky-container{border:1px dashed rgba(114,186,94,.35)}.wpadsensei-sticky-ads{position:sticky;top:0;padding:20px 0;overflow:hidden;}.wpadsensei-sticky-continue{padding:5px;margin:0 auto;width:100%;text-align:center}.wpadsensei-sticky-row img{max-width:100%;}</style>';
                            $content = preg_replace('/<!--CusSS.*-->/i', $temp_con, $content);
                          }

                          $ads_list = unserialize($ad_meta['ads_list']['0']);

                          if (!is_array($ads_list)) return $content;
                          $temp_array =array();
                          foreach ($ads_list as $ad ) {
                            if (isset($ad['value'])){
                              $temp_array[] = $ad['value'];
                            }
                          }
                          $temp_array_count        =count($temp_array);
                          for($j=0; $j < $temp_array_count;$j++){
                            $enabled_on_amp = (isset($ad_meta['enabled_on_amp'][0]))? $ad_meta['enabled_on_amp'][0]: '';
                            $content = adsensei_replace_ads_new( $content, 'CusSS' . $ad_id, $temp_array[$j],$enabled_on_amp);

                            if($j == $temp_array_count-1){
                              $number_rand_ads = substr_count( $content, '<!--CusSS' );
                              if($number_rand_ads > 0){
                                $j = -1;
                              }
                            }
                          }

                          return $content;
                        }

                        /**
                        * Return the ad label
                        *
                        * @return string
                        */
                        function adsensei_render_ad_label( $adcode ) {
                          global $adsensei_options,$adsensei_mode;
                          if($adsensei_mode !='new'){
                            $position = isset( $adsensei_options['adlabel'] ) && $adsensei_options['adlabel'] !== 'none' ? $adsensei_options['adlabel'] : 'none';

                            $label = apply_filters( 'adsensei_ad_label', 'Advertisements' );

                            $html = '<div class="adsensei-ad-label">' . sanitize_text_field($label) . '</div>';

                            if( $position === 'none' ) {
                              return $adcode;
                            }
                            if( $position === 'above' ) {
                              return $html . $adcode;
                            }
                            if( $position === 'below' ) {
                              return $adcode . $html;
                            }
                          }else{
                            return $adcode;
                          }
                        }

                        add_filter( 'adsensei_render_ad', 'adsensei_render_ad_label' );

                        /**
                        * Overwrite custom advert code.
                        * Can be used in functions.php to overwrite ads
                        *
                        * @param string $id number of the ad
                        * @return string ad code
                        */
                        function adsensei_overwrite_ad( $id ) {
                          // Overwrite an ad with custom one
                          $custom_ad = apply_filters( 'adsensei_overwrite_ad', $id );

                          if( isset( $custom_ad[$id] ) ) {
                            return $custom_ad[$id];
                          }

                          if( !empty( $custom_ad ) && !is_array( $custom_ad ) ) {
                            return $custom_ad;
                          }

                          return false;
                        }

                        /**
                        * Add more margin positions
                        *
                        * @global array $adsensei_options
                        * @param string $margin
                        * @param int $id
                        * @return string
                        */
                        function adsensei_add_margin( $style, $id ) {
                          global $adsensei_options;

                          if( empty( $adsensei_options['ads'][$id]['margin-left'] ) &&
                          empty( $adsensei_options['ads'][$id]['margin-top'] ) &&
                          empty( $adsensei_options['ads'][$id]['margin-right'] ) &&
                          empty( $adsensei_options['ads'][$id]['margin-bottom'] ) ) {
                            return $style;
                          }

                          $top = isset( $adsensei_options['ads'][$id]['margin-top'] ) ? $adsensei_options['ads'][$id]['margin-top'] : '0';
                          $right = isset( $adsensei_options['ads'][$id]['margin-right'] ) ? $adsensei_options['ads'][$id]['margin-right'] : '0';
                          $bottom = isset( $adsensei_options['ads'][$id]['margin-bottom'] ) ? $adsensei_options['ads'][$id]['margin-bottom'] : '0';
                          $left = isset( $adsensei_options['ads'][$id]['margin-left'] ) ? $adsensei_options['ads'][$id]['margin-left'] : '0';

                          $arr = array(
                            'float:left;margin:%1$dpx %2$dpx %3$dpx %4$dpx;',
                            'float:none;margin:%1$dpx %2$dpx %3$dpx %4$dpx;text-align:center;',
                            'float:right;margin:%1$dpx %2$dpx %3$dpx %4$dpx;',
                            'float:none;margin:%1$dpx %2$dpx %3$dpx %4$dpx;');

                            $align = isset( $adsensei_options['ads'][$id]['align'] ) ? $adsensei_options['ads'][$id]['align'] : '3'; // 3 is default
                            $style = sprintf( $arr[( int ) $align], $top, $right, $bottom, $left );

                            return $style;
                          }

                          add_filter( 'adsensei_filter_margins', 'adsensei_add_margin', 2, 3 );

                          /**
                          * Add more margin positions for widgets
                          *
                          * @global array $adsensei_options
                          * @param string $margin
                          * @param int $id
                          * @return string
                          */
                          function adsensei_add_widget_margin( $style, $id ) {
                            global $adsensei_options;

                            if( empty( $adsensei_options['ads'][$id]['margin-left'] ) &&
                            empty( $adsensei_options['ads'][$id]['margin-top'] ) &&
                            empty( $adsensei_options['ads'][$id]['margin-right'] ) &&
                            empty( $adsensei_options['ads'][$id]['margin-bottom'] ) ) {
                              return $style;
                            }

                            $top = isset( $adsensei_options['ads'][$id]['margin-top'] ) ? $adsensei_options['ads'][$id]['margin-top'] : '0';
                            $right = isset( $adsensei_options['ads'][$id]['margin-right'] ) ? $adsensei_options['ads'][$id]['margin-right'] : '0';
                            $bottom = isset( $adsensei_options['ads'][$id]['margin-bottom'] ) ? $adsensei_options['ads'][$id]['margin-bottom'] : '0';
                            $left = isset( $adsensei_options['ads'][$id]['margin-left'] ) ? $adsensei_options['ads'][$id]['margin-left'] : '0';

                            $arr = array(
                              'float:left;margin:%1$dpx %2$dpx %3$dpx %4$dpx;',
                              'float:none;margin:%1$dpx %2$dpx %3$dpx %4$dpx;text-align:center;',
                              'float:right;margin:%1$dpx %2$dpx %3$dpx %4$dpx;',
                              'float:none;margin:%1$dpx %2$dpx %3$dpx %4$dpx;');

                              $align = isset( $adsensei_options['ads'][$id]['align'] ) ? $adsensei_options['ads'][$id]['align'] : '3'; // 3 is default
                              $style = sprintf( $arr[( int ) $align], $top, $right, $bottom, $left );

                              return $style;
                            }

                            add_filter( 'adsensei_filter_widget_margins', 'adsensei_add_widget_margin', 2, 3 );

                            /**
                            * Flattens an array
                            *
                            * @param array $array
                            * @return array
                            */
                            function adsensei_flatten( array $array ) {
                              $new = array();
                              foreach ( $array as $value ) {
                                $new += $value;
                              }
                              return $new;
                            }

                            add_filter( 'adsensei_localize_filter', 'adsensei_is_pro_activated',10,2);
                            function adsensei_is_pro_activated($object, $object_name){
                              $object['is_pro'] = true;
                              return $object;
                            }
