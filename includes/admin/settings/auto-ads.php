<?php

/**
 * Add Google Auto ads settings after AD CODE tab
 * 
 * @param array $settings
 * @return array
 */
function adsensei_add_auto_ads_settings( $settings ) {
   global $adsensei_options;
   

   $more_settings = array(
       'autoads_header' => array(
           'id' => 'autoads_header',
           'name' => '<strong>' . __( 'Google Auto Ads', 'adsenseib30' ) . '</strong>',
           'desc' => '<a href="https://adsplugin.net/help/add-google-auto-ads-wordpress/" target="_blank">Read this</a> to learn how to create Google auto ads and to learn more about this new ad type. After activation, Google detects on his own where to place ads on your website. If you want to place ads manually leave auto ads empty and use the <a href="#adsensei_settingsadsense_header">regular ad codes</a> instead.<br><br> Any code that you place into this field will be added to the head of your website.',
           'type' => 'header'
       ),
       'auto_ads' => array(
           'id' => 'auto_ad_code',
           'name' => __( 'Enter Google Auto Ads code below', 'adsenseib30' ),
           'desc' => __( '', 'adsenseib30' ),
           "helper-desc" => __( '<a href="https://adsplugin.net/help/add-google-auto-ads-wordpress/" target="_blank">Read this</a> to know how to create Google auto ads and to learn more about this new ad type.', "adsenseib30" ),
           'type' => 'textarea',
           'size' => 10
       ),
       'auto_ads_pos' => array(
           'id' => 'auto_ads_pos',
           'name' => __( 'Status', 'adsenseib30' ),
           'desc' => __( '', 'adsenseib30' ),
           "helper-desc" => __( '', "adsenseib30" ),
           'type' => 'select',
           'options' => array(
               'disabled' => 'Auto Ads Disabled',
               'enabled' => 'Auto Ads Enabled',
           )
       ),
       'autoads_excl_post_types' => array(
           "id" => "autoads_post_types",
           "name" => __( "Exclude Auto Ads From Post Types", "adsenseib30" ),
           "desc" => __( "Select post types where auto ads should be disabled.", "adsenseib30" ),
           "helper-desc" => __( "Select post types where auto ads should be disabled.", "adsenseib30" ),
           "type" => "multiselect",
           "options" => adsensei_auto_ads_get_post_types(),
           "placeholder" => __( "Select Post Type", "adsenseib30" )
       ),
       'autoads_excl_extra_pages' => array(
           "id" => "autoads_extra_pages",
           "name" => __( "Exclude Auto Ads From Extra pages", "adsenseib30" ),
           "desc" => __( "Exclude Auto Ads from extra pages", "adsenseib30" ),
           "helper-desc" => __( "Exclude Auto Ads from extra pages", "adsenseib30" ),
           "type" => "multiselect",
           "options" => array('none' => 'Exclude nothing', 'homepage' => 'homepage'),
           "placeholder" => __( "Select Post Type", "adsenseib30" )
       ),
       'autoads_excl_user_roles' => array(
           "id" => "autoads_user_roles",
           "name" => __( "Exclude Auto Ads From User Roles", "adsenseib30" ),
           "desc" => __( "Exclude Auto Ads from user roles", "adsenseib30" ),
           "helper-desc" => __( "Exclude Auto Ads from user roles", "adsenseib30" ),
           "type" => "multiselect",
           "options" => array_merge(array('none' => 'Exclude nothing'), adsensei_get_user_roles()),
           "placeholder" => __( "Select Post Type", "adsenseib30" )
       ),
       
   );


   // Put them in position 100
   adsensei_array_insert( $settings, $more_settings, 21 );
   return $settings;
}

add_filter( 'adsensei_settings_general', 'adsensei_add_auto_ads_settings', 1000 );

function adsensei_auto_ads_get_post_types(){
   $post_types = get_post_types();
   
   $add = array('none' => 'Exclude nothing');
   
   return $add + $post_types;
}

