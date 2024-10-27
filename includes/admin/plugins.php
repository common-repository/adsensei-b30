<?php
/**
 * Admin Plugins
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Plugins row action links
 *
 * @since 2.0
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function adsensei_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=adsensei-settings' ) . '">' . esc_html__( 'General Settings', 'adsenseib30' ) . '</a>';

	if ( $file == 'adsenseib30/adsenseib30.php' ){
		array_unshift( $links, $settings_link );
  }

	return $links;
}
add_filter( 'plugin_action_links', 'adsensei_plugin_action_links', 10, 2 );

function adsensei_premium_plugin_action_links( $links, $file ){

		$settings_link = array( 'settings'=> '<a href="https://adsplugin.net/">' . esc_html__( 'Premium Features', 'adsenseib30' ) . '</a> | <a href="https://adsplugin.net/help/">' . esc_html__( 'Support', 'adsenseib30' ) . '</a>' );
		if ( $file == 'adsenseib30/adsenseib30.php' ){
			$links = array_merge( $links, $settings_link );
		}

		return $links;

	}

add_filter('plugin_action_links', 'adsensei_premium_plugin_action_links', 10, 2);
