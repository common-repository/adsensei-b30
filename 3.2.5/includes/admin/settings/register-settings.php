<?php
/**
 * Register Settings
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Settings
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
   exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @return mixed
 */
function adsensei_get_option( $key = '', $default = false ) {
   global $adsensei_options;
   $value = !empty( $adsensei_options[$key] ) ? $adsensei_options[$key] : $default;
   $value = apply_filters( 'adsensei_get_option', $value, $key, $default );
   return apply_filters( 'adsensei_get_option_' . $key, $value, $key, $default );
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array ADSENSEI settings
 */
function adsensei_get_settings() {
   $settings = get_option( 'adsensei_settings' );

   if( empty( $settings ) ) {
      // Update old settings with new single option
      $general_settings = is_array( get_option( 'adsensei_settings_general' ) ) ? get_option( 'adsensei_settings_general' ) : array();
      $ext_settings = is_array( get_option( 'adsensei_settings_extensions' ) ) ? get_option( 'adsensei_settings_extensions' ) : array();
      $addons_settings = is_array( get_option( 'adsensei_settings_addons' ) ) ? get_option( 'adsensei_settings_addons' ) : array();
      $imexport_settings = is_array( get_option( 'adsensei_settings_imexport' ) ) ? get_option( 'adsensei_settings_imexport' ) : array();
      $help_settings = is_array( get_option( 'adsensei_settings_help' ) ) ? get_option( 'adsensei_settings_help' ) : array();

      $settings = array_merge( $general_settings, $ext_settings, $imexport_settings, $help_settings );

      update_option( 'adsensei_settings', $settings );


   }
   return apply_filters( 'adsensei_get_settings', $settings );
}

function wpadsensei_support_page_callback(){
    ?>
     <div class="wpadsensei_support_div">
          <?php echo esc_html__('If you have any query, please write the query in below box or email us at', 'adsenseib30') ?> <a href="mailto:team@wpadsensei.com">team@wpadsensei.com</a>. <?php echo esc_html__('We will reply to your email address shortly', 'adsenseib30') ?><br><br>
            <span class="wpadsensei-query-success wpadsensei_hide"><?php echo esc_html__('Message sent successfully, Please wait we will get back to you shortly', 'adsenseib30'); ?></span>
                    <span class="wpadsensei-query-error wpadsensei_hide"><?php echo esc_html__('Message not sent. please check your network connection', 'adsenseib30'); ?></span>
            <ul>
                <li>
                   <input type="text" id="wpadsensei_query_email" name="wpadsensei_query_email" placeholder="Your Email">
                </li>
                <li>
                    <div><textarea rows="5" cols="60" id="wpadsensei_query_message" name="wpadsensei_query_message" placeholder="Write your query"></textarea></div>
                </li>
                <li>
                    <strong><?php echo esc_html__('Are you a premium customer ?', 'adsenseib30'); ?></strong>
                    <select id="wpadsensei_query_premium_cus" name="wpadsensei_query_premium_cus">
                        <option value=""><?php echo esc_html__('Select', 'adsenseib30'); ?></option>
                        <option value="yes"><?php echo esc_html__('Yes', 'adsenseib30'); ?></option>
                        <option value="no"><?php echo esc_html__('No', 'adsenseib30'); ?></option>
                    </select>
                </li>
                <li><button class="button wpadsensei-send-query"><?php echo esc_html__('Send Message', 'adsenseib30'); ?></button></li>
            </ul>
        </div>
    <?php
}
/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
 */
function adsensei_register_settings() {

   if( false == get_option( 'adsensei_settings' ) ) {
      add_option( 'adsensei_settings' );
   }

   foreach ( adsensei_get_registered_settings() as $tab => $settings ) {

      add_settings_section(
              'adsensei_settings_' . $tab, __return_null(), '__return_false', 'adsensei_settings_' . $tab
      );

      foreach ( $settings as $option ) {

         $name = isset( $option['name'] ) ? $option['name'] : '';
        if($tab=='help' && $option['id'] == 'wpadsensei_support'){

         add_settings_field(
                     'adsensei_settings[' . $option['id'] . ']', $name, 'wpadsensei_support_page_callback', 'adsensei_settings_' . $tab, 'adsensei_settings_' . $tab
             );
        }else{
             add_settings_field(
                     'adsensei_settings[' . $option['id'] . ']', $name, function_exists( 'adsensei_' . $option['type'] . '_callback' ) ? 'adsensei_' . $option['type'] . '_callback' : 'adsensei_missing_callback', 'adsensei_settings_' . $tab, 'adsensei_settings_' . $tab, array(
                 'id' => isset( $option['id'] ) ? $option['id'] : null,
                 'desc' => !empty( $option['desc'] ) ? $option['desc'] : '',
                 'desc2' => !empty( $option['desc2'] ) ? $option['desc2'] : '',
                 'helper-desc' => !empty( $option['helper-desc'] ) ? $option['helper-desc'] : '',
                 'name' => isset( $option['name'] ) ? $option['name'] : null,
                 'section' => $tab,
                 'size' => isset( $option['size'] ) ? $option['size'] : null,
                 'options' => isset( $option['options'] ) ? $option['options'] : '',
                 'std' => isset( $option['std'] ) ? $option['std'] : '',
                 'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
                 'textarea_rows' => isset( $option['textarea_rows'] ) ? $option['textarea_rows'] : ''
                     )
             );
        }
      }
   }

   // Store adsense values
   adsensei_store_adsense_args();

   // Store AdSense value
   //adsensei_fix_ad_not_shown();
   // Creates our settings in the options table
   register_setting( 'adsensei_settings', 'adsensei_settings', 'adsensei_settings_sanitize' );
}
add_action( 'admin_init', 'adsensei_register_settings' );

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
 */
function adsensei_get_registered_settings() {

   /**
    * 'Whitelisted' ADSENSEI settings, filters are provided for each settings
    * section to allow extensions and other plugins to add their own settings
    */
    global $adsensei, $adsensei_options;

    $vi_ads = array(
          'id' => 'vi_header',
          'name' => '<strong>' . __( 'vi ads', 'adsenseib30' ) . '</strong>',
          'desc' => '<strong>Native video ad units powered by video intelligence</strong>',
          'type' => 'header'
     );

     $vi_ads_not_loggedin = array(
          'id' => '',
          'type' => ''
     );

    $vi_ads_final = ( false === $adsensei->vi->setRevenue() ) ? $vi_ads_not_loggedin :  $vi_ads;

   $adsensei_settings = array(
       /** General Settings */
       'general' => apply_filters( 'adsensei_settings_general', array(
           array(
               'id' => 'general_header',
               'name' => '<strong>' . __( 'General & Position', 'adsenseib30' ) . '</strong>',
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'header'
           ),
           'maxads' => array(
               'id' => 'maxads',
               'name' => __( 'Limit Amount of ads:', 'adsenseib30' ),
               'desc' => __( ' ads on a page.', 'adsenseib30' ),
               'desc2' => sprintf( __( '<a href="%s" target="_blank">Read here</a> to learn how many AdSense ads are allowed. If you are unsure set the value to unlimited.', 'adsenseib30' ), 'http://wpadsensei.com/google-adsense-allowed-number-ads/' ),
               'type' => 'select',
               'std' => 100,
               'options' => array(
                   1 => '1',
                   2 => '2',
                   3 => '3',
                   4 => '4',
                   5 => '5',
                   6 => '6',
                   7 => '7',
                   8 => '8',
                   9 => '9',
                   10 => '10',
                   11 => '11',
                   12 => '12',
                   13 => '13',
                   14 => '14',
                   15 => '15',
                   16 => '16',
                   17 => '17',
                   18 => '18',
                   19 => '19',
                   20 => '20',
                   100 => 'Unlimited',
               ),
           ),
           array(
               'id' => 'ad_position',
               'name' => __( 'Position - Default Ads', 'adsenseib30' ),
               'desc' => __( 'Assign and activate ads on specific ad places', 'adsenseib30' ),
               'type' => 'ad_position'
           ),
           array(
               'id' => 'visibility',
               'name' => __( 'Visibility', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'visibility'
           ),
           array(
               "id" => "post_types",
               "name" => __( "Post Types", "adsenseib30" ),
               "desc" => __( "Select post types where ads are visible.", "adsenseib30" ),
               "helper-desc" => __( "Select post types where ads are visible.", "adsenseib30" ),
               "type" => "multiselect",
               "options" => adsensei_get_post_types(),
               "placeholder" => __( "Select Post Type", "adsenseib30" )
           ),
           array(
               'id' => 'hide_ajax',
               'name' => __( 'Hide Ads From Ajax Requests', 'adsenseib30' ),
               'desc' => __( 'If your site is using ajax based infinite loading it might happen that ads are loaded without any further post content. Disable this here.', 'adsenseib30' ),
               'type' => 'checkbox'
           ),
           array(
               'id' => 'quicktags',
               'name' => __( 'Quicktags', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'quicktags'
           ),
           array(
               'id' => 'adsTxtEnabled',
               'name' => __( 'ads.txt - Automatic Creation', 'adsenseib30' ),
               'desc' => __( 'Create an ads.txt file', 'adsenseib30' ),
               "helper-desc" => sprintf(__( 'Allow WP ADSENSEI to generate automatically the ads.txt file in root of your website domain. After enabling and saving settings,'
                       . ' check if your ads.txt is correct by opening: <a href="%1$s" target="_blank">%1$s</a> <br><a href="%2$s" target="_blank">Read here</a> to learn more about ads.txt', 'adsenseib30' ),
                        get_site_url() . '/ads.txt',
                       'https://adsplugin.net/make-more-revenue-by-using-an-ads-txt-in-your-website-root-domain/'
                       ),
               'type' => 'checkbox'
           ),
            array(
               'id' => 'lazy_load_global',
               'name' => __( 'Lazy Loading for Adsense', 'adsenseib30' ),
               // 'desc' => __( 'Lazy Loading for Adsense', 'adsenseib30' ),
               'type' => 'checkbox'
           ),
           array(
               'id' => 'quicktags',
               'name' => __( 'Quicktags', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'quicktags'
           ),
          $vi_ads_final,
           array(
               'id' => 'vi_signup',
               'name' =>__( '', 'adsenseib30' ) . '</strong>',
               'type' => 'vi_signup'
           ),
           /* 'load_scripts_footer' => array(
             'id' => 'load_scripts_footer',
             'name' => __( 'JS Load Order', 'adsenseib30' ),
             'desc' => __( 'Enable this to load all *.js files into footer. Make sure your theme uses the wp_footer() template tag in the appropriate place. Default: Disabled', 'adsenseib30' ),
             'type' => 'checkbox'
             ), */
           'adsense_header' => array(
               'id' => 'adsense_header',
               'name' => '<strong>' . __( 'Ads', 'adsenseib30' ) . '</strong>',
               'desc' => '<div class="adsense_admin_header">' . __( 'Enter your ads below:</div>'
                               . '<ul style="margin-top:10px;">'
                               . '<li style="font-weight:600;">- <i>AdSense</i> for using <span style="font-weight:600;">AdSense Text & display Ads</span>!</li>'
                               . '<li style="font-weight:600;">- <i>Plain Text / HTML / JS</i> for all other ads! <br><strong>Caution:</strong> Adding AdSense code into <i>Plain Text</i> option can result in non-displayed ads!</li></ul>', 'adsenseib30' )
               . '</ul>'
               . '<div style="clear:both;">' . sprintf( __( '<strong>Ads are not showing? Read the <a href="%s" target="_blank">troubleshooting guide</a> to find out how to resolve it.', 'adsenseib30' ), 'http://wpadsensei.com/docs/adsense-ads-are-not-showing/?utm_source=plugin&utm_campaign=wpadsensei-settings&utm_medium=website&utm_term=toplink' ) . ''
               . '<br><a href="http://wpadsensei.com/effective-adsense-banner-size-formats/?utm_campaign=plugin&utm_source=general_tab&utm_medium=admin&utm_content=best_banner_sizes" target="_blank">Read this</a> to find out the most effective AdSense banner sizes. </div>'
               . '<div id="adsensei-open-toggle" class="button">' . __( 'Open All Ads', 'adsenseib30' ) . '</div>',
               'type' => 'header'
           ),
           array(
               'id' => 'adsensei_ads',
               'name' => __( '', 'adsenseib30' ),
               'type' => 'ad_code'
           ),
           array(
               'id' => 'new_ad',
               'name' => __( '', 'adsenseib30' ),
               'type' => 'new_ad',
           ),
           'widget_header' => array(
               'id' => 'widget_header',
               'name' => '<strong>' . __( 'Widget Ads', 'adsenseib30' ) . '</strong>',
               'desc' => sprintf( __( 'After creating your ads here go to <a href="%s" target="_self">Appearance->Widgets</a> and drag the WP ADSENSEI widget into place.', 'adsenseib30' ), admin_url() . 'widgets.php' ),
               'type' => 'header'
           ),
           'ad1_widget' => array(
               'id' => 'ad1_widget',
               'name' => __( 'Ad widget 1', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad2_widget' => array(
               'id' => 'ad2_widget',
               'name' => __( 'Ad widget 2', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad3_widget' => array(
               'id' => 'ad3_widget',
               'name' => __( 'Ad widget 3', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad4_widget' => array(
               'id' => 'ad4_widget',
               'name' => __( 'Ad widget 4', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad5_widget' => array(
               'id' => 'ad5_widget',
               'name' => __( 'Ad widget 5', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad6_widget' => array(
               'id' => 'ad6_widget',
               'name' => __( 'Ad widget 6', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad7_widget' => array(
               'id' => 'ad7_widget',
               'name' => __( 'Ad widget 7', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad8_widget' => array(
               'id' => 'ad8_widget',
               'name' => __( 'Ad widget 8', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad9_widget' => array(
               'id' => 'ad9_widget',
               'name' => __( 'Ad widget 9', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           'ad10_widget' => array(
               'id' => 'ad10_widget',
               'name' => __( 'Ad widget 10', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'adsense_widget',
               'size' => 4
           ),
           array(
               'id' => 'plugin_header',
               'name' => '<strong>' . __( 'Plugin Settings', 'adsenseib30' ) . '</strong>',
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'header'
           ),
           'priority' => array(
               'id' => 'priority',
               'name' => __( 'Load Priority', 'adsenseib30' ),
               //'desc' => __( 'Do not change this until you know what you are doing. Usually the default value 20 is working fine. Changing this value can lead to unexpected results like ads not showing or loaded on wrong order. <strong>Default:</strong> 20', 'adsenseib30' ),
               'helper-desc' => __( 'Do not change this until you know what you are doing. Usually the default value 20 is working fine. Changing this value can lead to unexpected results like ads not showing or loaded on wrong order. <strong>Default:</strong> 20', 'adsenseib30' ),
               'type' => 'number',
               'size' => 'small',
               'std' => 10
           ),
           'create_settings' => array(
               'id' => 'create_settings',
               'name' => __( 'Remove menu button', 'adsenseib30' ),
               //'desc' => __( 'Make the WPADSENSEI settings available from <strong>Settings->WPADSENSEI</strong>. This will remove the primary menu button from the admin sidebar', 'adsenseib30' ),
               'desc' => __( 'Remove it' ),
               'helper-desc' => __( 'Make the WPADSENSEI settings available from <strong>Settings->WPADSENSEI</strong>. This will remove the primary menu button from the admin sidebar', 'adsenseib30' ),
               'type' => 'checkbox',
           ),
           'disableAmpScript' => array(
               'id' => 'disableAmpScript',
               'name' => __( 'Disable AMP script', 'adsenseib30' ),
               //'desc' => __( 'Make the WPADSENSEI settings available from <strong>Settings->WPADSENSEI</strong>. This will remove the primary menu button from the admin sidebar', 'adsenseib30' ),
               'desc' => __( 'Disable AMP Scripts' ),
               'helper-desc' => __( 'Disable duplicate AMP ad script integration if your AMP plugin is already loading the script https://cdn.ampproject.org/v0/amp-ad-0.1.js into your site', 'adsenseib30' ),
               'type' => 'checkbox',
           ),
           'uninstall_on_delete' => array(
               'id' => 'uninstall_on_delete',
               'name' => __( 'Delete Data on Uninstall?', 'adsenseib30' ),
               //'desc' => __( 'Check this box if you would like <strong>Settings->WPADSENSEI</strong> to completely remove all of its data when the plugin is deleted.', 'adsenseib30' ),
                'helper-desc' => __( 'Check this box if you would like <strong>Settings->WPADSENSEI</strong> to completely remove all of its data when the plugin is deleted.', 'adsenseib30' ),
               'desc' => 'Delete data',
               'type' => 'checkbox'
           ),
           'hide_add_on_disableplugin' => array(
               'id' => 'hide_add_on_disableplugin',
               'name' => __( 'Hide Shortcode after Deactivate', 'adsenseib30' ),
               //'desc' => __( 'Check this box if you would like <strong>Settings->WPADSENSEI</strong> to completely remove all of its data when the plugin is deleted.', 'adsenseib30' ),
               'helper-desc' => __( 'Check this box if you would like to Hide [adsensei] shortcode from the content after deactivating the plugin.', 'adsenseib30' ),
               'desc' => 'Hides [adsensei] shortcode from the content',
               'type' => 'checkbox'
           ),
           'debug_mode' => array(
               'id' => 'debug_mode',
               'name' => __( 'Debug mode', 'adsenseib30' ),
               'desc' => __( 'Check this to not minify JavaScript and CSS files. This makes debugging much easier and is recommended setting for inspecting issues on your site', 'adsenseib30' ),
               'type' => 'checkbox'
           )
               )
       ),
       'extensions' => apply_filters( 'adsensei_settings_extension', array()
       ),
       'addons' => apply_filters( 'adsensei_settings_addons', array(
           'addons' => array(
               'id' => 'addons',
               'name' => __( '', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'addons'
           ),
               )
       ),
       'imexport' => apply_filters( 'adsensei_settings_imexport', array(
           'imexport' => array(
               'id' => 'imexport',
               'name' => __( '', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'imexport'
           )
               )
       ),
       'help' => apply_filters( 'adsensei_settings_help', array(

            'support' => array(
               'id' => 'wpadsensei_support',
               'name' => __( 'Get help from our development team', 'adsenseib30' ),
                'desc' => __( '', 'adsenseib30' ),
               'type' => 'header'
           ),
           'systeminfo' => array(
               'id' => 'systeminfo',
               'name' => __( 'Systeminfo', 'adsenseib30' ),
               'desc' => __( '', 'adsenseib30' ),
               'type' => 'systeminfo'
           )
               )
       )
   );

   return $adsensei_settings;
}

function adsensei_get_active_ads_data() {
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
           $adsArray[] = 'ad'.$i;
       }
       $i++;
   }
   return (isset($adsArray) && count($adsArray) > 0) ? $adsArray : 0;
}

add_action('wp_ajax_wpadsensei_ads_for_shortcode_data', 'wpadsensei_ads_for_shortcode_data');
function wpadsensei_ads_for_shortcode_data(){

      $html = adsensei_get_active_ads_data();
      echo json_encode($html);
      wp_die();

}

add_action('wp_ajax_wpadsensei_ads_for_shortcode', 'wpadsensei_ads_for_shortcode');
function wpadsensei_ads_for_shortcode(){
      if ( ! isset( $_POST['wpadsensei_security_nonce'] ) ){
        return;
    }
    if ( !wp_verify_nonce( $_POST['wpadsensei_security_nonce'], 'adsensei_ajax_nonce' ) ){
        return;
    }
     global $adsensei_options;
      $html ='<select id="adsensei-select-for-shortcode">';
      foreach ($adsensei_options['ads'] as $key => $value){
        $html .='<option value="'.$key.'"> '.$key.'</option>';


      }
   $html .='</select>';
   echo  $html;
   wp_die();

}

add_action('wp_ajax_wpadsensei_send_query_message', 'wpadsensei_send_query_message');
function wpadsensei_send_query_message(){

    if ( ! isset( $_POST['wpadsensei_security_nonce'] ) ){
        return;
    }
    if ( !wp_verify_nonce( $_POST['wpadsensei_security_nonce'], 'adsensei_ajax_nonce' ) ){
        return;
    }
    $customer_type  = 'Are you a premium customer ? No';
    $message        = sanitize_textarea_field($_POST['message']);
    $email          = sanitize_textarea_field($_POST['email']);
    $premium_cus    = sanitize_textarea_field($_POST['premium_cus']);
    $user           = wp_get_current_user();

    if($premium_cus == 'yes'){
        $customer_type  = 'Are you a premium customer ? Yes';
    }

    $message = '<p>'.$message.'</p><br><br>'. $customer_type. '<br><br> query from WPAdsensei support tab <br> User Website URL: '.site_url();

    if($user){
        $user_data  = $user->data;
        $user_email = $user_data->user_email;
        if($email){
            $user_email = $email;
        }
        //php mailer variables
        $sendto    = 'team@ampforwp.com';
        $subject   = "WPAdsensei Support ticket";
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: '. esc_attr($user_email);
        $headers[] = 'Reply-To: ' . esc_attr($user_email);
        // Load WP components, no themes.
        $sent = wp_mail($sendto, $subject, $message, $headers);
        if($sent){
            echo json_encode(array('status'=>'t'));
        }else{
            echo json_encode(array('status'=>'f'));
        }
    }
    wp_die();
}

/**
 * return empty settings
 * @return string empty one
 */
function adsensei_empty_callback() {
   return '';
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 0.9.0
 *
 * @param array $input The value input in the field
 *
 * @return string $input Sanitized value
 */
function adsensei_settings_sanitize( $input = array() ) {

   global $adsensei_options;


   if( empty( $_POST['_wp_http_referer'] ) ) {
      return $input;
   }

   parse_str( $_POST['_wp_http_referer'], $referrer );

   $settings = adsensei_get_registered_settings();
   $tab = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';


   $input = $input ? $input : array();
   $input = apply_filters( 'adsensei_settings_' . $tab . '_sanitize', $input );
   // Loop through each setting being saved and pass it through a sanitization filter
   foreach ( $input as $key => $value ) {

      // Get the setting type (checkbox, select, etc)
      $type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;
      if( $type ) {
         // Field type specific filter
         $input[$key] = apply_filters( 'adsensei_settings_sanitize_' . $type, $value, $key );
      }

      // General filter
      $input[$key] = apply_filters( 'adsensei_settings_sanitize', $value, $key );
   }
   //wp_die(var_dump($input));


   // Loop through the whitelist and unset any that are empty for the tab being saved
   if( !empty( $settings[$tab] ) ) {
      foreach ( $settings[$tab] as $key => $value ) {
         // settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
         if( is_numeric( $key ) ) {
            $key = $value['id'];
         }

         if( empty( $input[$key] ) ) {
            unset( $adsensei_options[$key] );
         }
      }
   }


   // Merge our new settings with the existing
   $output = array_merge( $adsensei_options, $input );


   add_settings_error( 'adsensei-notices', '', __( 'Settings updated.', 'adsenseib30' ), 'updated' );

   return $output;
}

/**
 * Sanitize all fields and remove whitespaces
 *
 * @since 1.5.3
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function adsensei_sanitize_general_field( $input ){
   if (!is_array( $input )){
      return trim($input);
   }
   return array_map('adsensei_sanitize_general_field', $input);
}
add_filter( 'adsensei_settings_sanitize', 'adsensei_sanitize_general_field' );

/**
 * Sanitize text fields
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function adsensei_sanitize_text_field( $input ) {
   return trim( $input );
}
add_filter( 'adsensei_settings_sanitize_text', 'adsensei_sanitize_text_field' );

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function adsensei_get_settings_tabs() {

   $settings = adsensei_get_registered_settings();

   $tabs = array();
   $tabs['general'] = __( 'General', 'adsenseib30' );

   if( !empty( $settings['visual'] ) ) {
      $tabs['visual'] = __( 'Visual', 'adsenseib30' );
   }

   if( !empty( $settings['extensions'] ) ) {
      $tabs['extensions'] = __( 'Add-On Setting', 'adsenseib30' );
   }

   $tabs['imexport'] = __( 'Import/Export', 'adsenseib30' );
   $tabs['help'] = __( 'Help', 'adsenseib30' );

   return apply_filters( 'adsensei_settings_tabs', $tabs );
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function adsensei_header_callback( $args ) {
   if( !empty( $args['desc'] ) ) {
      echo $args['desc'];
   } else {
      echo '&nbsp';
   }
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_checkbox_callback( $args ) {
   global $adsensei_options;

   $checked = isset( $adsensei_options[$args['id']] ) ? checked( 1, $adsensei_options[$args['id']], false ) : '';
   $html = '<input type="checkbox" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_checkbox_adsense_callback( $args ) {
   global $adsensei_options;

   $checked = isset( $adsensei_options[$args['id']] ) ? checked( 1, $adsensei_options[$args['id']], false ) : '';
   $html = '<input type="checkbox" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   return $html;
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_multicheck_callback( $args ) {
   global $adsensei_options;

   if( !empty( $args['options'] ) ) {
      foreach ( $args['options'] as $key => $option ):
         if( isset( $adsensei_options[$args['id']][$key] ) ) {
            $enabled = $option;
         } else {
            $enabled = NULL;
         }
         echo '<input name="adsensei_settings[' . $args['id'] . '][' . $key . ']" id="adsensei_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';
         echo '<label for="adsensei_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
      endforeach;
      echo '<p class="description adsensei_hidden">' . $args['desc'] . '</p>';
   }
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_radio_callback( $args ) {
   global $adsensei_options;

   foreach ( $args['options'] as $key => $option ) :
      $checked = false;

      if( isset( $adsensei_options[$args['id']] ) && $adsensei_options[$args['id']] == $key )
         $checked = true;
      elseif( isset( $args['std'] ) && $args['std'] == $key && !isset( $adsensei_options[$args['id']] ) )
         $checked = true;

      echo '<input name="adsensei_settings[' . $args['id'] . ']"" id="adsensei_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
      echo '<label for="adsensei_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
   endforeach;

   echo '<p class="description adsensei_hidden">' . $args['desc'] . '</p>';
}

/**
 * Radio Callback for ad types
 *
 * Renders radio boxes for specific ads
 *
 * @since 1.2.7
 * @param1 array $args Arguments passed by the setting
 * @param2 id int ID of the ad
 *
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_adtype_callback( $id, $args ) {
   global $adsensei_options;

   foreach ( $args['options'] as $key => $option ) :
      $checked = false;

      if( isset( $adsensei_options['ads'][$id]['ad_type'] ) && $adsensei_options['ads'][$id]['ad_type'] == $key )
         $checked = true;
      elseif( isset( $args['std'] ) && $args['std'] == $key && !isset( $adsensei_options['ads'][$id]['ad_type'] ) )
         $checked = true;

      echo '<input name="adsensei_settings[ads][' . $id . '][ad_type]" class="adsensei_adsense_type" id="adsensei_settings[ads][' . $id . '][ad_type_' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
      echo '<label for="adsensei_settings[ads][' . $id . '][ad_type_' . $key . ']">' . $option . '</label>&nbsp;';
   endforeach;

   echo '<p class="description adsensei_hidden">' . $args['desc'] . '</p>';
}

/**
 * Radio Callback for ad positions
 *
 * Renders radio boxes for left center right alignment
 *
 * @since 1.2.7
 * @param1 array $args Arguments passed by the setting
 * @param2 id int ID of the ad
 *
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_adposition_callback( $id, $args ) {
   global $adsensei_options;

   foreach ( $args['options'] as $key => $option ) :
      $checked = false;

      if( isset( $adsensei_options['ads'][$id]['align'] ) && $adsensei_options['ads'][$id]['align'] == $key )
         $checked = true;
      elseif( isset( $args['std'] ) && $args['std'] == $key && !isset( $adsensei_options['ads'][$id]['align'] ) )
         $checked = true;

      if( $key == '3' ) {
         echo '<input name="adsensei_settings[ads][' . $id . '][align]" class="adsensei_adsense_align" id="adsensei_settings[ads][' . $id . '][align_' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
         echo '<label for="adsensei_settings[ads][' . $id . '][align_' . $key . ']">Default</label>&nbsp;';
      } else {
         echo '<input name="adsensei_settings[ads][' . $id . '][align]" class="adsensei_adsense_positon" id="adsensei_settings[ads][' . $id . '][align_' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
         echo '<label for="adsensei_settings[ads][' . $id . '][align_' . $key . ']"><img src="' . ADSENSEI_PLUGIN_URL . 'assets/images/align_' . $key . '.png" width="75" height="56"></label>&nbsp;';
      }

   endforeach;
}


/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_text_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
   $html = '<input type="text" class="' . esc_attr($size) . '-text" id="adsensei_settings[' . esc_attr($args['id']) . ']" name="adsensei_settings[' . esc_attr($args['id']) . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
   $html .= '<label class="adsensei_hidden" class="adsensei_hidden" for="adsensei_settings[' . esc_attr($args['id']) . ']"> ' . esc_attr($args['desc']) . '</label>';

   echo $html;
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_number_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $max = isset( $args['max'] ) ? $args['max'] : 999999;
   $min = isset( $args['min'] ) ? $args['min'] : 0;
   $step = isset( $args['step'] ) ? $args['step'] : 1;

   $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
   $html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . esc_attr($args['id']) . ']"> ' . esc_attr($args['desc']) . '</label>';

   echo $html;
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_textarea_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : '40';
   $html = '<textarea class="large-text adsensei-textarea" cols="50" rows="' . $size . '" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_password_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
   $html = '<input type="password" class="' . $size . '-text" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
   $html .= '<label for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function adsensei_missing_callback( $args ) {
   echo '<div class="callback_data">';
   printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'adsenseib30' ), $args['id'] );
   echo '</div>';
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_select_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $html = '<select id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']">';

   foreach ( $args['options'] as $option => $name ) :
      $selected = selected( $option, $value, false );
      $html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
   endforeach;

   $html .= '</select>';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';
   $html .= '<br>' . $args['desc2'];

   echo $html;
}

/**
 * AdSense Type Select Callback
 *
 * Renders Adsense adsense type fields.
 *
 * @since 1.0
 * @param1 array $args Arguments passed by the setting
 * @param2 int $id if od the ad
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_adense_select_callback( $id, $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options['ads'][$id][$args['id']] ) )
      $value = $adsensei_options['ads'][$id][$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';


   $size = !empty( $args['size'] ) ? $args['size'] : 'adsensei-medium-size';

   $htmlNew = '<label class="adsensei_hidden" id="adsensei-label-' . $args['desc'] . '" for="adsensei_settings[ads][' . $id . '][' . $args['id'] . ']"> ' . $args['desc'] . ' </label>';
   $htmlNew .= '<select class="adsensei-select-' . $args['desc'] . ' ' . $size . '" id="adsensei_settings[ads][' . $id . '][' . $args['id'] . ']" name="adsensei_settings[ads][' . $id . '][' . $args['id'] . ']" >';

   foreach ( $args['options'] as $option => $name ) {
      $selected = selected( $option, $value, false );
      $htmlNew .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
   }

   $htmlNew .= '</select>';
   echo $htmlNew;
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 2.1.2
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_color_select_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $html = '<strong>#:</strong><input type="text" style="max-width:80px;border:1px solid #' . esc_attr( stripslashes( $value ) ) . ';border-right:20px solid #' . esc_attr( stripslashes( $value ) ) . ';" id="adsensei_settings[' . $args['id'] . ']" class="medium-text ' . $args['id'] . '" name="adsensei_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';

   $html .= '</select>';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @global $wp_version WordPress Version
 */
function adsensei_rich_editor_callback( $args ) {
   global $adsensei_options, $wp_version;
   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   if( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
      ob_start();
      wp_editor( stripslashes( $value ), 'adsensei_settings_' . $args['id'], array('textarea_name' => 'adsensei_settings[' . $args['id'] . ']', 'textarea_rows' => $args['textarea_rows']) );
      $html = ob_get_clean();
   } else {
      $html = '<textarea class="large-text adsensei-richeditor" rows="10" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
   }

   $html .= '<br/><label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_upload_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
   $html = '<input type="text" class="' . $size . '-text adsensei_upload_field" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
   $html .= '<span>&nbsp;<input type="button" class="adsensei_settings_upload_button button-secondary" value="' . __( 'Upload File', 'adsenseib30' ) . '"/></span>';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_color_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $default = isset( $args['std'] ) ? $args['std'] : '';

   $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
   $html = '<input type="text" class="adsensei-color-picker" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/**
 * Registers the Add-Ons field callback for WPADSENSEI Add-Ons
 *
 * @since 2.0.5
 * @param array $args Arguments passed by the setting
 * @return html
 */
function adsensei_addons_callback( $args ) {
   $html = adsensei_add_ons_page();
   echo $html;
}

/**
 * Registers the im/export callback for WPADSENSEI
 *
 * @since 0.9.0
 * @param array $args Arguments passed by the setting
 * @return html
 */
function adsensei_imexport_callback( $args ) {
   $html = adsensei_tools_import_export_display();
   $html .= adsensei_import_quick_adsense_settings();
   echo $html;
}

/**
 * Registers the system info for WPADSENSEI
 *
 * @since 0.9.0
 * @param array $args Arguments passed by the setting
 * @return html
 */
function adsensei_systeminfo_callback( $args ) {
   $html = adsensei_tools_sysinfo_display();
   echo $html;
}

/**
 * Registers the image upload field
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_upload_image_callback( $args ) {
   global $adsensei_options;

   if( isset( $adsensei_options[$args['id']] ) )
      $value = $adsensei_options[$args['id']];
   else
      $value = isset( $args['std'] ) ? $args['std'] : '';

   $size = ( isset( $args['size'] ) && !is_null( $args['size'] ) ) ? $args['size'] : 'regular';
   $html = '<input type="text" class="' . $size . '-text ' . $args['id'] . '" id="adsensei_settings[' . $args['id'] . ']" name="adsensei_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

   $html .= '<input type="submit" class="button-secondary adsensei_upload_image" name="' . $args['id'] . '_upload" value="' . __( 'Select Image', 'adsenseib30' ) . '"/>';

   $html .= '<label class="adsensei_hidden" for="adsensei_settings[' . $args['id'] . ']"> ' . $args['desc'] . '</label>';

   echo $html;
}

/*
 * Note Callback
 *
 * Show a note
 *
 * @since 2.2.8
 * @param array $args Arguments passed by the setting
 * @return void
 *
 */

function adsensei_note_callback( $args ) {
   global $adsensei_options;
   $html = '';
   echo $html;
}

/**
 * Additional content Callback
 * Adds several content text boxes selectable via jQuery easytabs()
 *
 * @param array $args
 * @return string $html
 * @scince 2.3.2
 */
function adsensei_add_content_callback( $args ) {
   global $adsensei_options;

   $html = '<div id="adsenseitabcontainer" class="tabcontent_container"><ul class="adsenseitabs" style="width:99%;max-width:500px;">';
   foreach ( $args['options'] as $option => $name ) :
      $html .= '<li class="adsenseitab" style="float:left;margin-right:4px;"><a href="#' . $name['id'] . '">' . $name['name'] . '</a></li>';
   endforeach;
   $html .= '</ul>';
   $html .= '<div class="adsenseitab-container">';
   foreach ( $args['options'] as $option => $name ) :
      $value = isset( $adsensei_options[$name['id']] ) ? $adsensei_options[$name['id']] : '';
      $textarea = '<textarea class="large-text adsensei-textarea" cols="50" rows="15" id="adsensei_settings[' . $name['id'] . ']" name="adsensei_settings[' . $name['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
      $html .= '<div id="' . $name['id'] . '" style="max-width:500px;"><span style="padding-top:60px;display:block;">' . $name['desc'] . ':</span><br>' . $textarea . '</div>';
   endforeach;
   $html .= '</div>';
   $html .= '</div>';
   echo $html;
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function adsensei_hook_callback( $args ) {
   do_action( 'adsensei_' . $args['id'] );
}

/**
 * Set manage_options as the cap required to save ADSENSEI settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function adsensei_set_settings_cap() {
   return 'manage_options';
}

add_filter( 'option_page_capability_adsensei_settings', 'adsensei_set_settings_cap' );




/* returns Cache Status if enabled or disabled
 *
 * @since 2.0.4
 * @return string
 */

function adsensei_cache_status() {
   global $adsensei_options;
   if( isset( $adsensei_options['disable_cache'] ) ) {
      return ' <strong style="color:red;">' . __( 'Transient Cache disabled! Enable it for performance increase.', 'adsenseib30' ) . '</strong> ';
   }
}

/* Permission check if logfile is writable
 *
 * @since 2.0.6
 * @return string
 */

function adsensei_log_permissions() {
   global $adsensei_options;
   if( !$adsensei->logger->checkDir() ) {
      return '<br><strong style="color:red;">' . __( 'Log file directory not writable! Set FTP permission to 755 or 777 for /wp-content/plugins/adsenseisharer/logs/', 'adsenseib30' ) . '</strong> <br> Read here more about <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">file permissions</a> ';
   }
}

/**
 * Get number of available ads
 *
 * @global $adsensei_options $adsensei_options
 * @return array
 */
function adsensei_get_ads() {
   global $adsensei_options;
   $adsensei_options['ads'] = (isset($adsensei_options['ads']) && count( $adsensei_options['ads'] ) !== 0 )?(array)$adsensei_options['ads']: array();
   if (empty($adsensei_options['ads'])) {
            $ads = array(
          0 => __( 'Random Ads', 'adsenseib30' ),
          1 => isset( $adsensei_options['ads']['ad1']['label'] ) ? $adsensei_options['ads']['ad1']['label'] : 'ad1',
          2 => isset( $adsensei_options['ads']['ad2']['label'] ) ? $adsensei_options['ads']['ad2']['label'] : 'ad2',
          3 => isset( $adsensei_options['ads']['ad3']['label'] ) ? $adsensei_options['ads']['ad3']['label'] : 'ad3',
          4 => isset( $adsensei_options['ads']['ad4']['label'] ) ? $adsensei_options['ads']['ad4']['label'] : 'ad4',
          5 => isset( $adsensei_options['ads']['ad5']['label'] ) ? $adsensei_options['ads']['ad5']['label'] : 'ad5',
          6 => isset( $adsensei_options['ads']['ad6']['label'] ) ? $adsensei_options['ads']['ad6']['label'] : 'ad6',
          7 => isset( $adsensei_options['ads']['ad7']['label'] ) ? $adsensei_options['ads']['ad7']['label'] : 'ad7',
          8 => isset( $adsensei_options['ads']['ad8']['label'] ) ? $adsensei_options['ads']['ad8']['label'] : 'ad8',
          9 => isset( $adsensei_options['ads']['ad9']['label'] ) ? $adsensei_options['ads']['ad9']['label'] : 'ad9',
          10 => isset( $adsensei_option['ads']['ad10']['label'] ) ? $adsensei_options['ads']['ad10']['label'] : 'ad10',
      );
      return $ads;
   }

   // Start array with
   $arrHeader = array ( 0 => __( 'Random Ads', 'adsenseib30' ) );

   $ads = array();

   foreach ( $adsensei_options['ads'] as $key => $value ){
      // Skip all widget ads
      if ( false !== strpos($key, '_widget') ){
         continue;
      }
      // Create array
      if (!empty( $value['label'] ) ) {
         $ads[] = $value['label'];
      } else {
          $ads[] = $key;
      }

   }

   return array_merge($arrHeader, $ads);

//   $ads = array(
//       0 => __( 'Random Ads', 'adsenseib30' ),
//       1 => isset( $adsensei_options['ads']['ad1']['label'] ) ? $adsensei_options['ads']['ad1']['label'] : 'ad1',
//       2 => isset( $adsensei_options['ads']['ad2']['label'] ) ? $adsensei_options['ads']['ad2']['label'] : 'ad2',
//       3 => isset( $adsensei_options['ads']['ad3']['label'] ) ? $adsensei_options['ads']['ad3']['label'] : 'ad3',
//       4 => isset( $adsensei_options['ads']['ad4']['label'] ) ? $adsensei_options['ads']['ad4']['label'] : 'ad4',
//       5 => isset( $adsensei_options['ads']['ad5']['label'] ) ? $adsensei_options['ads']['ad5']['label'] : 'ad5',
//       6 => isset( $adsensei_options['ads']['ad6']['label'] ) ? $adsensei_options['ads']['ad6']['label'] : 'ad6',
//       7 => isset( $adsensei_options['ads']['ad7']['label'] ) ? $adsensei_options['ads']['ad7']['label'] : 'ad7',
//       8 => isset( $adsensei_options['ads']['ad8']['label'] ) ? $adsensei_options['ads']['ad8']['label'] : 'ad8',
//       9 => isset( $adsensei_options['ads']['ad9']['label'] ) ? $adsensei_options['ads']['ad9']['label'] : 'ad9',
//       10 => isset( $adsensei_option['ads']['ad10']['label'] ) ? $adsensei_options['ads']['ad10']['label'] : 'ad10',
//   );
//return $ads;
}

/**
 * Get array of 1 to 50 for image and paragraph dropdown values
 *
 * @global $adsensei_options $adsensei_options
 * @return array
 */
function adsensei_get_values() {

   $array = array(1);
   for ( $i = 1; $i <= 50; $i++ ) {
      $array[] = $i;
   }
   unset( $array[0] ); // remove the 0 and start the array with 1
   return $array;
}

/**
 * Visibility Callback
 *
 * Renders fields for ad visibility
 *
 * @since 0.9.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_visibility_callback( $args ) {
   global $adsensei_options, $adsensei;

   $html = $adsensei->html->checkbox( array('name' => 'adsensei_settings[visibility][AppHome]', 'current' => !empty( $adsensei_options['visibility']['AppHome'] ) ? $adsensei_options['visibility']['AppHome'] : null, 'class' => 'adsensei-checkbox') ) . __( 'Homepage ', 'adsenseib30' );
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[visibility][AppCate]', 'current' => !empty( $adsensei_options['visibility']['AppCate'] ) ? $adsensei_options['visibility']['AppCate'] : null, 'class' => 'adsensei-checkbox') ) . __( 'Categories ', 'adsenseib30' );
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[visibility][AppArch]', 'current' => !empty( $adsensei_options['visibility']['AppArch'] ) ? $adsensei_options['visibility']['AppArch'] : null, 'class' => 'adsensei-checkbox') ) . __( 'Archives ', 'adsenseib30' );
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[visibility][AppTags]', 'current' => !empty( $adsensei_options['visibility']['AppTags'] ) ? $adsensei_options['visibility']['AppTags'] : null, 'class' => 'adsensei-checkbox') ) . __( 'Tags', 'adsenseib30' ) . '<br>';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[visibility][AppSide]', 'current' => !empty( $adsensei_options['visibility']['AppSide'] ) ? $adsensei_options['visibility']['AppSide'] : null, 'class' => 'adsensei-checkbox') ) . __( 'Hide Ad Widgets on Homepage', 'adsenseib30' ) . '<br>';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[visibility][AppLogg]', 'current' => !empty( $adsensei_options['visibility']['AppLogg'] ) ? $adsensei_options['visibility']['AppLogg'] : null, 'class' => 'adsensei-checkbox') ) . __( 'Hide Ads when user is logged in.', 'adsenseib30' ) . '<br>';

   echo $html;
}

/**
 * Ad position Callback
 *
 * Renders multioptions fields for ad position
 *
 * @since 0.9.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_ad_position_callback( $args ) {
   global $adsensei_options, $adsensei;


   // Pos 1
   $html = $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos1][BegnAds]', 'current' => !empty( $adsensei_options['pos1']['BegnAds'] ) ? $adsensei_options['pos1']['BegnAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos1][BegnRnd]', 'name' => 'adsensei_settings[pos1][BegnRnd]', 'selected' => !empty( $adsensei_options['pos1']['BegnRnd'] ) ? $adsensei_options['pos1']['BegnRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( 'to <strong>Beginning of Post</strong>', 'adsenseib30' ) . '</br>';

   // Pos 2
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos2][MiddAds]', 'current' => !empty( $adsensei_options['pos2']['MiddAds'] ) ? $adsensei_options['pos2']['MiddAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos2][MiddRnd]', 'name' => 'adsensei_settings[pos2][MiddRnd]', 'selected' => !empty( $adsensei_options['pos2']['MiddRnd'] ) ? $adsensei_options['pos2']['MiddRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( 'to <strong>Middle of Post</strong>', 'adsenseib30' ) . '</br>';

   // Pos 3
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos3][EndiAds]', 'current' => !empty( $adsensei_options['pos3']['EndiAds'] ) ? $adsensei_options['pos3']['EndiAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos3][EndiRnd]', 'name' => 'adsensei_settings[pos3][EndiRnd]', 'selected' => !empty( $adsensei_options['pos3']['EndiRnd'] ) ? $adsensei_options['pos3']['EndiRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( 'to <strong>End of Post</strong>', 'adsenseib30' ) . '</br>';

   // Pos 4
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos4][MoreAds]', 'current' => !empty( $adsensei_options['pos4']['MoreAds'] ) ? $adsensei_options['pos4']['MoreAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos4][MoreRnd]', 'name' => 'adsensei_settings[pos4][MoreRnd]', 'selected' => !empty( $adsensei_options['pos4']['MoreRnd'] ) ? $adsensei_options['pos4']['MoreRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( 'right after <strong>the <span style="font-family:Courier New,Courier,Fixed;">&lt;!--more--&gt;</span> tag</strong>', 'adsenseib30' ) . '</br>';

   // Pos 5
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos5][LapaAds]', 'current' => !empty( $adsensei_options['pos5']['LapaAds'] ) ? $adsensei_options['pos5']['LapaAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos5][LapaRnd]', 'name' => 'adsensei_settings[pos5][LapaRnd]', 'selected' => !empty( $adsensei_options['pos5']['LapaRnd'] ) ? $adsensei_options['pos5']['LapaRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( 'right before <strong>the last Paragraph</strong>', 'adsenseib30' ) . ' </br>';

   // Pos 6
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos6][Par1Ads]', 'current' => !empty( $adsensei_options['pos6']['Par1Ads'] ) ? $adsensei_options['pos6']['Par1Ads'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos6][Par1Rnd]', 'name' => 'adsensei_settings[pos6][Par1Rnd]', 'selected' => !empty( $adsensei_options['pos6']['Par1Rnd'] ) ? $adsensei_options['pos6']['Par1Rnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'class' => 'adsensei-paragraph', 'id' => 'adsensei_settings[pos6][Par1Nup]', 'name' => 'adsensei_settings[pos6][Par1Nup]', 'selected' => !empty( $adsensei_options['pos6']['Par1Nup'] ) ? $adsensei_options['pos6']['Par1Nup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos6][Par1Con]', 'current' => !empty( $adsensei_options['pos6']['Par1Con'] ) ? $adsensei_options['pos6']['Par1Con'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';

   // Pos 7
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos7][Par2Ads]', 'current' => !empty( $adsensei_options['pos7']['Par2Ads'] ) ? $adsensei_options['pos7']['Par2Ads'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos7][Par2Rnd]', 'name' => 'adsensei_settings[pos7][Par2Rnd]', 'selected' => !empty( $adsensei_options['pos7']['Par2Rnd'] ) ? $adsensei_options['pos7']['Par2Rnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[pos7][Par2Nup]', 'name' => 'adsensei_settings[pos7][Par2Nup]', 'selected' => !empty( $adsensei_options['pos7']['Par2Nup'] ) ? $adsensei_options['pos7']['Par2Nup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos7][Par2Con]', 'current' => !empty( $adsensei_options['pos7']['Par2Con'] ) ? $adsensei_options['pos7']['Par2Con'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';

   // Pos 8
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos8][Par3Ads]', 'current' => !empty( $adsensei_options['pos8']['Par3Ads'] ) ? $adsensei_options['pos8']['Par3Ads'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[pos8][Par3Rnd]', 'name' => 'adsensei_settings[pos8][Par3Rnd]', 'selected' => !empty( $adsensei_options['pos8']['Par3Rnd'] ) ? $adsensei_options['pos8']['Par3Rnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[pos8][Par3Nup]', 'name' => 'adsensei_settings[pos8][Par3Nup]', 'selected' => !empty( $adsensei_options['pos8']['Par3Nup'] ) ? $adsensei_options['pos8']['Par3Nup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos8][Par3Con]', 'current' => !empty( $adsensei_options['pos8']['Par3Con'] ) ? $adsensei_options['pos8']['Par3Con'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';

   $html .= apply_filters( 'adsensei_extra_paragraph', '' );

   // Pos 9
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos9][Img1Ads]', 'current' => !empty( $adsensei_options['pos9']['Img1Ads'] ) ? $adsensei_options['pos9']['Img1Ads'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'name' => 'adsensei_settings[pos9][Img1Rnd]', 'selected' => !empty( $adsensei_options['pos9']['Img1Rnd'] ) ? $adsensei_options['pos9']['Img1Rnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Image</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[pos9][Img1Nup]', 'name' => 'adsensei_settings[pos9][Img1Nup]', 'selected' => !empty( $adsensei_options['pos9']['Img1Nup'] ) ? $adsensei_options['pos9']['Img1Nup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[pos9][Img1Con]', 'current' => !empty( $adsensei_options['pos9']['Img1Con'] ) ? $adsensei_options['pos9']['Img1Con'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'after <b>Image\'s outer</b><b><span style="font-family:Courier New,Courier,Fixed;"> &lt;div&gt; wp-caption</span></b> if any.', 'adsenseib30' ) . ' </br>';

   echo apply_filters( 'adsensei_ad_position_callback', $html );
}

/**
 * Quicktags Callback
 *
 * Renders quicktags fields
 *
 * @since 0.9.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_quicktags_callback( $args ) {
   global $adsensei_options, $adsensei;

   // Quicktags info
   $html = '<div style="margin-bottom:5px;"><strong>Optional: </strong><a href="#" id="adsensei_insert_ads_action">' . __( ' Insert Ads into a post, on-the-fly', 'adsenseib30' ) . '</a></br>' .
           '<ol style="margin-top:5px;display:none;" id="adsensei_insert_ads_box">
                <li>' . __( 'Insert <span class="adsensei-quote-docs">&lt;!--Ads1--&gt;</span>, <span class="adsensei-quote-docs">&lt;!--Ads2--&gt;</span>, etc. into a post to show the <b>Particular Ads</b> at specific location.', 'adsenseib30' ) . '</li>
                <li>' . __( 'Insert <span class="adsensei-quote-docs">&lt;!--RndAds--&gt;</span> into a post to show the <b>Random Ads</b> at specific location', 'adsenseib30' ) . '</li>
                </ol></div>';

   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[quicktags][QckTags]', 'current' => !empty( $adsensei_options['quicktags']['QckTags'] ) ? $adsensei_options['quicktags']['QckTags'] : null, 'class' => 'adsensei-checkbox') );
   $html .= __( 'Show Quicktag Buttons on the HTML Post Editor', 'adsenseib30' ) . '</br>';
   $html .= '<span class="adsensei-desc">' . __( 'Tags can be inserted into a post via the additional Quicktag Buttons at the HTML Edit Post SubPanel.', 'adsenseib30' ) . '</span>';
   echo $html;
}

/**
 * Add new ad
 * @global array $adsensei_options
 */
function adsensei_ajax_add_ads(){
   global $adsensei_options;

   $postCount = !empty($_POST['count']) ? $_POST['count'] : 1;


   $count = isset($adsensei_options['ads']) ? count ($adsensei_options['ads']) + $postCount : 10 + $postCount;


   $args = array();
   // subtract 10 widget ads
   //$args['id'] = $count-10;
   $args['id'] = $count-getTotalWidgets();
   $args['name'] = 'Ad ' . $args['id'];

   adsensei_ajax_add_ads_new($args);

   ob_start();
   // ... get the content ...
   adsensei_adsense_code_callback( $args );
   $content = ob_get_contents();
   ob_end_clean();

   $html = '<tr><td>';
   $html.= $content;
   $html.= '</td></tr>';
   echo $html;
   die();
}
add_action( 'wp_ajax_adsensei_ajax_add_ads', 'adsensei_ajax_add_ads' );

/**
 * Get the total amount of widget ads
 * @global $adsensei_options $adsensei_options
 * @return int
 */
function getTotalWidgets(){
      global $adsensei_options;

      $i = 0;

      foreach ($adsensei_options['ads'] as $key => $value){
         if (false !== strpos($key, 'widget')){
            $i++;
         }
      }
      return $i;
}

/**
 * Count normal ads. Do not count widget ads
 *
 * @global array $adsensei_options
 * @return int
 */
function adsensei_count_normal_ads() {
   global $adsensei_options;

   if(!isset($adsensei_options['ads'])){
      return 0;
   }

   // Count normal ads - not widget ads
   $adsCount = 0;
   $id = 1;
   foreach ( $adsensei_options['ads'] as $ads => $value ) {
      // Skip if its a widget ad
      if( strpos( $ads, 'ad' . $id ) === 0 && false === strpos( $ads, 'ad' . $id . '_widget' ) ) {
         $adsCount++;
      }
      $id++;
   }
   return $adsCount;
}

function adsensei_new_ad_callback(){
  echo '<a href="#" id="adsensei-add-new-ad">' . __('Add New Ad','adsenseib30') . '</a>';
}

/**
 * Render all ad relevant settings (ADSENSE CODE tab)
 * No widget ads
 * @global $adsensei_options $adsensei_options
 */
function adsensei_ad_code_callback(){
   global $adsensei_options;

//   echo '<tr><td>';
//   echo 'test2';
//   echo '</td></tr>';

   $args = array();

   $i = 1;
   // Render 10 default ads if there are less than 10 ads stored or none at all
   if( adsensei_count_normal_ads() < 10 ) {
      //wp_die('t2');
      while ( $i <= 10 ) {

         $id = $i++;

         $args['id'] = $id;

         $args['desc'] = '';

         $args['name'] = !empty( $adsensei_options['ads']['ad' . $id]['label'] ) ? $adsensei_options['ads']['ad' . $id]['label'] : 'Ad ' . $id;

         echo '<tr><td>';
         echo adsensei_adsense_code_callback( $args );
         echo '</td></tr>';

      }

      // Stop here early
      return true;
   }

   // Else render 10 + n ads
   $i = 1;
   foreach ($adsensei_options['ads'] as $ads => $value ){

      $id = $i++;

      $args['id'] = $id;

      $args['desc'] = '';

      $args['name'] = !empty($adsensei_options['ads']['ad' . $id]['label']) ? $adsensei_options['ads']['ad' . $id]['label'] : 'Ad ' . $id;

      // Skip if its a widget ad
      if ( (strpos($ads, 'ad' . $id) === 0) && (false === strpos($ads, 'ad' . $id . '_widget') ) ){
      echo '<tr><td>';
      echo adsensei_adsense_code_callback( $args );
      echo '</td></tr>';
      }

   }
}

/**
 * AdSense Code Callback
 *
 * Renders adsense code fields
 *
 * @since 0.9.0
 * @param array $args Arguments passed by the setting
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_adsense_code_callback( $args ) {
   global $adsensei_options;

   $new_label = isset( $adsensei_options['ads']['ad'.$args['id']]['label'] ) ? $adsensei_options['ads']['ad'.$args['id']]['label'] : '';

   $label = !empty( $new_label ) ? $new_label : $args['name'];

   $code = isset( $adsensei_options['ads']['ad'.$args['id']]['code'] ) ? $adsensei_options['ads']['ad'.$args['id']]['code'] : '';

   $margin = isset( $adsensei_options['ads']['ad'.$args['id']]['margin'] ) ? esc_attr( stripslashes( $adsensei_options['ads']['ad'.$args['id']]['margin'] ) ) : 0;

   $g_data_ad_client = isset( $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_client'] ) ? $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_client'] : '';

   $g_data_ad_slot = isset( $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_slot'] ) ? $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_slot'] : '';

   $g_data_ad_width = isset( $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_width'] ) ? $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_width'] : '';

   $g_data_ad_height = isset( $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_height'] ) ? $adsensei_options['ads']['ad'. $args['id']]['g_data_ad_height'] : '';

   //$args['desc'] = __( '<strong>Shortcode:</strong> [adsensei id="'.$args['id'].'"] | <strong>PHP:</strong> echo do_shortcode(\'[adsensei id="'.$args['id'].'"]\');', 'adsenseib30' );

   //$label = !empty($new_label) ? $new_label :
   // Create a shorter var to make HTML cleaner
   $id = 'ad' . $args['id'];
   ?>
   <div class="adsensei-ad-toggle-header adsensei-box-close" data-box-id="adsensei-toggle<?php echo $id; ?>">
       <div class="adsensei-toogle-title"><span contenteditable="true" id="adsensei-ad-label-<?php echo $id; ?>"><?php echo $label; ?></span><input type="hidden" class="adsensei-input-label" name="adsensei_settings[ads][<?php echo $id; ?>][label]" value="<?php echo $new_label; ?>"></div>
       <a class="adsensei-toggle" data-box-id="adsensei-toggle<?php echo $id; ?>" href="#"><div class="adsensei-close-open-icon"></div></a>
   </div>
   <div class="adsensei-ad-toggle-container" id="adsensei-toggle<?php echo $id; ?>" style="display:none;">
       <div>
   <?php
   $args_ad_type = array(
       'id' => 'ad_type',
       'name' => 'Type',
       'desc' => '',
       'std' => 'plain_text',
       'options' => array(
           'adsense' => 'AdSense',
           'plain_text' => 'Plain Text / HTML / JS'
       )
   );
   echo adsensei_adtype_callback( $id, $args_ad_type );
   ?>
       </div>
       <textarea style="vertical-align:top;margin-right:20px;" class="large-text adsensei-textarea" cols="50" rows="10" id="adsensei_settings[ads][<?php echo $id; ?>][code]" name="adsensei_settings[ads][<?php echo $id; ?>][code]"><?php echo esc_textarea( stripslashes( $code ) ); ?></textarea>
       <!--<label for="adsensei_settings[ads][ <?php //echo $id; ?> ][code]"> <?php //echo $args['desc']; ?></label><br>//-->
       <label for="adsensei_shortcode_<?php echo $args['id'];?>">Post Shortcode:</label><input readonly id="adsensei_shortcode_<?php echo $args['id'];?>" type="text" onclick="this.focus(); this.select()" value='[adsensei id=<?php echo $args['id'];?>]' title="Optional: Copy and paste the shortcode into the post editor, click below then press Ctrl + C (PC) or Cmd + C (Mac).">
       <label for="adsensei_php_shortcode_<?php echo $args['id'];?>">PHP:</label><input readonly id="adsensei_php_shortcode_<?php echo $args['id'];?>" type="text" onclick="this.focus(); this.select()" style="width:290px;" value="&lt;?php echo do_shortcode('[adsensei id=<?php echo $args['id']; ?>]'); ?&gt;" title="Optional: Copy and paste the PHP code into your theme files, click below then press Ctrl + C (PC) or Cmd + C (Mac).">
       <br>
       <div class="adsensei_adsense_code">
           <input type="button" style="vertical-align:inherit;" class="button button-primary adsensei-add-adsense" value="Copy / Paste AdSense Code"> <span>or add Ad Slot ID & Publisher ID manually below:</span>
           <br />
   <?php //echo __('Generate Ad Slot & Publisher ID automatically from your adsense code', 'adsenseib30') ?>
           <label class="adsensei-label-left" for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_slot]">Ad Slot ID </label><input type="text" class="adsensei-medium-size adsensei-bggrey" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_slot]" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_slot]" value="<?php echo $g_data_ad_slot; ?>">
           <label for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_client]">Publisher ID</label><input type="text" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_client]" class="medium-text adsensei-bggrey" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_client]" value="<?php echo $g_data_ad_client; ?>">
           <br />
   <?php
   $args = array(
       'id' => 'adsense_type',
       'name' => 'Type',
       'desc' => 'Type',
       'options' => array(
           'normal' => 'Fixed Size',
           'responsive' => 'Responsive'
       )
   );
   echo adsensei_adense_select_callback( $id, $args );
   ?>
           <label class="adsensei-label-left adsensei-type-normal" for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_width]">Width </label><input type="number" step="1" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_width]" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_width]" class="small-text adsensei-type-normal" value="<?php echo $g_data_ad_width; ?>">
           <label class="adsensei-type-normal" for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_height]">Height </label><input type="number" step="1" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_height]" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_height]" class="small-text adsensei-type-normal" value="<?php echo $g_data_ad_height; ?>">
       </div>
       <div class="adsensei-style">
           <h3>Layout</h3>
   <?php
   $args_ad_position = array(
       'id' => 'align',
       'name' => 'align',
       'desc' => 'align',
       'std' => '3',
       'options' => array(
           '3' => 'Default',
           '0' => 'Left',
           '1' => 'Center',
           '2' => 'Right'
       )
   );
   echo adsensei_adposition_callback( $id, $args_ad_position );
   echo apply_filters( 'adsensei_render_margin', '', $id ); ?>
       </div>
           <?php
           echo apply_filters( 'adsensei_advanced_settings', '', $id );
           ?>
   </div>
       <?php
    }

    /**
     * AdSense Code Widget Callback
     *
     * Renders adsense code fields
     *
     * @since 0.9.0
     * @param array $args Arguments passed by the setting
     * @global $adsensei_options Array of all the ADSENSEI Options
     * @return void
     */
    function adsensei_adsense_widget_callback( $args ) {
       global $adsensei_options;

       $label = !empty( $args['name'] ) ? $args['name'] : '';

       $code = isset( $adsensei_options['ads'][$args['id']]['code'] ) ? $adsensei_options['ads'][$args['id']]['code'] : '';

       $margin = isset( $adsensei_options['ads'][$args['id']]['margin'] ) ? esc_attr( stripslashes( $adsensei_options['ads'][$args['id']]['margin'] ) ) : 0;
       $margintop   = 0;
      $marginright  = 0;
      $marginbottom = 0;
      $marginleft   = 0;
      if(isset( $adsensei_options['ads'][$args['id']]['margin'] )){
         $margintop     = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['margin']));
         $marginright   = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['margin']));
         $marginbottom  = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['margin']));
         $marginleft    = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['margin']));
       }
       if(isset( $adsensei_options['ads'][$args['id']]['margintop'] )){
         $margintop = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['margintop']));
       }
       if(isset( $adsensei_options['ads'][$args['id']]['marginright'] )){
         $marginright = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['marginright']));
       }
       if(isset( $adsensei_options['ads'][$args['id']]['marginbottom'] )){
         $marginbottom = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['marginbottom']));
       }
       if(isset( $adsensei_options['ads'][$args['id']]['marginleft'] )){
         $marginleft = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['marginleft']));
       }
       // padding
      $paddingtop   = 0;
      $paddingright  = 0;
      $paddingbottom = 0;
      $paddingleft   = 0;

       if(isset( $adsensei_options['ads'][$args['id']]['paddingtop'] )){
         $paddingtop = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['paddingtop']));
       }
       if(isset( $adsensei_options['ads'][$args['id']]['paddingright'] )){
         $paddingright = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['paddingright']));
       }
       if(isset( $adsensei_options['ads'][$args['id']]['paddingbottom'] )){
         $paddingbottom = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['paddingbottom']));
       }
       if(isset( $adsensei_options['ads'][$args['id']]['paddingleft'] )){
         $paddingleft = esc_attr( stripslashes($adsensei_options['ads'][$args['id']]['paddingleft']));
       }

       $g_data_ad_client = isset( $adsensei_options['ads'][$args['id']]['g_data_ad_client'] ) ? $adsensei_options['ads'][$args['id']]['g_data_ad_client'] : '';

       $g_data_ad_slot = isset( $adsensei_options['ads'][$args['id']]['g_data_ad_slot'] ) ? $adsensei_options['ads'][$args['id']]['g_data_ad_slot'] : '';

       $g_data_ad_width = isset( $adsensei_options['ads'][$args['id']]['g_data_ad_width'] ) ? $adsensei_options['ads'][$args['id']]['g_data_ad_width'] : '';

       $g_data_ad_height = isset( $adsensei_options['ads'][$args['id']]['g_data_ad_height'] ) ? $adsensei_options['ads'][$args['id']]['g_data_ad_height'] : '';

       // Create a shorter var to make HTML cleaner
       $id = $args['id']; //xss ok
       ?>
   <div class="adsensei-ad-toggle-header adsensei-box-close" data-box-id="adsensei-toggle<?php echo $id; ?>">
       <div class="adsensei-toogle-title"><?php echo $label; ?></div>
       <a class="adsensei-toggle" data-box-id="adsensei-toggle<?php echo $id; ?>" href="#"><div class="adsensei-close-open-icon"></div></a>
   </div>
   <div class="adsensei-ad-toggle-container" id="adsensei-toggle<?php echo $id; ?>" style="display:none;">
       <div>
   <?php
   $args_ad_type = array(
       'id' => 'ad_type',
       'name' => 'Type',
       'desc' => '',
       'std' => 'plain_text',
       'options' => array(
           'adsense' => 'AdSense',
           'plain_text' => 'Plain Text / HTML / JS'
       )
   );
   echo adsensei_adtype_callback( $id, $args_ad_type );
   ?>
       </div>
       <textarea style="vertical-align:top;margin-right:20px;" class="large-text adsensei-textarea" cols="50" rows="10" id="adsensei_settings[ads][<?php echo $id; ?>][code]" name="adsensei_settings[ads][<?php echo $id; ?>][code]"><?php echo esc_textarea( stripslashes( $code ) ); ?></textarea><label for="adsensei_settings[ads][ <?php echo $id; ?> ][code]"> <?php echo $args['desc']; ?></label>
       <br>
       <div class="adsensei_adsense_code">
           <input type="button" style="vertical-align:inherit;" class="button button-primary adsensei-add-adsense" value="Copy / Paste AdSense Code"> <span>or add Ad Slot ID & Publisher ID manually below:</span>
           <br />
   <?php //echo __('Generate Ad Slot & Publisher ID automatically from your adsense code', 'adsenseib30') ?>
           <label class="adsensei-label-left" for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_slot]">Ad Slot ID </label><input type="text" class="adsensei-medium-size adsensei-bggrey" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_slot]" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_slot]" value="<?php echo $g_data_ad_slot; ?>">
           <label for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_client]">Publisher ID</label><input type="text" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_client]" class="medium-text adsensei-bggrey" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_client]" value="<?php echo $g_data_ad_client; ?>">
           <br />
   <?php
   $args_adsense_type = array(
       'id' => 'adsense_type',
       'name' => 'Type',
       'desc' => 'Type',
       'options' => array(
           'normal' => 'Fixed Size',
           'responsive' => 'Responsive'
       )
   );
   echo adsensei_adense_select_callback( $id, $args_adsense_type );
   ?>
           <label class="adsensei-label-left adsensei-type-normal" for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_width]">Width </label><input type="number" step="1" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_width]" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_width]" class="small-text adsensei-type-normal" value="<?php echo $g_data_ad_width; ?>">
           <label class="adsensei-type-normal" for="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_height]">Height </label><input type="number" step="1" id="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_height]" name="adsensei_settings[ads][<?php echo $id; ?>][g_data_ad_height]" class="small-text adsensei-type-normal" value="<?php echo $g_data_ad_height; ?>">
       </div>
       <div class="adsensei-style">
           <h3>Layout</h3>
   <?php
   $args_ad_position = array(
       'id' => 'align',
       'name' => 'align',
       'desc' => 'align',
       'std' => '3',
       'options' => array(
           '3' => 'Default',
           '0' => 'Left',
           '1' => 'Center',
           '2' => 'Right'
       )
   );
   echo adsensei_adposition_callback( $id, $args_ad_position );
   echo apply_filters( 'adsensei_render_margin', '', $id ); ?>
       </div>
           <?php
           echo apply_filters( 'adsensei_advanced_settings', '', $id );
           ?>
   </div>
       <?php
    }

    /**
     *
     * Return array of alignment options
     *
     * @return array
     */
    function adsensei_get_alignment() {
       // Do not change the key => value order for compatibility reasons
       return array(
           3 => 'none',
           0 => 'left',
           1 => 'center',
           2 => 'right',
       );
    }

    /**
     * Check if plugin Clickfraud Monitoring is installed
     *
     * @return boolean true when it is installed and active
     */
    function adsensei_is_installed_clickfraud() {
       $plugin_file = 'cfmonitor/cfmonitor.php';
       $plugin_file2 = 'clickfraud-monitoring/cfmonitor.php';

       if( is_plugin_active( $plugin_file ) || is_plugin_active( $plugin_file2 ) ) {
          return true;
       }

       return false;
    }

    /**
     *
     * @param array $args array(
     * 'id' => 'string),
     * 'type' => desktop, tablet_landscape, tablet_portrait, phone
     * @return string

     */
    function adsensei_render_size_option( $args ) {
       global $adsensei_options;

       if( !isset( $args['id'] ) ) {
          return '';
       }

       $checked = isset( $adsensei_options['ads'][$args['id']][$args['type']] ) ? $adsensei_options['ads'][$args['id']][$args['type']] : '';
       $html = '<div class="adsensei-select-style-overwrite">';
       $html .= '<select class="adsensei-size-input" id="adsensei_settings[ads][' . $args['id'] . '][' . $args['type'] . ']" name="adsensei_settings[ads][' . $args['id'] . '][' . $args['type'] . ']">';
       foreach ( adsensei_get_adsense_sizes() as $key => $value ) :
          $selected = selected( $key, $checked, false );
          $html .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
       endforeach;
       $html .= '</select>';
       $html .= '</div>';

       return $html;
    }

    /**
     * Get all AdSense Sizes
     * @return array
     */
    function adsensei_get_adsense_sizes() {
       $sizes = array(
           'Auto' => 'Auto',
           '120 x 90' => '120 x 90',
           '120 x 240' => '120 x 240',
           '120 x 600' => '120 x 600',
           '125 x 125' => '125 x 125',
           '160 x 90' => '160 x 90',
           '160 x 600' => '160 x 600',
           '180 x 90' => '180 x 90',
           '180 x 150' => '180 x 150',
           '200 x 90' => '200 x 90',
           '200 x 200' => '200 x 200',
           '234 x 60' => '234 x 60',
           '250 x 250' => '250 x 250',
           '320 x 100' => '320 x 100',
           '300 x 250' => '300 x 250',
           '300 x 600' => '300 x 600',
           '300 x 1050' => '300 x 1050',
           '320 x 50' => '320 x 50',
           '336 x 280' => '336 x 280',
           '360 x 300' => '360 x 300',
           '435 x 300' => '435 x 300',
           '468 x 15' => '468 x 15',
           '468 x 60' => '468 x 60',
           '640 x 165' => '640 x 165',
           '640 x 190' => '640 x 190',
           '640 x 300' => '640 x 300',
           '728 x 15' => '728 x 15',
           '728 x 90' => '728 x 90',
           '970 x 90' => '970 x 90',
           '970 x 250' => '970 x 250',
           '240 x 400' => '240 x 400 - Regional ad sizes',
           '250 x 360' => '250 x 360 - Regional ad sizes',
           '580 x 400' => '580 x 400 - Regional ad sizes',
           '750 x 100' => '750 x 100 - Regional ad sizes',
           '750 x 200' => '750 x 200 - Regional ad sizes',
           '750 x 300' => '750 x 300 - Regional ad sizes',
           '980 x 120' => '980 x 120 - Regional ad sizes',
           '930 x 180' => '930 x 180 - Regional ad sizes',
       );

       return apply_filters( 'adsensei_adsense_size_formats', $sizes );
    }

    /**
     * Store AdSense parameters
     *
     * @return boolean
     */
   function adsensei_store_adsense_args() {
   global $adsensei_options;

   foreach ( $adsensei_options as $id => $ads ) {
      if (!is_array($ads)){
         continue;
      }
      foreach ($ads as $key => $value) {
         if( is_array( $value ) && array_key_exists( 'code', $value ) && !empty( $value['code'] ) ) {

            //check to see if it is google ad
            if( preg_match( '/googlesyndication.com/', $value['code'] ) ) {

               // Test if its google asyncron ad
               if( preg_match( '/data-ad-client=/', $value['code'] ) ) {
                  //*** GOOGLE ASYNCRON *************
                  $adsensei_options['ads'][$key]['current_ad_type'] = 'google_async';
                  //get g_data_ad_client
                  $explode_ad_code = explode( 'data-ad-client', $value['code'] );
                  preg_match( '#"([a-zA-Z0-9-\s]+)"#', $explode_ad_code[1], $matches_add_client );
                  $adsensei_options['ads'][$key]['g_data_ad_client'] = str_replace( array('"', ' '), array(''), $matches_add_client[1] );

                  //get g_data_ad_slot
                  $explode_ad_code = explode( 'data-ad-slot', $value['code'] );
                  preg_match( '#"([a-zA-Z0-9/\s]+)"#', $explode_ad_code[1], $matches_add_slot );
                  if (isset($matches_add_slot[1])){
                  $adsensei_options['ads'][$key]['g_data_ad_slot'] = str_replace( array('"', ' '), array(''), $matches_add_slot[1] );
                  }
               } else {
                  //*** GOOGLE SYNCRON *************
                  $adsensei_options['ads'][$key]['current_ad_type'] = 'google_sync';
                  //get g_data_ad_client
                  $explode_ad_code = explode( 'google_ad_client', $value['code'] );
                  preg_match( '#"([a-zA-Z0-9-\s]+)"#', $explode_ad_code[1], $matches_add_client );
                  $adsensei_options['ads'][$key]['g_data_ad_client'] = str_replace( array('"', ' '), array(''), $matches_add_client[1] );

                  //get g_data_ad_slot
                  $explode_ad_code = explode( 'google_ad_slot', $value['code'] );
                  //preg_match( '#"([a-zA-Z0-9/\s]+)"#', $explode_ad_code[1], $matches_add_slot );
                  //$adsensei_options['ads'][$key]['g_data_ad_slot'] = str_replace( array('"', ' '), array(''), $matches_add_slot[1] );
                  preg_match( '#"([a-zA-Z0-9/\s]+)"#', isset($explode_ad_code[1]) ? $explode_ad_code[1] : null, $matches_add_slot );
                  $adsensei_options['ads'][$key]['g_data_ad_slot'] = str_replace( array('"', ' '), array(''), isset($matches_add_slot[1]) ? $matches_add_slot[1] : null  );
               }
            }
         }
      }
   }
   //wp_die( var_dump( $adsensei_options ) );
   update_option( 'adsensei_settings', $adsensei_options );
}

    /**
     * Sanitizes a string key for ADSENSEI Settings
     *
     * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are allowed
     *
     * @since  2.0.0
     * @param  string $key String key
     * @return string Sanitized key
     */
    function adsensei_sanitize_key( $key ) {
       $raw_key = $key;
       $key = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );
       /**
        * Filter a sanitized key string.
        *
        * @since 2.5.8
        * @param string $key     Sanitized key.
        * @param string $raw_key The key prior to sanitization.
        */
       return apply_filters( 'adsensei_sanitize_key', $key, $raw_key );
    }

    /**
     * Multi Select Callback
     *
     * @since 1.3.8
     * @param array $args Arguments passed by the settings
     * @global $adsensei_options Array of all the ADSENSEI Options
     * @return string $output dropdown
     */
    function adsensei_multiselect_callback( $args = array() ) {
       global $adsensei_options;

       $placeholder = !empty( $args['placeholder'] ) ? $args['placeholder'] : '';
       $selected = isset( $adsensei_options[$args['id']] ) ? $adsensei_options[$args['id']] : '';
       $checked = '';

       $html = '<select id="adsensei_select_'. $args['id'] .'" name="adsensei_settings[' . $args['id'] . '][]" data-placeholder="' . $placeholder . '" style="width:550px;" multiple tabindex="4" class="adsensei-select adsensei-chosen-select">';
       $i = 0;
       foreach ( $args['options'] as $key => $value ) :
          if( is_array( $selected ) ) {
             $checked = selected( true, in_array( $key, $selected ), false );
          }
          $html .= '<option value="' . $key . '" ' . $checked . '>' . $value . '</option>';
       endforeach;
       $html .= '</select>';
       echo $html;
    }
    /**
     * Multi Select Ajax Callback
     * This adds only active elements to the array. Useful if there are a lot of elements like tags to increase performance
     *
     * @since 1.3.8
     * @param array $args Arguments passed by the settings
     * @global $adsensei_options Array of all the ADSENSEI Options
     * @return string $output dropdown
     */
    function adsensei_multiselect_ajax_callback( $args = array() ) {
       global $adsensei_options;

       $placeholder = !empty( $args['placeholder'] ) ? $args['placeholder'] : '';
       $selected = isset( $adsensei_options[$args['id']] ) ? $adsensei_options[$args['id']] : '';
       $checked = '';

       $html = '<select id="adsensei_select_'. $args['id'] .'" name="adsensei_settings[' . $args['id'] . '][]" data-placeholder="' . $placeholder . '" style="width:550px;" multiple tabindex="4" class="adsensei-select adsensei-chosen-select">';
       $i = 0;

       if (!isset($adsensei_options[$args['id']]) || !is_array( $adsensei_options[$args['id']] ) || count($adsensei_options[$args['id']]) == 0){
            $html .= '</select>';
            echo $html;
            return;
       }

       foreach ( $adsensei_options[$args['id']] as $key => $value ) {
          $html .= '<option value="' . $key . '" selected="selected">' . $value . '</option>';
       };
       $html .= '</select>';
       echo $html;
    }

    /**
     * VI Integration
     * @global type $adsensei
     *
     */
    function adsensei_vi_signup_callback() {
    global $adsensei, $adsensei_options;

            //$adsense = new \wpadsensei\adsense($adsensei_options);
            //var_dump($adsense->getPublisherIds());
            //echo 'test' . $adsense->getPublisherIds() . $adsense->writeAdsTxt();

    $header = new \wpadsensei\template('/includes/vendor/vi/views/partials/header', array());
    $footer = new \wpadsensei\template('/includes/vendor/vi/views/partials/footer', array());
    $error = new \wpadsensei\template('/includes/vendor/vi/views/error', array());

    // Try to initially load vi settings
    $settings = $adsensei->vi->getSettings();
    if ( false === $settings || empty($settings)){
        if (!$adsensei->vi->setSettings()) {
            echo $header->render();
            echo $error->render();
            echo $footer->render();
            return true;
        }
    }


    $data = !empty($adsensei->vi->getSettings()->data) ? (array) $adsensei->vi->getSettings()->data : array();

    $data['jsTag'] = $adsensei->vi->getAdCode();

    $logged_in = new \wpadsensei\template('/includes/vendor/vi/views/logged_in', $data);
    $not_logged_in = new \wpadsensei\template('/includes/vendor/vi/views/not_logged_in', $data);
    $adform = new \wpadsensei\template('/includes/vendor/vi/views/ad_settings', $data);
    $revenue = new \wpadsensei\template('/includes/vendor/vi/views/revenue', $data);

    // header
    echo $header->render();


    // Not logged in
    if (empty($data) || false === $adsensei->vi->setRevenue()) {
        return false;
    } else {
    // Is logged in
    //if ($adsensei->vi->setRevenue()) {
        echo $revenue->render();
        echo $adform->render();
    }

    // footer
    echo $footer->render();


}

/**
 * Create ads.txt for Google AdSense when saving settings
 * @return boolean
 */
    function adsensei_write_adsense_ads_txt() {
        // Get the current recently updated settings
        $adsensei_options = get_option('adsensei_settings');

        // ads.txt is disabled
        if (!isset($adsensei_options['adsTxtEnabled'])) {
            set_transient('adsensei_ads_txt_disabled', true, 100);
            delete_transient('adsensei_ads_txt_error');
            return false;
        }

        // Create AdSense ads.txt entries
        $adsense = new \wpadsensei\adsense($adsensei_options);
        if ($adsense->writeAdsTxt()){
            return true;
        } else {
            // Make sure an error message is shown when ads.txt is available but can not be modified
            // Otherwise google adsense ads are not shown
            if (is_file(ABSPATH . 'ads.txt')) {
                set_transient('adsensei_ads_txt_error', 'true', 3000);
            }
            return false;
        }
    }
    add_action('update_option_adsensei_settings', 'adsensei_write_adsense_ads_txt');


    /**
     * Periodically update ads.txt once a day for vi and adsense
     * This is to ensure that the file is recreated in case it was deleted
     * @return boolean
     */
   function updateAdsTxt(){
       global $adsensei, $adsensei_options;
        if(is_file('ads.txt') || !isset($adsensei_options['adsTxtEnabled'])){
            return false;
        }
        $adsensei->vi->createAdsTxt();
        $adsense = new wpadsensei\adsense($adsensei_options);
        $adsense->writeAdsTxt();
    }
 add_action('adsensei_daily_event', 'updateAdsTxt');

 // Start 2.0 code from here //

 function adsensei_ajax_add_ads_new($args){

   if($args['id']){

      $parameters = array();
      $parameters['adsensei_post_meta']['ad_type'] = 'adsense';
      $parameters['adsensei_post_meta']['label']   = $args['name'];
      $parameters['adsensei_post_meta']['adsensei_ad_old_id'] = 'ad'.$args['id'];

      $api_service =   new ADSENSEI_Ad_Setup_Api_Service();
      $api_service->updateAdData($parameters);

   }

 }

 // 2.0 code end here

/**
 * We are adding extra fields for user profile
 * @param type $user
 */
function adsensei_extra_user_profile_fields( $user ) {
    ?>
    <h3><?php esc_html_e("WPAdsensei Revenue Sharing", "adsensei"); ?></h3>

    <table class="form-table">
    <tr>
        <th><label for="adsensei-data-client-id"><?php esc_html_e("AdSense Publisher ID","adsensei"); ?></label></th>
        <td>
            <input placeholder="ca-pub-2005XXXXXXXXX342" type="text" name="adsensei_adsense_pub_id" id="adsensei_adsense_pub_id" value="<?php echo esc_attr( get_the_author_meta( 'adsensei_adsense_pub_id', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php esc_html_e("Please enter your pub ID.", "adsensei"); ?></span>
        </td>
    </tr>
    </table>
<?php
}
add_action( 'show_user_profile', 'adsensei_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'adsensei_extra_user_profile_fields' );

/**
 * we are saving user extra fields data in database
 * @param type $user_id
 * @return boolean
 */
function adsensei_save_extra_user_profile_fields( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
    $adsense_pub_id     = sanitize_text_field($_POST['adsensei_adsense_pub_id']);
    update_user_meta( $user_id, 'adsensei_adsense_pub_id', $adsense_pub_id );
}

add_action( 'personal_options_update', 'adsensei_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'adsensei_save_extra_user_profile_fields' );

function wp_adsensei_quick_tag() {
   if ( wp_script_is( 'quicktags' ) ) {
   ?>
<script language="javascript" type="text/javascript">
      ( function() {
         jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				'action': 'wpadsensei_ads_for_shortcode_data',
				'wpadsensei_security_nonce' :adsensei.nonce
			}

		}, 'json' )
		.done( function( data, textStatus, jqXHR ) {
         dataObj = JSON.parse(data);
         dataObj.forEach( ad_data => {
         var ad_id = ad_data.replace('[',' ').replace(']','').replace('"','').replace('"','').replace('ad','')
         //QTags.addButton( ad_data , ad_data, "[adsensei id="+ad_id+"]", '', '' );
         QTags.addButton( ad_data , ad_data, "<!--Ads"+ad_id+"-->", '', '' );
         });
		} )
} )();
	</script>
<?php
   }
}
$adsensei_mode = get_option('adsensei-mode') ? get_option('adsensei-mode') : '' ;
if( isset($adsensei_mode) && $adsensei_mode == "old" ) {
   add_action( 'admin_print_footer_scripts', 'wp_adsensei_quick_tag', 100 );
}
