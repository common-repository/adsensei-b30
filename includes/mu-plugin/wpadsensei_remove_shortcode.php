<?php
/**
 * Plugin Name: WP ADSENSEI Shortcode Remover
 * Plugin URI: https://adsplugin.net/
 * Description: Remove WP ADSENSEI shortcode when the plugin AdSense Integration WP ADSENSEI has deactive/uninstall
 * Author: WP Adsensei
 * Author URI: https://adsplugin.net/
 * Version: 0.1
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
	exit;
// Plugin version
if( !defined( 'WP_ADSENSEI_MU_PLUGIN_VERSION' ) ) {
 define( 'WP_ADSENSEI_MU_PLUGIN_VERSION', '0.1' );
}

if(!defined( 'ADSENSEI_NAME' ) && !class_exists( 'AdsenseiB30' )){
	add_shortcode( 'adsensei_ad', 'wpadsensei_remove_unsed_shortcode', 1);
	add_shortcode( 'adsensei', 'wpadsensei_remove_unsed_shortcode', 1);
	function wpadsensei_remove_unsed_shortcode( $atts ) {
		return '';
	}
}
