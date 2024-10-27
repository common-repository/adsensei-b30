<?php
/**
 * Register Settings
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Settings
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
add_filter( 'adsensei_advanced_settings', 'adsensei_advanced_settings', 10, 2 );

function adsensei_advanced_settings( $content, $id ) {
   global $adsensei_options, $adsensei;

   $html = '<div class="adsensei-advanced-ad-box">';
   $html .= '<h3>Advanced Options</h3>';
   $html .= '<div>' . __( '<i>Auto</i> creates a responsive ad with automatic size detection. You can overwrite the size on specific devices with manual selecting a fixed size value. <br><strong>Responsive Ad not shown?</strong>  Switch Layout to <i>Default</i> or select a fixed ad size here.<br><br>', 'adsenseib30' ) . '</div>';
   $html .= '<div class="adsensei-left-box">';
   $html .= '<div class="adsensei-advanced-description"><label for="adsensei_settings[ads][' . $id . '][desktop]">' . __( 'Disable on Desktop ', 'adsenseib30' ) . '</label></div>' . $adsensei->html->checkbox( array('name' => 'adsensei_settings[ads][' . $id . '][desktop]', 'current' => !empty( $adsensei_options['ads'][$id]['desktop'] ) ? $adsensei_options['ads'][$id]['desktop'] : null, 'class' => 'adsensei-checkbox') );
   $html .= '<div class="adsensei-advanced-description"><label for="adsensei_settings[ads][' . $id . '][tablet_landscape]">' . __( 'Disable on Tablet Landscape ', 'adsenseib30' ) . '</label></div>' . $adsensei->html->checkbox( array('name' => 'adsensei_settings[ads][' . $id . '][tablet_landscape]', 'current' => !empty( $adsensei_options['ads'][$id]['tablet_landscape'] ) ? $adsensei_options['ads'][$id]['tablet_landscape'] : null, 'class' => 'adsensei-checkbox') );
   $html .= '<div class="adsensei-advanced-description"><label for="adsensei_settings[ads][' . $id . '][tablet_portrait]">' . __( 'Disable on Tablet Portrait ', 'adsenseib30' ) . '</label></div>' . $adsensei->html->checkbox( array('name' => 'adsensei_settings[ads][' . $id . '][tablet_portrait]', 'current' => !empty( $adsensei_options['ads'][$id]['tablet_portrait'] ) ? $adsensei_options['ads'][$id]['tablet_portrait'] : null, 'class' => 'adsensei-checkbox') );
   $html .= '<div class="adsensei-advanced-description"><label for="adsensei_settings[ads][' . $id . '][phone]">' . __( 'Disable on Phone  ', 'adsenseib30' ) . '</label></div>' . $adsensei->html->checkbox( array('name' => 'adsensei_settings[ads][' . $id . '][phone]', 'current' => !empty( $adsensei_options['ads'][$id]['phone'] ) ? $adsensei_options['ads'][$id]['phone'] : null, 'class' => 'adsensei-checkbox') );
   $html .= '<div class="adsensei-advanced-description adsensei-amp"><label for="adsensei_settings[ads][' . $id . '][amp]">' . __( 'Activate on AMP  ', 'adsenseib30' ) . '<a class="adsensei-helper" href="#"></a><div class="adsensei-message">Activate this advert on AMP pages. Any AMP plugin is required. To test if the AMP ad is working it\'s required to open your site on mobile device. Ads are not shown on other devices! </div></label></div>' . $adsensei->html->checkbox( array('name' => 'adsensei_settings[ads][' . $id . '][amp]', 'current' => !empty( $adsensei_options['ads'][$id]['amp'] ) ? $adsensei_options['ads'][$id]['amp'] : null, 'class' => 'adsensei-checkbox adsensei-activate-amp') );
   $html .= '</div>';
   $html .= '<div class="adsensei-sizes-container">';
   $html .= '<div class="adsensei-sizes">';
   $html .= '<span class="adsense-size-title">' . __( 'Desktop Size: ', 'adsenseib30' ) . '</span>' . adsensei_render_size_option( array('id' => $id, 'type' => 'desktop_size') );
   $html .= '<span class="adsense-size-title">' . __( 'Tablet Size: ', 'adsenseib30' ) . '</span>' . adsensei_render_size_option( array('id' => $id, 'type' => 'tbl_lands_size') );
   $html .= '<span class="adsense-size-title">' . __( 'Tablet Size: ', 'adsenseib30' ) . '</span>' . adsensei_render_size_option( array('id' => $id, 'type' => 'tbl_portr_size') );
   $html .= '<span class="adsense-size-title">' . __( 'Phone Size: ', 'adsenseib30' ) . '</span>' . adsensei_render_size_option( array('id' => $id, 'type' => 'phone_size') );
   $html .= '</div>';
   $html .= '</div>';
   $html .= '<div class="adsensei-advanced-description adsensei-amp">' . $adsensei->html->textarea( array('id' => 'adsensei_settings[ads][' . $id . '][amp_code]', 'name' => 'adsensei_settings[ads][' . $id . '][amp_code]', 'class' => 'adsensei-amp-code', 'value' => !empty( $adsensei_options['ads'][$id]['amp_code'] ) ? $adsensei_options['ads'][$id]['amp_code'] : '', 'placeholder' => 'Optional: Add any custom AMP ad code here. Must not be exclusive AdSense - If it\'s left empty, an AdSense AMP ad with size 300x250 is automatically generated.') ) . '</div>';
   $html .='</div>';
   $html .='<div>';
   $html .='<a href="#" class="adsensei-delete-ad">' . __( 'Delete Ad', 'adsenseib30' ) . '</a><br>';
   $html .='</div>';

   return $html;
}

/**
 * Add custom ad sizes to the list of available ad sizes
 *
 * @param type $content
 * @return type
 */
function adsensei_custom_banner_formats( $sizes ) {
   global $adsensei_options;

   $content = !empty( $adsensei_options['custom_ad_sizes'] ) ? $adsensei_options['custom_ad_sizes'] : '';

   if( empty( $content ) ) {
      return $sizes;
   }

   $custom_ad_sizes = explode( ',', $content );

   if( is_array( $custom_ad_sizes ) && !empty( $custom_ad_sizes ) ) {
      foreach ( $custom_ad_sizes as $value ) {
         $ad_sizes[$value] = $value . ' Custom Size';
      }
      return array_merge( $sizes, $ad_sizes );
   }
}

add_filter( 'adsensei_adsense_size_formats', 'adsensei_custom_banner_formats' );

/**
 * Add more paragraph options
 *
 * @param type $content
 * @return string
 */
function adsensei_add_more_paragraph_settings( $content ) {
   global $adsensei, $adsensei_options;
   // Extra position
   $html = $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra1][ParAds]', 'current' => !empty( $adsensei_options['extra1']['ParAds'] ) ? $adsensei_options['extra1']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra1][ParRnd]', 'name' => 'adsensei_settings[extra1][ParRnd]', 'selected' => !empty( $adsensei_options['extra1']['ParRnd'] ) ? $adsensei_options['extra1']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra1][ParNup]', 'name' => 'adsensei_settings[extra1][ParNup]', 'selected' => !empty( $adsensei_options['extra1']['ParNup'] ) ? $adsensei_options['extra1']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra1][ParCon]', 'current' => !empty( $adsensei_options['extra1']['ParCon'] ) ? $adsensei_options['extra1']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';
   // Extra position
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra2][ParAds]', 'current' => !empty( $adsensei_options['extra2']['ParAds'] ) ? $adsensei_options['extra2']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra2][ParRnd]', 'name' => 'adsensei_settings[extra2][ParRnd]', 'selected' => !empty( $adsensei_options['extra2']['ParRnd'] ) ? $adsensei_options['extra2']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra2][ParNup]', 'name' => 'adsensei_settings[extra2][ParNup]', 'selected' => !empty( $adsensei_options['extra2']['ParNup'] ) ? $adsensei_options['extra2']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra2][ParCon]', 'current' => !empty( $adsensei_options['extra2']['ParCon'] ) ? $adsensei_options['extra2']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';
   // Extra position
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra3][ParAds]', 'current' => !empty( $adsensei_options['extra3']['ParAds'] ) ? $adsensei_options['extra3']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra3][ParRnd]', 'name' => 'adsensei_settings[extra3][ParRnd]', 'selected' => !empty( $adsensei_options['extra3']['ParRnd'] ) ? $adsensei_options['extra3']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra3][ParNup]', 'name' => 'adsensei_settings[extra3][ParNup]', 'selected' => !empty( $adsensei_options['extra3']['ParNup'] ) ? $adsensei_options['extra3']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra3][ParCon]', 'current' => !empty( $adsensei_options['extra3']['ParCon'] ) ? $adsensei_options['extra3']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';

   // Extra position
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra4][ParAds]', 'current' => !empty( $adsensei_options['extra4']['ParAds'] ) ? $adsensei_options['extra4']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra4][ParRnd]', 'name' => 'adsensei_settings[extra4][ParRnd]', 'selected' => !empty( $adsensei_options['extra4']['ParRnd'] ) ? $adsensei_options['extra4']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra4][ParNup]', 'name' => 'adsensei_settings[extra4][ParNup]', 'selected' => !empty( $adsensei_options['extra4']['ParNup'] ) ? $adsensei_options['extra4']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra4][ParCon]', 'current' => !empty( $adsensei_options['extra4']['ParCon'] ) ? $adsensei_options['extra4']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';
   // Extra position
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra5][ParAds]', 'current' => !empty( $adsensei_options['extra5']['ParAds'] ) ? $adsensei_options['extra5']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra5][ParRnd]', 'name' => 'adsensei_settings[extra5][ParRnd]', 'selected' => !empty( $adsensei_options['extra5']['ParRnd'] ) ? $adsensei_options['extra5']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra5][ParNup]', 'name' => 'adsensei_settings[extra5][ParNup]', 'selected' => !empty( $adsensei_options['extra5']['ParNup'] ) ? $adsensei_options['extra5']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra5][ParCon]', 'current' => !empty( $adsensei_options['extra5']['ParCon'] ) ? $adsensei_options['extra5']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';
   // Extra position
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra6][ParAds]', 'current' => !empty( $adsensei_options['extra3']['ParAds'] ) ? $adsensei_options['extra6']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra6][ParRnd]', 'name' => 'adsensei_settings[extra6][ParRnd]', 'selected' => !empty( $adsensei_options['extra6']['ParRnd'] ) ? $adsensei_options['extra6']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra6][ParNup]', 'name' => 'adsensei_settings[extra6][ParNup]', 'selected' => !empty( $adsensei_options['extra6']['ParNup'] ) ? $adsensei_options['extra6']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra6][ParCon]', 'current' => !empty( $adsensei_options['extra6']['ParCon'] ) ? $adsensei_options['extra6']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';
   // Extra position
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra7][ParAds]', 'current' => !empty( $adsensei_options['extra7']['ParAds'] ) ? $adsensei_options['extra7']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra7][ParRnd]', 'name' => 'adsensei_settings[extra7][ParRnd]', 'selected' => !empty( $adsensei_options['extra7']['ParRnd'] ) ? $adsensei_options['extra7']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra7][ParNup]', 'name' => 'adsensei_settings[extra7][ParNup]', 'selected' => !empty( $adsensei_options['extra7']['ParNup'] ) ? $adsensei_options['extra7']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra7][ParCon]', 'current' => !empty( $adsensei_options['extra7']['ParCon'] ) ? $adsensei_options['extra7']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';
   // Extra position
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra8][ParAds]', 'current' => !empty( $adsensei_options['extra8']['ParAds'] ) ? $adsensei_options['extra8']['ParAds'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_ads(), 'id' => 'adsensei_settings[extra8][ParRnd]', 'name' => 'adsensei_settings[extra8][ParRnd]', 'selected' => !empty( $adsensei_options['extra8']['ParRnd'] ) ? $adsensei_options['extra8']['ParRnd'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '<strong>After Paragraph</strong>', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->select( array('options' => adsensei_get_values(), 'id' => 'adsensei_settings[extra8][ParNup]', 'name' => 'adsensei_settings[extra8][ParNup]', 'selected' => !empty( $adsensei_options['extra8']['ParNup'] ) ? $adsensei_options['extra8']['ParNup'] : null, 'show_option_all' => false, 'show_option_none' => false) );
   $html .= ' ' . __( '→', 'adsenseib30' ) . ' ';
   $html .= $adsensei->html->checkbox( array('name' => 'adsensei_settings[extra8][ParCon]', 'current' => !empty( $adsensei_options['extra8']['ParCon'] ) ? $adsensei_options['extra8']['ParCon'] : null, 'class' => 'adsensei-checkbox adsensei-assign') );
   $html .= ' ' . __( 'to <strong>End of Post</strong> if fewer paragraphs are found.', 'adsenseib30' ) . ' </br>';
   return $html;
}

add_filter( 'adsensei_extra_paragraph', 'adsensei_add_more_paragraph_settings' );

/**
 * Add more advanced settings
 *
 * @param array $settings
 * @return array
 */
function adsensei_add_advanced_settings( $settings ) {
   global $adsensei_options;

   $more_settings = array(
       'excluded_id' => array(
           "id" => "excluded_id",
           "name" => __( "Hide Ads for Post ID's", "adsenseib30" ),
           "desc" => __( "", "adsenseib30" ),
           "helper-desc" => __( "Enter post id's separated by comma that can not see any ads, e.g. 0,1,5,6", "adsenseib30" ),
           "type" => "text",
       ),
       'user_roles' => array(
           "id" => "user_roles",
           "name" => __( "Hide Ads for User Roles", "adsenseib30" ),
           "desc" => __( "Select user roles that can not see any ads. If nothing is set ads are visible for all user roles including public visitors.", "adsenseib30" ),
           "helper-desc" => __( "Select user roles that can not see any ads. If nothing is set ads are visible for all user roles including public visitors.", "adsenseib30" ),
           "type" => "multiselect",
           "options" => adsensei_get_user_roles(),
           "placeholder" => __( "Select User Roles", "adsenseib30" ),
           "std" => __( "All Roles", "adsenseib30" )
       ),
       'tags' => array(
           "id" => "tags",
           "name" => __( "Hide Ads for Tags", "adsenseib30" ),
           "desc" => __( "Select tags where ads are not shown. If nothing is set ads are shown for all post tags.", "adsenseib30" ),
           "helper-desc" => __( "Select tags where ads are not shown. If nothing is set ads are shown for all post tags.", "adsenseib30" ),
           "type" => "multiselect_ajax",
           //"options" => adsensei_get_tags(),
           "options" => array(),
           "placeholder" => __( "Select Post Tags", "adsenseib30" ),
           "std" => __( "All Tags", "adsenseib30" )
       ),
       'plugins' => array(
           "id" => "plugins",
           "name" => __( "Hide Ads for plugins", "adsenseib30" ),
           "desc" => __( "", "adsenseib30" ),
           "helper-desc" => __( "Hide ads on plugin specific pages", "adsenseib30" ),
           "type" => "multiselect",
           "options" => array('buddypress' => 'buddypress', 'woocommerce' => 'woocommerce'),
       ),
       'custom_ad_sizes' => array(
           'id' => 'custom_ad_sizes',
           'name' => __( 'Custom Banner Sizes', 'adsenseib30' ),
           'desc' => '<br>' . __( 'Add more banner formats separated by comma. e.g. 600 x 100, 400 x 50', 'adsenseib30' ),
           'type' => 'textarea',
           'size' => 3
       ),
       'adlabel' => array(
           'id' => 'adlabel',
           'name' => __( 'Ad label', 'adsenseib30' ),
           'desc' => __( 'Add Label <i>Advertisement</i> above or below ads', 'adsenseib30' ),
           'type' => 'select',
           'options' => array(
               'none' => 'No Label',
               'above' => 'Above Ads',
               'below' => 'Below Ads',
           )
       ),
       array(
           'id' => 'ignoreShortcodeCond',
           'name' => 'Ignore Conditions for Shortcodes',
           'helper-desc' => 'Activate this to ignore above display conditions for post shortcodes like [adsensei]. Using a shortcode will result in showing of the ad, no matter if there is any display condition which usually would prevent this.',
           'type' => 'checkbox'
       )
   );

   // Put them in position 5
   adsensei_array_insert( $settings, $more_settings, 5 );
   return $settings;
}

add_filter( 'adsensei_settings_general', 'adsensei_add_advanced_settings', 1000 );

/**
 * Add more settings under tab Plugin Settings
 *
 * @param array $settings
 * @return array
 */
function adsensei_add_plugin_settings( $settings ) {
   global $adsensei_options;

   $more_settings = array(
       'analytics' => array(
           'id' => 'analytics',
           'name' => __( 'Google Analytics Integration', 'adsenseib30' ),
           'desc' => __( 'Enable', 'adsenseib30' ),
           "helper-desc" => __( "Check how many visitors are using ad blockers in your Google Analytics account from the event tracking in <i>Google Analytics->Behavior->Events</i>. This only works if your visitors are using regular ad blockers like 'adBlock'. There are browser plugins which block all external requests like the  software uBlock origin. This also block google analytics and as a result you do get any analytics data at all.", "adsenseib30" ),
           'type' => 'checkbox'
       ),
       'ad_blocker_message' => array(
           'id' => 'ad_blocker_message',
           'name' => __( 'Ask user to deactivate ad blocker', 'adsenseib30' ),
           'desc' => __( 'Enable', 'adsenseib30' ),
           "helper-desc" => sprintf( __( "If visitor is using an ad blocker he will see a message instead of an ad, asking him to deactivate the ad blocker. <a href='%s' target='_blank'>Read here</a> how to customize colors and text.", "adsenseib30" ), 'http://wpadsensei.com/docs/customize-ad-blocker-notice/' ),
           'type' => 'checkbox'
       )
   );


   // Put them in position 100
   adsensei_array_insert( $settings, $more_settings, 100 );
   return $settings;
}

add_filter( 'adsensei_settings_general', 'adsensei_add_plugin_settings', 1000 );

/**
 * Put array into specific position in another array
 *
 *
 * @param array      $array
 * @param int|string $position
 * @param mixed      $insert
 */
function adsensei_array_insert( &$array, $insert, $position ) {
   settype( $array, "array" );
   settype( $insert, "array" );
   settype( $position, "int" );

//if pos is start, just merge them
   if( $position == 0 ) {
      $array = array_merge( $insert, $array );
   } else {

      //if pos is end just merge them
      if( $position >= (count( $array ) - 1) ) {
         $array = array_merge( $array, $insert );
      } else {
         //split into head and tail, then merge head+inserted bit+tail
         $head = array_slice( $array, 0, $position );
         $tail = array_slice( $array, $position );
         $array = array_merge( $head, $insert, $tail );
      }
   }
}

/**
 * Render extra margin fields
 *
 * @global array $adsensei_options
 * @param string $content
 * @param int $id
 */
function adsensei_render_margins( $content, $id ) {
   global $adsensei_options;

   // One margin value rules the world. This is the default option since releasing of the free version
   if( empty( $adsensei_options['ads'][$id]['margin-left'] ) &&
           empty( $adsensei_options['ads'][$id]['margin-top'] ) &&
           empty( $adsensei_options['ads'][$id]['margin-right'] ) &&
           empty( $adsensei_options['ads'][$id]['margin-bottom'] ) &&
           !empty( $adsensei_options['ads'][$id]['margin'] ) ) {
      // Get old margin values depending on the alignment option
      $align = isset( $adsensei_options['ads'][$id]['align'] ) ? $adsensei_options['ads'][$id]['align'] : '3'; // 3 is default

      switch ( $align ) {
         case '0':
            $top = $adsensei_options['ads'][$id]['margin'];
            $right = $adsensei_options['ads'][$id]['margin'];
            $bottom = $adsensei_options['ads'][$id]['margin'];
            $left = '0';
            break;
         case '1':
            $top = $adsensei_options['ads'][$id]['margin'];
            $right = '0';
            $bottom = $adsensei_options['ads'][$id]['margin'];
            $left = '0';
            break;
         case '2':
            $top = $adsensei_options['ads'][$id]['margin'];
            $right = '0';
            $bottom = $adsensei_options['ads'][$id]['margin'];
            $left = $adsensei_options['ads'][$id]['margin'];
            break;
         case '3':
            $top = '0';
            $right = '0';
            $bottom = '0';
            $left = '0';
            break;
      }
   } else {
      // New margin setting allows control of all four positions. Since WP ADSENSEI PRO 1.2.7
      $top = isset( $adsensei_options['ads'][$id]['margin-top'] ) ? $adsensei_options['ads'][$id]['margin-top'] : '';
      $right = isset( $adsensei_options['ads'][$id]['margin-right'] ) ? $adsensei_options['ads'][$id]['margin-right'] : '';
      $bottom = isset( $adsensei_options['ads'][$id]['margin-bottom'] ) ? $adsensei_options['ads'][$id]['margin-bottom'] : '';
      $left = isset( $adsensei_options['ads'][$id]['margin-left'] ) ? $adsensei_options['ads'][$id]['margin-left'] : '';
   }
   ?>
   <br />
   <label><?php _e( 'Margin', 'adsenseib30' ); ?> &nbsp;&nbsp;&nbsp;&nbsp; <?php _e( 'Top:', 'adsenseib30' ); ?> </label><input type="number" step="1" max="" min="" class="small-text" id="adsensei_settings[ads][<?php echo $id; ?>][margin-top]" name="adsensei_settings[ads][<?php echo $id; ?>][margin-top]" value="<?php echo esc_attr( stripslashes( $top ) ); ?>"/>px
   <label style="margin-left:10px;"><?php _e( 'Right:', 'adsenseib30' ); ?> </label><input type="number" step="1" max="" min="" class="small-text" id="adsensei_settings[ads][<?php echo $id; ?>][margin-right]" name="adsensei_settings[ads][<?php echo $id; ?>][margin-right]" value="<?php echo esc_attr( stripslashes( $right ) ); ?>"/>px
   <label style="margin-left:10px;"><?php _e( 'Bottom:', 'adsenseib30' ); ?> </label> <input type="number" step="1" max="" min="" class="small-text" id="adsensei_settings[ads][<?php echo $id; ?>][margin-bottom]" name="adsensei_settings[ads][<?php echo $id; ?>][margin-bottom]" value="<?php echo esc_attr( stripslashes( $bottom ) ); ?>"/>px
   <label style="margin-left:10px;"><?php _e( 'Left:', 'adsenseib30' ); ?> </label> <input type="number" step="1" max="" min="" class="small-text" id="adsensei_settings[ads][<?php echo $id; ?>][margin-left]" name="adsensei_settings[ads][<?php echo $id; ?>][margin-left]" value="<?php echo esc_attr( stripslashes( $left ) ); ?>"/>px

   <?php
}

add_filter( 'adsensei_render_margin', 'adsensei_render_margins', 2, 1000 );

/**
 *
 * Get all user roles
 *
 * @global array $wp_roles
 * @return array
 */
function adsensei_get_user_roles() {
   global $wp_roles;
   $roles = array();

   foreach ( $wp_roles->roles as $role ) {
      //if( isset( $role["capabilities"]["edit_posts"] ) && $role["capabilities"]["edit_posts"] === true ) {
      $value = str_replace( ' ', '', strtolower( $role["name"] ) );
      $roles[$value] = $role["name"];
      //}
   }
   return $roles;
}

/**
 * Add more post_types to default ones
 * @param array $default_post_types
 * @return array
 */
function adsensei_add_more_post_types( $default_post_types ) {
   $post_types = get_post_types();
   return $post_types;
}

add_filter( 'adsensei_post_types', 'adsensei_add_more_post_types' );

/**
 *
 * Get all available tags
 *
 * @global array $wp_roles
 * @return array
 */
function adsensei_get_tags() {
   $tags = get_tags();
   $new_tags = array();
   //wp_die(var_dump($tags));
   foreach ( $tags as $key => $value ) {
      //$new_tags[$key]['term_id'] = $value->term_id;
      $new_tags[$key][$value->slug] = $value->name;
   }
   $new_tags = adsensei_flatten( $new_tags );
   //wp_die(var_dump($new_tags));
   return $new_tags;
   //wp_die(var_dump($new_tags));
}
