<?php

namespace wpadsensei\vendor\google;

class AutoAds {

   /**
    *
    * @var array
    */
   var $settings;

   public function __construct() {
      add_action( 'wp_head', array($this, 'renderAutoAds') );
      $this->getSettings();
   }

   private function getSettings() {
      global $adsensei_options;

      if( !isset( $adsensei_options ) ) {
         return array();
      }

      $this->settings = $adsensei_options;
   }

   public function renderAutoAds() {

      if( !$this->isAllowed() ) {
         return false;
      }

      echo do_shortcode($this->settings['auto_ad_code']);
   }

   /**
    * Check if ad is allowed to show
    * @return boolean
    */
   private function isAllowed() {
      if( empty( $this->settings['auto_ad_code'] ) ) {
         return false;
      }

      if( empty( $this->settings['auto_ads_pos'] ) || (isset( $this->settings['auto_ads_pos'] ) && $this->settings['auto_ads_pos'] == 'disabled') ) {
         return false;
      }


      if( adsensei_is_amp_endpoint() ){
         return false;
      }

      if( $this->isExcludedExtraPages() ) {
         return false;
      }

      if( !adsensei_ad_is_allowed() ) {
         return false;
      }

//      if( $this->isExcludedPageId() ) {
//         return false;
//      }


      if( $this->isExcludedPostTypes() ) {
         return false;
      }

      if( $this->isExcludedUserRole() ) {
         return false;
      }




      return true;
   }

   private function isExcludedExtraPages() {
      // Is frontpage
      if( is_front_page() &&
          isset( $this->settings['autoads_extra_pages'] ) &&
          in_array( 'homepage', $this->settings['autoads_extra_pages'] ) ) {
         return true;
      }

      return false;
   }

   /**
    * Check if ad is allowed on specific post_type
    *
    * @global array $adsensei_options
    * @global array $post
    * @return boolean true if post_type is allowed
    */
   private function isExcludedPostTypes() {
      global $post;

      if( !isset( $post ) ) {
         return true;
      }

      if( empty( $this->settings['autoads_post_types'] ) || $this->settings['autoads_post_types'] == 'none' ) {
         return false;
      }

      $current_post_type = get_post_type( $post->ID );
      if( in_array( $current_post_type, $this->settings['autoads_post_types'] ) ) {
         return true;
      }

      return false;
   }

   /**
    * Check if post id is excluded
    * @global array $post
    * @return boolean
    */
//   private function isExcludedPageId() {
//      global $post;
//
//
//      if( !isset( $post->ID ) ) {
//         return true;
//      }
//
//      if( !isset( $this->ads['ads'][$this->id]['excludedPostIds'] ) ||
//              empty( $this->ads['ads'][$this->id]['excludedPostIds'] )
//      ) {
//         return false;
//      }
//
//      if( strpos( $this->ads['ads'][$this->id]['excludedPostIds'], ',' ) !== false ) {
//         $excluded = explode( ',', $this->ads['ads'][$this->id]['excludedPostIds'] );
//         if( in_array( $post->ID, $excluded ) ) {
//            return true;
//         }
//      }
//      if( $post->ID == $this->ads['ads'][$this->id]['excludedPostIds'] ) {
//         return true;
//      }
//
//      // default condition
//      return false;
//   }

   /**
    * Excluded user roles
    * @return boolean
    */
   private function isExcludedUserRole() {

      if( empty( $this->settings['autoads_user_roles'] ) || $this->settings['autoads_user_roles'] == 'none' ) {
         return false;
      }

      if( isset( $this->settings['autoads_user_roles'] ) &&
              count( array_intersect( $this->settings['autoads_user_roles'], wp_get_current_user()->roles ) ) >= 1 ) {
         return true;
      }

      return false;
   }

}
