<?php

/**
 * User Roles
 *
 * @package     ADSENSEI
 * @subpackage  Functions/user_roles
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.8
 */

/**
 * Check if ad is hidden from current user role
 *
 * @global array $adsensei_options
 * @return boolean true if the current user role is allowed to see ads
 */
function adsensei_user_roles_permission(){
    global $adsensei_options;

    // No restriction. Show ads to all user_roles including public visitors without user role
    if (!isset($adsensei_options['user_roles'])){
        return true;
    }
    $roles = wp_get_current_user()->roles;
    if ( isset ($adsensei_options['user_roles']) && count(array_intersect( $adsensei_options['user_roles'], $roles )) >= 1){
        return false;
    }

    return true;
}
