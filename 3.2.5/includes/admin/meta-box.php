<?php

/**
 * Extend the meta box
 *
 * @package     ADSENSEI\Widgets
 * @since       1.2.8
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
   exit;

add_filter( 'adsensei_meta_box_post_types', 'adsenseipro_add_extra_post_types' );
add_filter( 'adsensei_quicktag_list', 'adsensei_add_quicktags', 100 );

/**
 * Show meta settings on all available post_types
 *
 * @param array $content
 * @return array
 */
function adsenseipro_add_extra_post_types( $content ) {
   return apply_filters( 'adsenseipro_meta_box_post_types', $content );
}

/**
 * Add some extra options into the post edit meta box settings
 *
 * @param array $content
 * @return array
 */
function adsensei_add_quicktags( $content ) {

   $quicktags = array('OffAMP' => __( 'Hide all AMP ads', 'adsenseib30' ));

   return array_merge( $content, $quicktags );
}
