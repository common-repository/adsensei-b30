<?php

namespace wpadsensei\scripts;

/**
 * Scripts
 *
 * @package     ADSENSEI
 * @subpackage  Functions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.6
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;


add_action( 'admin_enqueue_scripts', '\\wpadsensei\\scripts\\adsensei_admin_scripts', 100 );


/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function adsensei_admin_scripts( $hook ) {
    if( !apply_filters( '\\wpadsensei\\scripts\\adsensei_admin_scripts', adsensei_is_admin_page(), $hook ) ) {
        return;
    }
    global $wp_version;


    $js_dir = ADSENSEI_PLUGIN_URL . 'assets/js/';

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( adsenseiIsDebugMode() ) ? '' : '.min';

    // These have to be global
    wp_enqueue_script( 'adsensei-admin-pro-scripts', $js_dir . 'adsensei-pro-admin' . $suffix . '.js', array('jquery'), ADSENSEI_VERSION, false );
    wp_enqueue_script( 'adsensei-chosen-ajaxaddition', $js_dir . 'chosen.ajaxaddition.jquery.js', array('jquery'), ADSENSEI_VERSION, false );
}
