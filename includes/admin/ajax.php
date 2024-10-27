<?php
add_action('wp_ajax_adsensei_get_tags', 'adsensei_ajax_get_tags');

/**
 * Get tags by ajax search
 */
function adsensei_ajax_get_tags() {
    
    if (empty($_POST['data']))
        wp_die(0);
    
    $q = $_POST['data']['q'];
    
    $tags = $tags = get_tags(array(
        'hide_empty' => false,
        'name__like' => $q
      ));    
    $new_tags = array();

    foreach ($tags as $key => $value) {
        $new_tags[$key][$value->slug] = $value->name;
    }
    
    $new_tags = adsensei_flatten($new_tags);

    echo json_encode(array('q' => $q, 'results' => $new_tags));
    wp_die();
}

add_action( 'wp_ajax_adsensei_delete_new_design_ad', 'adsensei_delete_new_design_ad');


function adsensei_delete_new_design_ad(){

            check_ajax_referer( 'adsensei_ajax_nonce', 'nonce' );
        
            if( ! current_user_can( 'manage_options' ) )
                return;
            $response = null;
            $ad_number = intval($_POST['ad_number']);    

            if($ad_number && function_exists('adsenseiGetPostIdByMetaKeyValue')){

                $post_id = adsenseiGetPostIdByMetaKeyValue('adsensei_ad_old_id', 'ad'.$ad_number);				
                $response = wp_delete_post($post_id, true);

            }
				
            if($response){
                echo json_encode(array('message' => 'Deleted Successfully'));
            }else{
                echo json_encode(array('message' => 'Something went wrong'));
            }

            wp_die();            
}