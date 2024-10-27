<?php

/**
 * Return ad locations HTML based on new API in general settings
 *
 * @param $html
 * @return string   Locations HTML
 */
function adsensei_render_ad_locations( $html ) {
    global $adsensei_options, $_adsensei_registered_ad_locations, $adsensei;

    if( isset( $_adsensei_registered_ad_locations ) && is_array( $_adsensei_registered_ad_locations ) ) {
        foreach ( $_adsensei_registered_ad_locations as $location => $location_args ) {

            $location_settings = adsensei_get_ad_location_settings( $location );

            $html .= $adsensei->html->checkbox( array(
                'name' => 'adsensei_settings[location_settings][' . $location . '][status]',
                'current' => !empty( $location_settings['status'] ) ? $location_settings['status'] : null,
                'class' => 'adsensei-checkbox adsensei-assign'
                    ) );
            $html .= ' ' . __( 'Assign', 'adsenseib30' ) . ' ';

            $html .= $adsensei->html->select( array(
                'options' => adsensei_get_ads(),
                'name' => 'adsensei_settings[location_settings][' . $location . '][ad]',
                'selected' => !empty( $location_settings['ad'] ) ? $location_settings['ad'] : null,
                'show_option_all' => false,
                'show_option_none' => false
                    ) );
            $html .= ' ' . $location_args['description'] . '</br>';
        }
    }

    return $html;
}

/**
 * This hook should be removed and the hook function should replace entire "adsensei_ad_position_callback" function.
 */
add_filter( 'adsensei_ad_position_callback', 'adsensei_render_ad_locations' );


/**
 * Register an ad position.
 *
 * @param array $args   Location settings
 */
function adsensei_register_ad( $args ) {
    global $_adsensei_registered_ad_locations;
    $defaults = array(
        'location'      => '',
        'description'   => ''
    );
    $args = wp_parse_args( $args, $defaults );
    if ( empty( $args['location'] ) ) {
        return;
    }
    if ( ! isset( $_adsensei_registered_ad_locations  ) ) {
        $_adsensei_registered_ad_locations  = array();
    }

    $_adsensei_registered_ad_locations [ $args['location'] ] = $args;
}
/**
 * Whether a registered ad location has an ad assigned to it.
 *
 * @param string $location      Location id
 * @return bool
 */
function adsensei_has_ad( $location ) {
    global $adsensei_options;
    $result = false;

    $location_settings = adsensei_get_ad_location_settings( $location );

    if ( $location_settings ) {
      $result = true;
    }

    if ( ! adsensei_ad_is_allowed() || adsensei_ad_reach_max_count() ) {
        $result = false;
    }

    /**
     * Filter whether an ad is assigned to the specified location.
     */
    return apply_filters( 'adsensei_has_ad', $result, $location );
}
/**
 * Display a custom ad
 *
 * @param array $args       Displaying options
 * @return string|void      Ad code or none if echo set to true
 */
function adsensei_ad( $args ) {
	global $post;

	$defaults = array(
		'location'  => '',
		'echo'      => true,
	);
	$args = wp_parse_args( $args, $defaults );
	$code = '';
	// All ads are deactivated via post meta settings
	if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
		return false;
	}
	global $adsensei_options,$adsensei_mode;
    $wp_adsensei_custom_ad_id = array();
    $loc = 'api-'.$args['location'];
    $loc_index = $loc;
    foreach ($adsensei_options['ads'] as $key => $value) {
         if(isset($value['position']) && strpos($value['position'],$loc_index) > -1){
            $wp_adsensei_custom_ad_id[$loc_index] = $key ;
         }
    }
	if ( adsensei_has_ad( $args['location'] ) ) {

		adsensei_set_ad_count_custom(); // increase amount of Custom ads

		// $location_settings = adsensei_get_ad_location_settings( $args['location'] );
         $location_settings['ad']='';
        if(isset($wp_adsensei_custom_ad_id["api-".$args['location'].""]))
        {
            $location_settings['ad'] = $wp_adsensei_custom_ad_id["api-".$args['location'].""];
            if($location_settings['ad'])
            {
                $modify = str_replace("ad","",$location_settings['ad']);
                $location_settings['ad'] = $modify;
                $code .= "\n".'<!-- WP ADSENSEI Custom Ad v. ' . ADSENSEI_VERSION .' -->'."\n";
                $code .= '<div class="adsensei-location adsensei-ad' .esc_html($location_settings['ad']). '" id="adsensei-ad' .esc_html($location_settings['ad']). '" style="'.  adsensei_get_inline_ad_style( $location_settings['ad'] ).'">'."\n";
                $code .= adsensei_render_ad( 'ad' . $location_settings['ad'], $adsensei_options['ads'][ 'ad' . $location_settings['ad'] ]['code'] );
                $code .= '</div>';
            }
        }

	}elseif ($adsensei_mode == 'new'){

		require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
		$api_service = new ADSENSEI_Ad_Setup_Api_Service();
		$adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');
		// Default Ads
		$adsArrayCus = array();
		if(isset($adsensei_ads['posts_data'])) {
			$i = 1;
			foreach ( $adsensei_ads['posts_data'] as $key => $value ) {
				$ads = $value['post_meta'];
				if ( $value['post']['post_status'] == 'draft' ) {
					continue;
				}
				if ( isset( $ads['random_ads_list'] ) ) {
					$ads['random_ads_list'] = unserialize( $ads['random_ads_list'] );
				}
				if ( isset( $ads['visibility_include'] ) ) {
					$ads['visibility_include'] = unserialize( $ads['visibility_include'] );
				}
				if ( isset( $ads['visibility_exclude'] ) ) {
					$ads['visibility_exclude'] = unserialize( $ads['visibility_exclude'] );
				}

				if ( isset( $ads['targeting_include'] ) ) {
					$ads['targeting_include'] = unserialize( $ads['targeting_include'] );
				}

				if ( isset( $ads['targeting_exclude'] ) ) {
					$ads['targeting_exclude'] = unserialize( $ads['targeting_exclude'] );
				}
				$is_on             = adsensei_is_visibility_on( $ads );
				$is_visitor_on     = adsensei_is_visitor_on( $ads );
				$is_click_fraud_on = adsensei_click_fraud_on();
				if ( isset( $ads['ad_id'] ) ) {
					$post_status = get_post_status( $ads['ad_id'] );
				} else {
					$post_status = 'publish';
				}
				if ( $is_on && $is_visitor_on && $is_click_fraud_on && $post_status == 'publish' ) {
					$api_pos =array();
					$api_pos = explode('-',$ads['position']);
					$ampsupport='';
					if(isset($api_pos[1]) && $api_pos[0]='api' && $api_pos[1]==$args['location']){
						$style = adsensei_get_inline_ad_style_new($ads['ad_id']);
                        if(function_exists('adsensei_hide_markup') && adsensei_hide_markup()  ) {
                            $adscode =
                                "\n". '<div style="'.$style.'">'."\n".
                                adsensei_render_ad($ads['ad_id'], $ads['code'],'',$ampsupport)."\n".
                                '</div>'. "\n";
                        }else{
                            $adscode =
                                "\n".'<!-- WP ADSENSEI Content Ad Plugin v. ' . ADSENSEI_VERSION .' -->'."\n".
                                '<div class="adsensei-location adsensei-ad' .esc_html($ads['ad_id']). '" id="adsensei-ad' .esc_html($ads['ad_id']). '" style="'.esc_html($style).'">'."\n".
                                adsensei_render_ad($ads['ad_id'], $ads['code'],'',$ampsupport)."\n".
                                '</div>'. "\n";
                        }


						$code =$adscode;
						break;

					}
				}

			}
		}

	}
	if ( $args['echo'] ) {
		echo $code;
	} else {
		return $code;
	}
}
/**
 * Return location settings.
 *
 * @param string $location      Location id
 * @return array
 */
function adsensei_get_ad_location_settings( $location ) {
    global $_adsensei_registered_ad_locations, $adsensei_options;

    $result = array(
        'status'    => false,
        'ad'        => '',
    );

    $location_registered     = isset( $_adsensei_registered_ad_locations ) && isset( $_adsensei_registered_ad_locations[ $location ] );
    $location_settings_exist = isset( $adsensei_options['location_settings'] ) && isset( $adsensei_options['location_settings'][ $location ] );

    if ( $location_registered && $location_settings_exist ) {
        $result = wp_parse_args( $adsensei_options['location_settings'][ $location ], $result );
    }

    return $result;
}
