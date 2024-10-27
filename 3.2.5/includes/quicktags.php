<?php
/**
 * Quicktags functions
 *
 * @package     ADSENSEI
 * @subpackage  Functions/Quicktag
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.6
 */
add_filter( 'content_edit_pre', 'adsensei_strip_quicktags_from_content' );
add_filter( 'content_save_pre', 'adsensei_strip_quicktags_from_content' );
/**
 * Removes all quicktags from content, but only if their config is already stored in post meta
 *
 * @param string $content
 * @return string Filtered content
 */
function adsensei_strip_quicktags_from_content ( $content ) {
	$ads_visibility_config = get_post_meta( get_the_ID(), '_adsensei_config_visibility', true );
	// if config exists, quicktags are handled via metabox
	// so we don't need them anymore in the content
	if ( $ads_visibility_config ) {
		$content = adsensei_strip_quicktags( $content );
	}
	return $content;
}
/**
 * Returns an array of all quicktags found in content
 *
 * @param string $content
 * @return array List of quicktags
 */
function adsensei_get_quicktags_from_content ( $content ) {
	$found = array();
	$quicktags = adsensei_quicktag_list();
	// we can use preg_match instead of multiple calls of strpos(),
	// but strpos is much faster and for such a small array should still be faster than preg_match()
	foreach ( $quicktags as $id => $label ) {
		if ( false !== strpos( $content, '<!--' . $id . '-->' ) ) {
			$found[ $id ] = 1;
		}
	}
	return $found;
}


/**
 * Removes all quicktags from content
 *
 * @param string $content
 * @return string Filtered content
 */
function adsensei_strip_quicktags ( $content ) {
	$quicktags = adsensei_quicktag_list();
	foreach ( $quicktags as $id => $label ) {
		$content = str_replace( '<!--'. $id .'-->', '', $content );
	}
	return $content;
}
/**
 * Returns list of all allowed quicktags
 *
 * @return array List of quicktags
 */
function adsensei_quicktag_list () {
	return apply_filters( 'adsensei_quicktag_list', array(
		/*'NoAds' 		=> __( 'Disable all Ads <!--NoAds-->', 'adsenseib30' ),
		'OffDef'		=> __( '<!--OffDef-->', 'adsenseib30' ),
		'OffWidget'		=> __( '<!--OffWidget-->', 'adsenseib30' ),
		'OffBegin'		=> __( '<!--OffBegin-->', 'adsenseib30' ),
		'OffMiddle'		=> __( '<!--OffMiddle-->', 'adsenseib30' ),
		'OffEnd'		=> __( '<!--OffEnd-->', 'adsenseib30' ),
		'OffAfMore'		=> __( '<!--OffAfMore-->', 'adsenseib30' ),
		'OffBfLastPara'         => __( '<!--OffBfLastPara-->', 'adsenseib30' ),*/
            	'NoAds' 		=> __( 'Hide all ads on page', 'adsenseib30' ),
		'OffDef'		=> __( 'Hide default ads, use manually placed ads', 'adsenseib30' ),
		'OffWidget'		=> __( 'Hide all ads in sidebar', 'adsenseib30' ),
		'OffBegin'		=> __( 'Hide ad on beginning', 'adsenseib30' ),
		'OffMiddle'		=> __( 'Hide ad in middle', 'adsenseib30' ),
		'OffEnd'		=> __( 'Hide ad on end', 'adsenseib30' ),
		'OffAfMore'		=> __( 'Hide ad after MoreTag', 'adsenseib30' ),
		'OffBfLastPara'         => __( 'Hide ad before last paragraph', 'adsenseib30' ),
	) );
}
