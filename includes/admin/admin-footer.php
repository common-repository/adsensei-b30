<?php
/**
 * Admin Footer
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Footer
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add rating links to the admin dashboard
 *
 * @since	1.0.0
 * @global	string $typenow
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function adsensei_admin_rate_us( $footer_text ) {
	global $typenow;

	if ( adsensei_is_admin_page() ) {
		$rate_text = sprintf( __( '<strong>Please do us a BIG favor and give us a 5 star rating <a href="%1$s" target="_blank">here</a>. If you have issues, open a <a href="%2s" target="_blank">support ticket</a>, so that we can sort it out. Thank you!</strong>', 'adsenseib30' ),
			'https://wordpress.org/support/plugin/adsenseib30/reviews/?filter=5#new-post',
			'http://wpadsensei.com/support/'
		);
	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'adsensei_admin_rate_us' );
