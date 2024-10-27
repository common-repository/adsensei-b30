<?php

/**
 * shortcode functions
 *
 * @package     ADSENSEI
 * @subpackage  Functions/shortcodes
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.4
 */


// add short codes
//@deprecated since 0.9.5
add_shortcode( 'adsensei_ad', 'adsensei_shortcode_display_ad', 1); // Important use a very early priority to be able to count total ads accurate
// new shortcode since 0.9.5
add_shortcode( 'adsensei', 'adsensei_shortcode_display_ad', 1); // Important use a very early priority to be able to count total ads accurate


/**
 * shortcode to include ads in frontend
 *
 * @since 0.9.4
 * @param array $atts
 */
function adsensei_shortcode_display_ad( $atts ) {
    global $adsensei_options;

    // Display Condition is false and ignoreShortcodeCond is empty or not true
    if( !adsensei_ad_is_allowed() && !isset($adsensei_options['ignoreShortcodeCond']) )
        return;


    //return adsensei_check_meta_setting('NoAds');
    if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
        return;
    }

    // The ad id
    $id = isset( $atts['id'] ) ? ( int ) $atts['id'] : 0;
    $ad_id = isset($adsensei_options['ads']['ad'.$id.'']) && $adsensei_options['ads']['ad'.$id.'']!==NULL ? (isset($adsensei_options['ads']['ad'.$id.'']['ad_id'])?$adsensei_options['ads']['ad'.$id.'']['ad_id']:NULL ): NULL ;

    $arr = array(
        'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
        'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
        'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
        'float:none;margin:%1$dpx;');

    $adsalign = isset($adsensei_options['ads']['ad' . $id]['align']) ? $adsensei_options['ads']['ad' . $id]['align'] : 3; // default
    $adsmargin = isset( $adsensei_options['ads']['ad' . $id]['margin'] ) ? $adsensei_options['ads']['ad' . $id]['margin'] : '3'; // default
    $margin = sprintf( $arr[( int ) $adsalign], $adsmargin );

            $ad_checker = '';
            // Removing duplicate db calls by saving function output in variable passing
            $adsensei_ad_var = adsensei_get_ad( $id );
            $ad_checker = $adsensei_ad_var ? $adsensei_ad_var : '' ;
            if ( isset($ad_checker) ) {
                if ( strpos( $ad_checker, 'adsensei-rotatorad')!==false) {
                    $margin = 'text-align: center';
                }
            }
            else{
                $margin = sprintf( $arr[( int ) $adsalign], $adsmargin );
            }

    // Do not create any inline style on AMP site
    $style = !adsensei_is_amp_endpoint() ? apply_filters( 'adsensei_filter_margins', $margin, 'ad' . $id ) : '';
    if(function_exists('adsensei_hide_markup') && adsensei_hide_markup()) {
        $code = "\n" . '<div style="' . $style . '">' . "\n";
        $code .= do_shortcode( $adsensei_ad_var );
        $code .= '</div>' . "\n";
    }else{
        $idof_ad_id = '';
        $idof_ad_id = $ad_id;
        $code = "\n" . '<!-- WP ADSENSEI v. ' . ADSENSEI_VERSION . '  Shortcode Ad -->' . "\n" .
            '<div class="adsensei-location adsensei-ad' . $idof_ad_id . '" id="adsensei-ad' . $idof_ad_id . '" style="' . $style . '">' . "\n";
        $code .= do_shortcode( $adsensei_ad_var );
        $code .= '</div>' . "\n";
    }

    return $code;
}

/**
 * return ad content
 *
 * @since 0.9.4
 * @param int $id id of the ad
 * @return string
 */
function adsensei_get_ad($id = 0) {
    global $adsensei_options,$adsensei_mode;

    if ( adsensei_ad_reach_max_count() ){
        return;
    }

    if ( isset($adsensei_options['ads']['ad' . $id]['code']) ){

        if($adsensei_mode == 'new'){
            $content_post = get_post($adsensei_options['ads']['ad' . $id]['ad_id']);
            if( isset($content_post->post_status) && $content_post->post_status == 'draft'){

                return '';
            }
        }
        $ads =$adsensei_options['ads']['ad' . $id];

//        $is_on         = adsensei_is_visibility_on($ads);
        $is_visitor_on = adsensei_is_visitor_on($ads);
        if($adsensei_mode == 'new' ) {
            if($is_visitor_on ) {
            if($ads['ad_type'] == 'random_ads') {
                if ( function_exists( 'adsensei_parse_random_ads' ) ) {
                    $html  ='<!--CusRnd'.$ads['ad_id'].'-->';
                    return adsensei_parse_random_ads_new($html);
                }else{
                    return '';
                }
            }else if($ads['ad_type'] == 'rotator_ads') {
                if ( function_exists( 'adsensei_parse_rotator_ads' ) ) {
                    $html  ='<!--CusRot'.$ads['ad_id'].'-->';
                    return adsensei_parse_rotator_ads($html);
                }else{
                    return '';
                }
            }else if($ads['ad_type'] == 'group_insertion') {
                if ( function_exists( 'adsensei_parse_group_insert_ads' ) ) {
                    $html  ='<!--CusGI'.$ads['ad_id'].'-->';
                    return adsensei_parse_group_insert_ads($html);
                }else{
                    return '';
                }
            }else{
                // Count how often the shortcode is used - Important
                adsensei_set_ad_count_shortcode();
                //$code = "\n".'<!-- WP ADSENSEI Shortcode Ad v. ' . ADSENSEI_VERSION .' -->'."\n";
                //return $code . $adsensei_options['ad' . $id]['code'];
                return adsensei_render_ad('ad' . $id, $adsensei_options['ads']['ad' . $id]['code']);
            }

            }else{

                return '';
            }
        }else{

            // Count how often the shortcode is used - Important
            adsensei_set_ad_count_shortcode();
            //$code = "\n".'<!-- WP ADSENSEI Shortcode Ad v. ' . ADSENSEI_VERSION .' -->'."\n";
            //return $code . $adsensei_options['ad' . $id]['code'];
            return adsensei_render_ad('ad' . $id, $adsensei_options['ads']['ad' . $id]['code']);
        }
    }
}



/**
 * Return value of adsensei meta box settings
 *
 * @param type $id id of meta settings
 * @return mixed string | bool value if setting is active. False if there is no setting
 */
function adsensei_check_meta_setting($key){
    global $post;

    if ( !isset($post->ID ) ){
        return false;
    }

    $meta_key = '_adsensei_config_visibility';

    $value_arr = get_post_meta ( $post->ID, $meta_key, true );
    $value_key = isset($value_arr[$key]) ? $value_arr[$key] : null;

    if (!empty($value_key))
    return (string)$value_key;

    return false;
}

/*
 * Return string through shortcode function and strip out specific shortcode from it to
 * prevents infinte loops if shortcode contains same shortcode
 *
 * @since 1.3.6
 * @param1 string shortcode e.g. adsensei
 * @param1 string content to return via shortcode
 * @return string / shortcodes parsed
 */

function adsenseiCleanShortcode( $code, $content ) {
    global $shortcode_tags;
    $stack = $shortcode_tags;
    $shortcode_tags = array($code => 1);
    $content = strip_shortcodes( $content );
    $shortcode_tags = $stack;

    return do_shortcode( $content );
}
