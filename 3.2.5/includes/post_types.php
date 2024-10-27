<?php

/**
 * Post Types
 *
 * @package     ADSENSEI
 * @subpackage  Functions/post_types
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.8
 */



/**
 * Get list of available post_types
 *
 * @return array list of post_types
 */
function adsensei_get_post_types(){
    $post_types = array('post'=>'post', 'page'=>'page');
    return apply_filters('adsensei_post_types',$post_types);
}

/**
 * Check if ad is allowed on specific post_type
 *
 * @global array $adsensei_options
 * @global array $post
 * @return boolean true if post_type is allowed
 */
function adsensei_post_type_allowed(){
    global $adsensei_options, $post;

    $return = false;

    if (!isset($post)){
        $return = false;
        return apply_filters('adsensei_post_type_allowed',$return);
    }

    if (!isset($adsensei_options['post_types']) || !is_array($adsensei_options['post_types']) || empty($adsensei_options['post_types'])){
        $return = false;
        return apply_filters('adsensei_post_type_allowed',$return);
    }

    $current_post_type = get_post_type($post->ID);
    if ( in_array( $current_post_type, $adsensei_options['post_types'] )){
        $return = true;
    }
    return apply_filters('adsensei_post_type_allowed',$return);
}
