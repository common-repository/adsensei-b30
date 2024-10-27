<?php
/**
 * Contextual Help
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Settings
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Settings contextual help.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function adsensei_settings_contextual_help() {
	$screen = get_current_screen();

	/*if ( $screen->id != 'adsensei-settings' )
		return;
*/
	$screen->set_help_sidebar(
		'<p><strong>' . $screen->id . sprintf( __( 'For more information:', 'adsenseib30' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Adsensei website.', 'adsenseib30' ), esc_url( 'https://wordpress.org/plugins/adsenseib30' ) ) ) . '</p>' .
		'<p>' . sprintf(
					__( '<a href="%s">Post an issue</a> on <a href="%s">Adsensei</a>. View <a href="%s">extensions</a>.', 'adsenseib30' ),
					esc_url( 'https://wordpress.org/plugins/adsenseib30' ),
					esc_url( 'https://wordpress.org/plugins/adsenseib30' ),
					esc_url( 'https://wordpress.org/plugins/adsenseib30' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'adsensei-settings-general',
		'title'	    => __( 'General', 'adsenseib30' ),
		'content'	=> '<p>' . __( 'This screen provides the most basic settings for configuring Adsensei.', 'adsenseib30' ) . '</p>'
	) );




	do_action( 'adsensei_settings_contextual_help', $screen );
}
add_action( 'load-adsensei-settings', 'adsensei_settings_contextual_help' );
