<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class ADSENSEI_Ad_Migration {
    private static $instance;

    public static function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

	/**
	 * @param $ad_id
	 * @param $post_meta
	 *
	 * @return mixed
	 */
	public function adsenseiUpdateOldAd($ad_id, $post_meta,$arg=''){
        global $adsensei_options;
            $new_data = array();

            $new_data = $post_meta;

            $old_ad_id      = get_post_meta($ad_id, 'adsensei_ad_old_id', true);

            $adsensei_settings = get_option( 'adsensei_settings' );

            if($old_ad_id && $arg != 'update_old'){
                $adsensei_settings['ads'][$old_ad_id] = $new_data;
            }else{

            $old_ads = $adsensei_settings;
            $ad_count = 1;
            if(isset($adsensei_settings['ads']) && !empty($adsensei_settings['ads'])){
                end($adsensei_settings['ads']);
                $key = key($adsensei_settings['ads']);
                if(!empty($key)){
                    $key_array =   explode("ad",$key);
                    if(is_array($key_array)){
                        $ad_count = (isset($key_array[1]) && !empty($key_array[1]))?($key_array[1]+1):1;
                    }
                }
            }
            $new_data['adsensei_ad_old_id'] ='ad'.$ad_count;
            $old_ads['ads']['ad'.$ad_count] = $new_data;
            $adsensei_settings= $old_ads;
            update_post_meta($ad_id, 'adsensei_ad_old_id', 'ad'.$ad_count);

            }
            update_option('adsensei_settings', $adsensei_settings);

            return $ad_id;
     }
    public function adsenseiAdReset(){
        global $adsensei_options;
        $adsenseiAdReset = get_option( 'adsenseiAdReset' );
	    $adsensei_mode = get_option('adsensei-mode');
        if(!$adsenseiAdReset && $adsensei_mode == 'new'){
            require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
            $api_service = new ADSENSEI_Ad_Setup_Api_Service();
            $adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');
            $duplicate_array =array();

            if(isset($adsensei_ads['posts_data']) && isset($adsensei_options['ads'])) {
                foreach ($adsensei_options['ads'] as $key1 => $value1) {
                    foreach ($adsensei_ads['posts_data'] as $key => $value) {
                        $ads = $value['post_meta'];
                        if($key1 == $ads['adsensei_ad_old_id'] && $value1['ad_id'] != $ads['ad_id']){
                            if(isset($ads['random_ads_list']))
                                $ads['random_ads_list'] = unserialize($ads['random_ads_list']);
                            if(isset($ads['visibility_include']))
                                $ads['visibility_include'] = unserialize($ads['visibility_include']);
                            if(isset($ads['visibility_exclude']))
                                $ads['visibility_exclude'] = unserialize($ads['visibility_exclude']);

                            if(isset($ads['targeting_include']))
                                $ads['targeting_include'] = unserialize($ads['targeting_include']);

                            if(isset($ads['targeting_exclude']))
                                $ads['targeting_exclude'] = unserialize($ads['targeting_exclude']);

                            $duplicate_array[] =$ads;
                        }
                    }
                }
            }
            if(!empty($duplicate_array)){
                $ad_count = 1;
                if(isset($adsensei_options['ads']) && !empty($adsensei_options['ads'])){
                    end($adsensei_options['ads']);
                    $key = key($adsensei_options['ads']);
                    if(!empty($key)){
                        $key_array =   explode("ad",$key);
                        if(is_array($key_array)){
                            $ad_count = (isset($key_array[1]) && !empty($key_array[1]))?$key_array[1]+1:1;
                        }
                    }
                }

                foreach ($duplicate_array as $duplicate){
                    $old_ads = $adsensei_options['ads'];
                    $duplicate['adsensei_ad_old_id'] = 'ad'.$ad_count;
                    $old_ads['ad'.$ad_count] = $duplicate;
                    update_post_meta($duplicate['ad_id'], 'adsensei_ad_old_id', 'ad'.$ad_count);
                    $adsensei_options['ads'] = $old_ads;
                    $ad_count++;
                }
                update_option('adsensei_settings_backup_reset', $adsensei_options);
                update_option('adsensei_settings', $adsensei_options);
            }
          update_option('adsenseiAdReset', true);
        }

    }


	public function adsenseiAdResetDeleted(){
		global $adsensei_options;
		$adsenseiAdResetDeleted = get_option( 'adsenseiAdResetDeleted' );
		$adsensei_mode = get_option('adsensei-mode');
		if(!$adsenseiAdResetDeleted && $adsensei_mode == 'new'){
			$adsensei_settings = get_option('adsensei_settings');
			update_option('adsenseiAdResetDeleted', $adsensei_settings);
			require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
			$api_service = new ADSENSEI_Ad_Setup_Api_Service();
			$adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');
			$check_array =array();
			if(isset($adsensei_ads['posts_data'])) {
				foreach ($adsensei_ads['posts_data'] as $key => $value) {
					$ads = $value['post_meta'];
					if ( ! in_array( $ads['adsensei_ad_old_id'], $check_array ) ) {
						$check_array[] = $ads['adsensei_ad_old_id'];
					}
				}
			}
            if(isset($adsensei_settings['ads'])){
			foreach ( $adsensei_settings['ads'] as $key => $value ) {
				if( ! in_array( $key, $check_array )){
					unset($adsensei_settings['ads'][$key]);
				}
			}
        }
			$adsensei_options =$adsensei_settings;
			update_option('adsensei_settings', $adsensei_settings);
		}
	}
    public function adsenseiAdReset_optionsDeleted(){
		global $adsensei_options;
        $adsensei_settings = get_option('adsensei_settings');
		$adsenseiAdResetDeleted = get_option( 'adsenseiAdReset_optionsDeleted' );
		$adsensei_mode = get_option('adsensei-mode');
		if(!$adsenseiAdResetDeleted && $adsensei_mode == 'new'){
			$adsensei_settings = get_option('adsensei_settings');

			update_option('adsenseiAdReset_optionsDeleted', $adsensei_settings);
			require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api-service.php';
			$api_service = new ADSENSEI_Ad_Setup_Api_Service();
			$adsensei_ads = $api_service->getAdDataByParam('adsensei-ads');
			$get_unique_value = array();
			if(isset($adsensei_ads['posts_data'])) {
                $get_unique_value = array_unique(array_map(function ($i) { return $i['post_meta']['adsensei_ad_old_id']; },$adsensei_ads['posts_data']));
                if( isset($adsensei_settings['ads']) ){
			foreach ( $adsensei_settings['ads'] as $key => $value ) {
				if( ! in_array( $key, $get_unique_value )){
					unset($adsensei_settings['ads'][$key]);
				}
			}
        }
            if(isset($adsensei_ads['posts_data']) && isset($adsensei_options['ads'])) {
                foreach ($adsensei_options['ads'] as $key1 => $value1) {
                    foreach ($adsensei_ads['posts_data'] as $key => $value) {
                        $ads = $value['post_meta'];
                        if($key1 == $ads['adsensei_ad_old_id'] && $value1['ad_id'] != $ads['ad_id']){
                            if(isset($ads['random_ads_list']))
                                $ads['random_ads_list'] = unserialize($ads['random_ads_list']);
                            if(isset($ads['visibility_include']))
                                $ads['visibility_include'] = unserialize($ads['visibility_include']);
                            if(isset($ads['visibility_exclude']))
                                $ads['visibility_exclude'] = unserialize($ads['visibility_exclude']);
                            if(isset($ads['targeting_include']))
                                $ads['targeting_include'] = unserialize($ads['targeting_include']);

                            if(isset($ads['targeting_exclude']))
                                $ads['targeting_exclude'] = unserialize($ads['targeting_exclude']);
                                $duplicate_array[] =$ads;
                        }
                    }
                }
            }
            if(!empty($duplicate_array)){
                $ad_count = 1;
                if(isset($adsensei_options['ads']) && !empty($adsensei_options['ads'])){
                    end($adsensei_options['ads']);
                    $key = key($adsensei_options['ads']);
                    if(!empty($key)){
                        $key_array =   explode("ad",$key);
                        if(is_array($key_array)){
                            $ad_count = (isset($key_array[1]) && !empty($key_array[1]))?$key_array[1]+1:1;
                        }
                    }
                }

                foreach ($duplicate_array as $duplicate){
                    $old_ads = $adsensei_options['ads'];
                    $duplicate['adsensei_ad_old_id'] = 'ad'.$ad_count;
                    $old_ads['ad'.$ad_count] = $duplicate;
                    update_post_meta($duplicate['ad_id'], 'adsensei_ad_old_id', 'ad'.$ad_count);
                    $adsensei_options['ads'] = $old_ads;
                    $ad_count++;
                }
                update_option('adsensei_settings_backup_reset', $adsensei_options);
                update_option('adsensei_settings', $adsensei_options);
            }
		}
	}
}
}

if(class_exists('ADSENSEI_Ad_Migration')){
    $adsenseiAdMigration = ADSENSEI_Ad_Migration::getInstance();
    $adsenseiAdMigration->adsenseiAdReset();
	$adsenseiAdMigration->adsenseiAdResetDeleted();
    $adsenseiAdMigration->adsenseiAdReset_optionsDeleted();

}
