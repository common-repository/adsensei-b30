<?php
/*
  Plugin Name: Adsmonetizer
  Plugin URI: https://adsplugin.net
  Description: Inserta de manera fácil, rápida y flexible anuncios de AdSense por todo tu blog
  Version: 3.2.4
  Author: <a href="https://adsplugin.net" target="_blank">José Fernandez</a>
  License: GPLv2 or later
  Text Domain: adsensei-b30
  Domain Path: /languages
 */


// Exit if accessed directly
if (!defined('ABSPATH'))
  exit;


// Plugin version
if (!defined('ADSENSEI_VERSION')) {
  define('ADSENSEI_VERSION', '10');
}

// Plugin name
if (!defined('ADSENSEI_NAME')) {
  define('ADSENSEI_NAME', 'WP ADSENSEI - Adsensei');
}

// Debug
if (!defined('ADSENSEI_DEBUG')) {
  define('ADSENSEI_DEBUG', false);
}


// Define some globals
$visibleContentAds = 0; // Amount of ads which are shown
$visibleShortcodeAds = 0; // Number of active ads which are shown via shortcodes
$visibleContentAdsGlobal = 0; // Number of active ads which are shown in the_content
$ad_count_custom = 0; // Number of active custom ads which are shown on the site
$ad_count_widget = 0; // Number of active ads in widgets
$AdsId = array(); // Array of active ad id's
$maxWidgets = 10; // number of widgets

// Include the main class file
require_once(plugin_dir_path(__FILE__) . 'classAdsensei.php');
require_once(plugin_dir_path(__FILE__) . 'classAdsensei10.php');


function adsenseib30_admin_analitycs($hook)
{

  if ((isset($_GET['page']) && $_GET['page'] == 'adsensei-admin')
      || (isset($_GET['page']) && $_GET['page'] == 'category-text')
      || (isset($_GET['page']) && $_GET['page'] == 'home-text')
      || (isset($_GET['page']) && $_GET['page'] == 'ninjas-admin')
      || (isset($_GET['page']) && $_GET['page'] == 'adsensei-settings')
    ) {
    echo "<script>
    window.dataLayer = window.dataLayer || [];
    </script>
    <script>
    dataLayer.push({'env':'adsplugin', 'version': '".ADSENSEI_VERSION."'}); 
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-T2TQH3');</script>";
  }
}

add_action('admin_head', 'adsenseib30_admin_analitycs');


/**
 * Populate the $adsensei global with an instance of the AdsenseiB30 class and return it.
 *
 * @return $adsensei a global instance class of the AdsenseiB30 class.
 */
function adsensei_loaded()
{

  global $adsensei;

  if (!is_null($adsensei)) {
    return $adsensei;
  }

  $adsenseib30_settings = get_option('adsenseib30_settings');
  $category_text_settings = get_option('category_text_settings');
  $home_text_settings = get_option('home_text_settings');
  $adsenseib30_migrated = get_option('adsenseib30_migrated');
  
  $adsenseib30_settings = (isset($adsenseib30_settings) && is_array($adsenseib30_settings) && count($adsenseib30_settings) > 0) ? true: false;
  $category_text_settings = (isset($category_text_settings) && is_array($category_text_settings) && count($category_text_settings) > 0) ? true: false;
  $home_text_settings = (isset($home_text_settings) && is_array($home_text_settings) && count($home_text_settings) > 0) ? true: false;

 

  if (($adsenseib30_migrated != 1) && ($adsenseib30_settings || $category_text_settings || $home_text_settings)){
    $adsensei = adsenseib30::instance();
    return $adsensei;
  } else {
    /**
     * The activation hook is called outside of the singleton because WordPress doesn't
     * register the call from within the class hence, needs to be called outside and the
     * function also needs to be static.
     */
    register_activation_hook( __FILE__, array('AdsenseiB30', 'activation') );

    $adsensei_instance = new AdsenseiB30_10;
    $adsensei = $adsensei_instance->instance();
    return $adsensei;
  }
}

add_action('plugins_loaded', 'adsensei_loaded');



/**
 * Create a MU plugin to remove unused shortcode when plugin is removed.
 *
 * @since 1.8.12
 */
add_action('update_option_adsensei_settings', 'wpadsensei_remove_shortcode', 10, 3);
function wpadsensei_remove_shortcode($old_value, $new_value, $option)
{
  $content_url = WPMU_PLUGIN_DIR . '/wpadsensei_remove_shortcode.php';
  if (isset($new_value['hide_add_on_disableplugin'])) {
    wp_mkdir_p(WPMU_PLUGIN_DIR, 755, true);
    $sourc = plugin_dir_path(__FILE__) . 'includes/mu-plugin/wpadsensei_remove_shortcode.php';
    if (!file_exists($content_url)) {
      copy($sourc, $content_url);
    }
  } else {
    wp_delete_file($content_url);
  }
}
