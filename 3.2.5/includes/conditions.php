<?php

/**
 * Conditions
 *
 * @package     ADSENSEI
 * @subpackage  Functions/conditions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.8
 */



/**
 * Global! Determine if ads are visible
 *
 * @global arr $adsensei_options
 * @param string $content
 * @since 0.9.4
 * @return boolean true when ads are shown
 */
function adsensei_ad_is_allowed( $content = null ) {
    global $adsensei_options, $adsensei_mode;

    // Never show ads in ajax calls
    if ( isset($adsensei_options['is_ajax']) && (defined('DOING_AJAX') && DOING_AJAX) ||
         (! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]) == 'xmlhttprequest' )
        )
        {
          $theme = wp_get_theme();
          if(is_object($theme) && $theme->name == 'Bimber'){
              $bimber_theme_settings = get_option( 'bimber_theme' );
              if(isset($bimber_theme_settings['posts_auto_load_enable']) && $bimber_theme_settings['posts_auto_load_enable']){
                return true;
              }
          }
        /* it's an AJAX call */
        return false;
    }
    $hide_ads = apply_filters('adsensei_hide_ads', false);

    if(isset($adsensei_mode) && $adsensei_mode == 'new'){

         if(
            (is_feed()) ||
            (is_search()) ||
            (is_404() ) ||
            (strpos( $content, '<!--NoAds-->' ) !== false) ||
            (strpos( $content, '<!--OffAds-->' ) !== false) ||
            true === $hide_ads
    ) {
        return false;
    }
       return true;

    }


    // User Roles check
    if(!adsensei_user_roles_permission()){
       return false;
    }

    // Frontpage check
    if (is_front_page() && isset( $adsensei_options['visibility']['AppHome'] ) ){
       return true;
    }

    if(
            (is_feed()) ||
            (is_search()) ||
            (is_404() ) ||
            (strpos( $content, '<!--NoAds-->' ) !== false) ||
            (strpos( $content, '<!--OffAds-->' ) !== false) ||
            (is_front_page() && !isset( $adsensei_options['visibility']['AppHome'] ) ) ||
            (is_category() && !(isset( $adsensei_options['visibility']['AppCate'] ) ) ) ||
            (is_archive() && !( isset( $adsensei_options['visibility']['AppArch'] ) ) ) ||
            (is_tag() && !( isset( $adsensei_options['visibility']['AppTags'] ) ) ) ||
            (!adsensei_post_type_allowed()) ||
            (is_user_logged_in() && ( isset( $adsensei_options['visibility']['AppLogg'] ) ) ) ||
            true === $hide_ads
    ) {
        return false;
    }
    // else
    return true;
}

/**
 * Check if ad is allowed on extra conditions like post tags
 *
 * @global array $adsensei_options
 * @global array $post
 * @return boolean true if tag is allowed
 */
function adsensei_hide_on_tags($isActive) {
   global $adsensei_options, $post,$adsensei_mode;
   // exit early. This condition can only be valid on post tags
   if( !isset( $post ) ) {
      return false;
   }
	$global_exclude= [];
	if( $adsensei_mode == 'new' && (isset( $adsensei_options['multiTagsValue'])) ) {
	   $global_exclude = array_column($adsensei_options['multiTagsValue'], 'value');
   }else if($adsensei_mode !='new' && (isset( $adsensei_options['tags'])) ) {
	   $global_exclude = $adsensei_options['tags'];
   }
	if(empty($global_exclude)){
		return $isActive;
	}

   // Create array of slugs containing tag names
   $tagsObj = wp_get_post_tags( $post->ID );
   $tags = array();

   foreach ( $tagsObj as $key => $value ) {
      $tags[$key] = trim( $value->slug );
   }
   if(  isset( $tags ) && count( array_intersect( $global_exclude, $tags ) ) >= 1 ) {
      return true;
   }
   return $isActive;
}

add_filter( 'adsensei_hide_ads', 'adsensei_hide_on_tags', 5 );

/**
 * Hide ads per post id
 *
 * @global array $adsensei_options
 * @global array $post
 * @return boolean
 */
function adsensei_hide_on_post_id($isActive){
    global $adsensei_options, $post;

   // exit early. This condition can only be valid on post ids
    if (!isset($post) || empty($adsensei_options['excluded_id'])) {
        return $isActive;
    }

    $excluded = !empty($adsensei_options['excluded_id']) ? $adsensei_options['excluded_id'] : null;

    if (strpos($excluded, ',') !== false) {
        $excluded = explode(',', $excluded);
        if (in_array($post->ID, $excluded)) {
            return true;
        }
    }
    if ($post->ID == $excluded) {
        return true;
    }

    // default condition
    return $isActive;
}

add_filter('adsensei_hide_ads', 'adsensei_hide_on_post_id', 6);

function adsensei_user_roles_permission_new($isActive){
	global $adsensei_options;
	// No restriction. Show ads to all user_roles including public visitors without user role
	if (!isset($adsensei_options['multiUserValue'])){
		return $isActive;
	}
	$roles = wp_get_current_user()->roles;
	foreach ($adsensei_options['multiUserValue'] as $role){
		if(isset($role['value']) && isset($roles[0]) && $role['value'] == $roles[0]){
			return false;
		}
	}
	return $isActive;
}
global $adsensei_mode;
if($adsensei_mode == 'new') {
	add_filter( 'adsensei_hide_ads', 'adsensei_user_roles_permission_new', 5 );
}


/**
 * Hide ads on buddypess pages
 *
 * @global array $adsensei_options
 * @global array $post
 * @return boolean
 */
function adsensei_hide_buddypress($isActive){

	global $adsensei_options,$adsensei_mode;
	// exit early. This condition can only be valid if buddypress plugin is installed
	if (!function_exists('is_buddypress')) {
		return $isActive;
	}
	$global_exclude= [];
	if( $adsensei_mode == 'new' && (isset( $adsensei_options['multiPluginsValue'])) ) {
		$global_exclude = array_column($adsensei_options['multiPluginsValue'], 'value');
	}else if($adsensei_mode !='new' && (isset( $adsensei_options['plugins'])) ) {
		$global_exclude = $adsensei_options['plugins'];
	}
	if(empty($global_exclude)){
		return $isActive;
	}

	if ( is_array($global_exclude) && in_array('buddypress', $global_exclude) && is_buddypress() ){
		return true;
	}
	// default condition
	return $isActive;

}

add_filter('adsensei_hide_ads', 'adsensei_hide_buddypress', 7);

/**
 * Hide ads on woocommerce pages
 *
 * @global array $adsensei_options
 * @global array $post
 * @return boolean
 */
function adsensei_hide_woocommerce($isActive){
	global $adsensei_options,$adsensei_mode;
	// exit early. This condition can only be valid if buddypress plugin is installed
	if (!function_exists('is_woocommerce')) {
		return $isActive;
	}
	$global_exclude= [];
	if( $adsensei_mode == 'new' && (isset( $adsensei_options['multiPluginsValue'])) ) {
		$global_exclude = array_column($adsensei_options['multiPluginsValue'], 'value');
	}else if($adsensei_mode !='new' && (isset( $adsensei_options['plugins'])) ) {
		$global_exclude = $adsensei_options['plugins'];
	}
	if(empty($global_exclude)){
		return $isActive;
	}

	if ( is_array($global_exclude) && in_array('woocommerce', $global_exclude) && is_woocommerce() ){
		return true;
	}
	// default condition
	return $isActive;
}

add_filter('adsensei_hide_ads', 'adsensei_hide_woocommerce', 8);

function adsensei_filter_ads( $args ) {
   global $adsensei_options;

   // Get all the paragraph values[]
   $paragraph = $args['paragraph'];

   //Add some extra paragraph positions
   $number = 11; // number of paragraph ads to loop
   for ( $i = 4; $i <= $number; $i++ ) {

      $key = ($i - 4) + 1; // 1,2,3

      $paragraph['status'][$i] = isset( $adsensei_options['extra' . $key]['ParAds'] ) ? $adsensei_options['extra' . $key]['ParAds'] : 0; // Status - active | inactive
      $paragraph['id'][$i] = isset( $adsensei_options['extra' . $key]['ParRnd'] ) ? $adsensei_options['extra' . $key]['ParRnd'] : 0; // Ad id
      $paragraph['position'][$i] = isset( $adsensei_options['extra' . $key]['ParNup'] ) ? $adsensei_options['extra' . $key]['ParNup'] : 0; // Paragraph No
      $paragraph['end_post'][$i] = isset( $adsensei_options['extra' . $key]['ParCon'] ) ? $adsensei_options['extra' . $key]['ParCon'] : 0; // End of post - yes | no
   }


   for ( $i = 1; $i <= $number; $i++ ) {
      if( $paragraph['id'][$i] == 0 ) {
         $paragraph[$i] = $args['cusrnd'];
      } else {
         $paragraph[$i] = $args['cusads'] . $paragraph['id'][$i];
         array_push( $args['AdsIdCus'], $paragraph['id'][$i] );
      };
   }
   // Convert all return values into one array()
   $args = array('paragraph' => $paragraph,
       'AdsIdCus' => $args['AdsIdCus']
   );

   return $args;
}

add_filter( 'adsensei_filter_paragraphs', 'adsensei_filter_ads' );



function adsenseipro_visitor_comparison_logic_checker($result,$visibility ){
    $v_type       = $visibility['type']['value'];
    $v_id         = $visibility['value']['value'];

    switch ($v_type) {

        case 'geo_location_country':

           $adsensei_client_info = array();
           $adsensei_client_info = adsensei_get_ip_geolocation();

            if (isset($adsensei_client_info['countryCode']) && $adsensei_client_info['countryCode'] != $v_id ) {
              $result = false;
            }else{
              $result = true;
            }
        break;
        case 'geo_location_city':

           $adsensei_client_info = array();
           $adsensei_client_info = adsensei_get_ip_geolocation();
           if(isset($adsensei_client_info['city'])){
              $city_cookie = str_replace(' ','',$adsensei_client_info['city']);
              $city_cookie = strtolower($city_cookie);
              $city        = str_replace(' ','',$v_id);
              $city        = strtolower($city);
              if($city_cookie !=  $city) {
                $result = false;
              }else{
              $result = true;
            }
            }
        break;
      }
      return $result;
}

add_filter( 'adsensei_visitor_comparison_logic_checker', 'adsenseipro_visitor_comparison_logic_checker',10 ,2 );
if (!function_exists('adsensei_get_client_ip')) {
   function adsensei_get_client_ip() {

       if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))  //for cloudflare server only
           $ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
       else if (isset($_SERVER['HTTP_CLIENT_IP']))
          $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
      else if(isset($_SERVER['REMOTE_ADDR']))
          $ipaddress = $_SERVER['REMOTE_ADDR'];
      else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
          $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
      else if(isset($_SERVER['HTTP_X_FORWARDED']))
          $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
      else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
          $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
      else if(isset($_SERVER['HTTP_FORWARDED']))
          $ipaddress = $_SERVER['HTTP_FORWARDED'];
      else
          $ipaddress = 'UNKNOWN';
      return $ipaddress;
  }
}
  if (!function_exists('adsensei_get_ip_geolocation')) {
    function adsensei_get_ip_geolocation(){
      if(!is_admin()){
        global $adsensei_options;
        $user_ip      = adsensei_get_client_ip();
        $saved_ip = '';
        $saved_ip_list = array();
        $adsensei_client_info = array();
        if(isset($_COOKIE['adsensei_client_info'])){
          $saved_ip_list = json_decode(base64_decode($_COOKIE['adsensei_client_info']),true);
          $saved_ip = trim($saved_ip_list['ipaddress']);
          $adsensei_client_info['ipaddress']=$saved_ip;
          $adsensei_client_info['countryCode']=trim($saved_ip_list['countryCode']);
          $adsensei_client_info['region']=trim($saved_ip_list['region']);
          $adsensei_client_info['city']=trim($saved_ip_list['city']);
        }
          return $adsensei_client_info;
      }
    }
}

/**
 * Global! Determine if widget ads are visible
 *
 * @global arr $adsensei_options
 * @param string $content
 * @since 0.9.4
 * @return boolean true when ads are shown
 */
function adsensei_widget_ad_is_allowed( $content = null ) {
    global $adsensei_options;


    // Never show ads in ajax calls
    if ( isset($adsensei_options['is_ajax']) && (defined('DOING_AJAX') && DOING_AJAX) ||
         (! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]) == 'xmlhttprequest' )
        )
        {
        /* it's an AJAX call */
        return false;
    }

    $hide_ads = apply_filters('adsensei_hide_ads', false);

    // User Roles check
    if(!adsensei_user_roles_permission()){
       return false;
    }

    // Frontpage check
    if (is_front_page() && isset( $adsensei_options['visibility']['AppHome'] ) ){
       return true;
    }

    if(
            (is_feed()) ||
            (is_search()) ||
            (is_404() ) ||
            (strpos( $content, '<!--NoAds-->' ) !== false) ||
            (strpos( $content, '<!--OffAds-->' ) !== false) ||
            (is_category() && !(isset( $adsensei_options['visibility']['AppCate'] ) ) ) ||
            (is_archive() && !( isset( $adsensei_options['visibility']['AppArch'] ) ) ) ||
            (is_tag() && !( isset( $adsensei_options['visibility']['AppTags'] ) ) ) ||
            (!adsensei_post_type_allowed()) ||
            (is_user_logged_in() && ( isset( $adsensei_options['visibility']['AppLogg'] ) ) ) ||
            true === $hide_ads
    ) {
        return false;
    }
    // else
    return true;
}


/**
 * Check if Ad widgets are visible on homepage
 *
 * @since 0.9.7
 * return true when ad widgets are not visible on frontpage else false
 */
function adsensei_hide_ad_widget_on_homepage(){
 global $adsensei_options;

 $is_active = isset($adsensei_options["visibility"]["AppSide"]) ? true : false;

 if( is_front_page() && $is_active ){
     return true;
 }

 return false;

}


/**
 * Get the total number of active ads
 *
 * @global int $visibleShortcodeAds
 * @global int $visibleContentAdsGlobal
 * @global int $ad_count_custom
 * @global int $ad_count_widget
 * @return int number of active ads
 */
function adsensei_get_total_ad_count(){
    global $visibleShortcodeAds, $visibleContentAdsGlobal, $ad_count_custom, $ad_count_widget;

    $shortcode = isset($visibleShortcodeAds) ? (int)$visibleShortcodeAds : 0;
    $content = isset($visibleContentAdsGlobal) ? (int)$visibleContentAdsGlobal : 0;
    $custom = isset($ad_count_custom) ? (int)$ad_count_custom : 0;
    //$widget = isset($ad_count_widget) ? (int)$ad_count_widget : 0;
    $widget = adsensei_get_number_widget_ads();

    //wp_die($widget);
    //wp_die( $shortcode + $content + $custom + $widget);
    return $shortcode + $content + $custom + $widget;
}

/**
 * Check if the maximum amount of ads are reached
 *
 * @global arr $adsensei_options settings
 * @var int amount of ads to activate

 * @return bool true if max is reached
 */

function adsensei_ad_reach_max_count(){
    global $adsensei_options;

    $maxads = isset($adsensei_options['maxads']) ? $adsensei_options['maxads'] : 100;
    $maxads = $maxads - adsensei_get_number_widget_ads();

    //echo 'Total ads: '.  adsensei_get_total_ad_count() . ' maxads: '. $maxads . '<br>';
    //wp_die('Total ads: '.  adsensei_get_total_ad_count() . ' maxads: '. $maxads . '<br>');
    if ( adsensei_get_total_ad_count() >= $maxads ){
        return true;
    }
}

/**
 * Increment count of active ads generated in the_content
 *
 * @global int $ad_count
 * @param type $ad_count
 * @return int amount of active ads in the_content
 */
function adsensei_set_ad_count_content(){
    global $visibleContentAdsGlobal;

    $visibleContentAdsGlobal++;
    return $visibleContentAdsGlobal;
}

/**
 * Increment count of active ads generated with shortcodes
 *
 * @return int amount of active shortcode ads in the_content
 */
function adsensei_set_ad_count_shortcode(){
    global $visibleShortcodeAds;

    $visibleShortcodeAds++;
    return $visibleShortcodeAds;
}

/**
 * Increment count of custom active ads
 *
 * @return int amount of active custom ads
 */
function adsensei_set_ad_count_custom(){
    global $ad_count_custom;

    $ad_count_custom++;
    return $ad_count_custom;
}

/**
 * Increment count of active ads generated on widgets
 *
 * @return int amount of active widget ads
 * @deprecated since 1.4.1
 */
function adsensei_set_ad_count_widget(){
    global $ad_count_widget;

    $ad_count_widget++;
    return $ad_count_widget;
}

/**
 * Check if AMP ads are disabled on a post via the post meta box settings
 *
 * @global obj $post
 * @return boolean true if its disabled
 */
function adsensei_is_disabled_post_amp() {
    global $post;

    if(!is_singular()){
        return true;
    }

    $ad_settings = get_post_meta( $post->ID, '_adsensei_config_visibility', true );

    if( !empty( $ad_settings['OffAMP'] ) ) {
        return true;
    }
    return false;
}

function getIPAddress() {
$ip = array();
 if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
   $new_ip = $_SERVER['HTTP_CLIENT_IP'];
  }
  //whether ip is from the proxy
  elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $new_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }
  //whether ip is from the remote address
  else{
    $new_ip = $_SERVER['REMOTE_ADDR'];
  }
   $ip =  get_option('add_blocked_ip') ? get_option('add_blocked_ip') : array() ;
   array_push( $ip, array('ip'=>$new_ip,'time'=>date('l d-m-Y H:i:s') ) );
   $ip = array_values(array_column( $ip , null, 'ip' ));
  return $ip;
}

function adsensei_click_fraud_on(){
  global $adsensei_options;
  $cookie_check = true;

  if (isset($adsensei_options['click_fraud_protection']) && !empty($adsensei_options['click_fraud_protection']) && $adsensei_options['click_fraud_protection']  && isset( $_COOKIE['adsensei_ad_clicks'] ) ) {
    $adsensei_ad_click = json_decode( stripslashes( $_COOKIE['adsensei_ad_clicks'] ), true );
    $current_time = time();
    if (isset($adsensei_options['allowed_click']) && isset($adsensei_options['ban_duration']) && $adsensei_ad_click['count']  >= $adsensei_options['allowed_click'] ) {
    if(function_exists('getIPAddress') ){
      $ips = getIPAddress();
    }
    $final_ip = $ips ;
    update_option( 'add_blocked_ip', $final_ip  );
  }

    if (isset($adsensei_options['allowed_click']) && isset($adsensei_options['ban_duration']) && $adsensei_options['allowed_click'] <= $adsensei_ad_click['count'] ) {
      $cookie_check = false;
      if($current_time >= strtotime( $adsensei_ad_click['exp']. ' +'.$adsensei_options['ban_duration'].' day') ){
        $cookie_check = true;
      }else {
        if ($current_time <= strtotime( $adsensei_ad_click['exp']. ' +'.$adsensei_options['click_limit'].' hours') ) {
             $cookie_check = false;
        }
    }
  }
}
return $cookie_check;
}
//New Functions in 2.0 starts here =272;

function adsensei_is_visitor_on($ads){
    global $adsensei_options;
    $include  = array();
    $exclude  = array();
    $response = false;

    $visibility_include = isset($ads['targeting_include']) ? $ads['targeting_include'] : array();

    $visibility_exclude = isset($ads['targeting_exclude']) ? $ads['targeting_exclude'] : array();
    $check_condition_include = array_column($visibility_include, 'condition');
    $check_condition_exclude = array_column($visibility_exclude, 'condition');

  if((is_array($check_condition_include) && !empty($check_condition_include)) || (is_array($check_condition_exclude) && !empty($check_condition_exclude))){

    $include_value_old = true;
    if($visibility_include){
        $condition_old = '';
        $include_value_old = true;
        foreach ($visibility_include as $visibility){
            $condition         = isset($visibility['condition']) ? $visibility['condition'] :'AND';
            $include_value_new = adsensei_visitor_comparison_logic_checker($visibility);
            switch ($condition_old){
                case 'AND':
                    $include_value_old = $include_value_old &&  $include_value_new;
                    $condition_old = $condition;
                    break;
                case 'OR':
                    $include_value_old = $include_value_old ||  $include_value_new;
                    $condition_old = $condition;
                    break;
                default:
                    $condition_old = $condition;
                    $include_value_old =$include_value_new;
                    break;
            }
        }
    }

    $response = $include_value_old;
    if($visibility_exclude){
        $exclude_value_old = false;
        $condition_old = '';
        foreach ($visibility_exclude as $visibility){
            $condition         = isset($visibility['condition']) ? $visibility['condition'] :'AND';
            $exclude_value_new = adsensei_visitor_comparison_logic_checker($visibility);
            switch ($condition_old){
                case 'AND':
                    $exclude_value_old = $exclude_value_old &&  $exclude_value_new;
                    $condition_old = $condition;
                    break;
                case 'OR':
                    $exclude_value_old = $exclude_value_old ||  $exclude_value_new;
                    $condition_old = $condition;
                    break;
                default:
                    $condition_old = $condition;
                    $exclude_value_old =$exclude_value_new;
                    break;
            }
        }
        if($exclude_value_old){
            $response =false;
        }
    }
  }else{
    if($visibility_include){
      foreach ($visibility_include as $visibility){
         $include[] = adsensei_visitor_comparison_logic_checker($visibility);
      }
    }else{
        $response = true;
    }
    if($visibility_exclude){
      foreach ($visibility_exclude as $visibility){
          $exclude[] = adsensei_visitor_comparison_logic_checker($visibility);
      }
    }else{
      if(empty($include)){
        $response = true;
      }
    }
    if(!empty($include)){
      if(in_array( false ,$include )){
        $response = false;
      }else{
        $response = true;
      }
    }
    if(!empty($exclude)){
      $exclude =   array_filter(array_unique($exclude));
      if(isset($exclude[0])){
          $response = false;
      }
    }
  }
    return $response;

}

function adsensei_is_visibility_on($ads){
    $include  = array();
    $exclude  = array();
    $response = false;
    $visibility_include = isset($ads['visibility_include']) ? $ads['visibility_include'] : array();
    $visibility_exclude = isset($ads['visibility_exclude']) ? $ads['visibility_exclude'] : array();
    $check_condition_include = array_column($visibility_include, 'condition');
    $check_condition_exclude = array_column($visibility_exclude, 'condition');

  if((is_array($check_condition_include) && !empty($check_condition_include)) || (is_array($check_condition_exclude) && !empty($check_condition_exclude))){

    $include_value_old = true;
    if($visibility_include){
        $condition_old = '';
        $include_value_old = true;
        foreach ($visibility_include as $visibility){
            $condition         = isset($visibility['condition']) ? $visibility['condition'] :'AND';
            $include_value_new = adsensei_comparison_logic_checker($visibility);
            switch ($condition_old){
                case 'AND':
                    $include_value_old = $include_value_old &&  $include_value_new;
                    $condition_old = $condition;
                    break;
                case 'OR':
                    $include_value_old = $include_value_old ||  $include_value_new;
                    $condition_old = $condition;
                    break;
                default:
                    $condition_old = $condition;
                    $include_value_old =$include_value_new;
                    break;
            }
        }
    }
    $response = $include_value_old;
    if($visibility_exclude){
        $exclude_value_old = false;
        $condition_old = '';
        foreach ($visibility_exclude as $visibility){
            $condition         = isset($visibility['condition']) ? $visibility['condition'] :'AND';
            $exclude_value_new = adsensei_comparison_logic_checker($visibility);
            switch ($condition_old){
                case 'AND':
                    $exclude_value_old = $exclude_value_old &&  $exclude_value_new;
                    $condition_old = $condition;
                    break;
                case 'OR':
                    $exclude_value_old = $exclude_value_old ||  $exclude_value_new;
                    $condition_old = $condition;
                    break;
                default:
                    $condition_old = $condition;
                    $exclude_value_old =$exclude_value_new;
                    break;
            }
        }
        if($exclude_value_old){
            $response =false;
        }
    }
  }else{
    if($visibility_include){
      foreach ($visibility_include as $visibility){
         $include[] = adsensei_comparison_logic_checker($visibility);
      }
    }

    if($visibility_exclude){
      foreach ($visibility_exclude as $visibility){
          $exclude[] = adsensei_comparison_logic_checker($visibility);
      }
    }

    if(!empty($include)){
      $include =   array_values(array_filter(array_unique($include)));
      if(isset($include[0])){
          $response = true;
      }
    }
    if(!empty($exclude)){
      $exclude =   array_filter(array_unique($exclude));
      if(isset($exclude[0])){
          $response = false;
      }
    }
  }
      return $response;
}
add_action('wp_head', 'adsensei_set_browser_width_script');
function adsensei_set_browser_width_script(){
  if(!is_admin() && !adsensei_is_amp_endpoint()){
    echo "<script type='text/javascript'>document.cookie = 'adsensei_browser_width='+screen.width;</script>";
  }
}

function adsensei_visitor_comparison_logic_checker($visibility){

    global $post;
    $v_type       = $visibility['type']['value'];
    $v_id         = $visibility['value']['value'];
    $result       = false;
    // Get all the users registered
    $user       = wp_get_current_user();
    switch ($v_type) {

        case 'device_type':
            require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/mobile-detect.php';

            $mobile_detect = $isTablet = '';
            $mobile_detect = new Adsensei_Mobile_Detect;
            $isMobile = $mobile_detect->isMobile();
            $isTablet = $mobile_detect->isTablet();

            $device_name  = 'desktop';
            if( $isMobile && $isTablet ){ //Only For tablet
              $device_name  = 'mobile';
            }else if($isMobile && !$isTablet){ // Only for mobile
              $device_name  = 'mobile';
            }
             if($v_id == $device_name){
                $result     = true;
             }
        break;
        case 'referrer_url':
            $referrer_url  = (isset($_SERVER['HTTP_REFERER'])) ? esc_url($_SERVER['HTTP_REFERER']):'';
            if ( $referrer_url == $v_id ) {
              $result = true;
            }

        break;
        case 'browser_language':
          $browser_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
          if ( $browser_language == $v_id ) {
            $result = true;
          }
        break;
        case 'multilingual_language':
          if( class_exists('SitePress') ){
          $multilingual_language = apply_filters( 'wpml_current_language', NULL );
          if ( $multilingual_language == $v_id ) {
            $result = true;
          }
        }
        break;

        case 'url_parameter':
              $url = esc_url($_SERVER['REQUEST_URI']);
              if ( strpos($url,$v_id) !== false ) {
                $result = true;
              }
        break;

        case 'cookie':

            $cookie_arr = $_COOKIE;

            if($v_id ==''){
              if ( isset($cookie_arr) ) {
                $result = true;
              }
            }else{

            if($cookie_arr){
              foreach($cookie_arr as $key=>$value){
                if($key == $v_id){
                    $result = true;
                    break;
                }
              }
            }
          }
          break;

         case 'logged_in_visitor':
        case 'logged_in':
        if ( is_user_logged_in() ) {
              $status = 'true';
           } else {
              $status = 'false';
           }


          if ( $status == $v_id ) {
            $result = true;
          }


      break;

      case 'user_agent':
            $user_agent_name =adsensei_detect_user_agent();
            if ( $user_agent_name == $v_id ) {
              $result = true;
            }
      break;
      case 'user_type':
        if ( in_array( $v_id, (array) $user->roles ) ) {
            $result = true;
        }
        break;
      case 'browser_width':
       if(isset($_COOKIE['adsensei_browser_width']) && $_COOKIE['adsensei_browser_width'] == $v_id){
          $result = true;
        }
        break;
    default:
      $result = false;
      break;
  }

 $result  = apply_filters( 'adsensei_visitor_comparison_logic_checker', $result ,$visibility);

return $result;
}

function adsensei_detect_user_agent( ){
        $user_agent_name ='others';
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') || strpos($user_agent_name, 'OPR/')) $user_agent_name = 'opera';
        elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge'))    $user_agent_name = 'edge';
        elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) $user_agent_name ='firefox';
        elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strpos($user_agent_name, 'Trident/7')) $user_agent_name = 'internet_explorer';
        elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'iPod')) $user_agent_name = 'ipod';
        elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'iPhone')) $user_agent_name = 'iphone';
        elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'iPad')) $user_agent_name = 'ipad';
        elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'Android')) $user_agent_name = 'android';
        elseif (stripos($_SERVER['HTTP_USER_AGENT'], 'webOS')) $user_agent_name = 'webos';
        elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome'))  $user_agent_name = 'chrome';
        elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari'))  $user_agent_name = 'safari';

        return $user_agent_name;
}


function adsensei_comparison_logic_checker($visibility){

    global $post;

    $v_type       = $visibility['type']['value'];
    $v_id         = isset($visibility['value']['value']) ? $visibility['value']['value'] :'';
    $result       = false;
    if(!is_object($post)){
      return false;
    }
    // Get all the users registered
    $user       = wp_get_current_user();

    switch ($v_type) {

    // Basic Controls ------------
      // Posts Type
    case 'post_type':

             $post_type  = get_post_type($post->ID);

             if($v_id == $post_type && is_singular()){
                $result     = true;
             }

      break;


      // Posts
    case 'general':

         if( ($v_id == 'homepage') && is_home() || is_front_page() || ( function_exists('ampforwp_is_home') && ampforwp_is_home()) ){
            $result     = true;
         }

         if($v_id == 'show_globally'){
            $result     = true;
         }

    break;

  // Logged in User Type
    case 'user_type':

            if ( in_array( $v_id, (array) $user->roles ) ) {
                $result = true;
            }

       break;

// Post Controls  ------------
  // Posts
    case 'post':

        if($v_id == $post->ID && is_singular()){
            $result = true;
        }


    break;

  // Post Category
    case 'post_category':

      $current_category = '';

      if(is_object($post)){

          $postcat = get_the_category( $post->ID );
            if(!empty($postcat)){
                if(is_object($postcat[0])){
                  $current_category = $postcat[0]->cat_ID;
                }
            }

      }
      if($v_id == $current_category){
          $result = true;
      }

    break;
  // Post Format
    case 'post_format':

      $current_post_format = get_post_format( $post->ID );

      if ( $current_post_format === false ) {
          $current_post_format = 'standard';
      }
      if($v_id == $current_post_format){
        $result = true;
      }
    break;

    case 'page':
        global $wp_query;
        $page_id = $wp_query->get_queried_object_id();
        if($v_id == $page_id){
            $result = true;
        }

    break;

    case 'tags':

        if ( has_tag( $v_id) ) {
            $result = true;
        }

    break;


    case 'ef_taxonomy':

    $taxonomy_names = get_post_taxonomies( $post->ID );

    $post_terms = '';

      if ( $v_id != 'all') {

        $post_terms = wp_get_post_terms($post->ID, $v_id);

        if ( $post_terms ) {
            $result = true;
        }

      } else {

          if ( $taxonomy_names ) {
              $result = true;
          }
      }
    break;
    case 'page_template':
          $object = get_queried_object();
          $template = get_page_template_slug($object);
          if($v_id == $template){
            $result = true;
          }
      break;

  default:
    $result = false;
    break;
}

return $result;
}


//New Functions in 2.0 ends here
