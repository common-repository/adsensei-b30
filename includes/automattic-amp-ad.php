<?php

/**
 * Automattic AMP Functions
 *
 * @package     ADSENSEI
 * @subpackage  Includes/automattic-amp-ad
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.9
 */

add_action( 'amp_post_template_head', 'adsensei_amp_add_amp_ad_js' );
function adsensei_amp_add_amp_ad_js( $amp_template ) {
   global $adsensei_options;

   if (isset($adsensei_options['disableAmpScript'])){
      return false;
   }
    ?>
    <script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>
    <?php
}
