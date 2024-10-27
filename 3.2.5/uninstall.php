<?php

/**
 * Uninstall Quick adsense reloaded
 *
 * @package     adsensei
 * @subpackage  Uninstall
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
   exit;

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @return mixed
 */
function adsensei_get_option_uninstall( $key = '', $default = false ) {
   $adsensei_options = get_option( 'adsensei_settings' );
   $value = !empty( $adsensei_options[$key] ) ? $adsensei_options[$key] : $default;
   $value = apply_filters( 'adsensei_get_option', $value, $key, $default );
   return apply_filters( 'adsensei_get_option_' . $key, $value, $key, $default );
}

if( adsensei_get_option_uninstall( 'uninstall_on_delete' ) ) {
   /** Delete all the Plugin Options */
   delete_option( 'adsensei_settings' );
   delete_option( 'adsensei_install_date' );
   delete_option( 'adsensei_rating_div' );
   delete_option( 'adsensei_version' );
   delete_option( 'adsensei_version_upgraded_from' );
   delete_option( 'adsensei_show_theme_notice' );
   delete_option( 'adsensei_show_update_notice' );
   delete_option( 'adsensei_settings_1_5_2' );
   delete_option( 'adsensei_show_update_notice_1_5_2' );

   /**
    * Delete all vi settings
    */
    delete_option( 'adsensei_close_vi_welcome_notice' );
    delete_option( 'adsensei_close_vi_notice' );
    delete_option( 'adsensei_vi_ads' );
    delete_option( 'adsensei_vi_settings' );
    delete_option( 'adsensei_vi_revenue' );
    delete_option( 'adsensei_vi_variant' );
    delete_option( 'adsensei_vi_token' );

   /* Delete all post meta options */
   delete_post_meta_by_key( 'adsensei_timestamp' );
   delete_post_meta_by_key( 'adsensei_shares' );
   delete_post_meta_by_key( 'adsensei_jsonshares' );

   // Delete transients
   delete_transient( 'adsensei_check_theme' );
   delete_transient( 'adsensei_activation_redirect' );
   delete_option( 'adsensei-mode' );
   delete_option( 'adsensei_version' );
   delete_option( 'widget_adsensei_ads_widget' );
   delete_option( 'adsensei_vi_variant' );

  $arg  = array();
  $arg['post_type']      = 'adsensei-ads';
  $arg['posts_per_page'] = -1;
  $arg['post_status']    = array('publish', 'draft');
  $allposts= get_posts( $arg );
  foreach ($allposts as $eachpost) {
  wp_delete_post( $eachpost->ID, true );
  }
}
