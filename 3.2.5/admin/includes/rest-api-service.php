<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class ADSENSEI_Ad_Setup_Api_Service {

    private $migration_service = null;
    private $amp_front_loop = array();

    public function __construct() {

        if($this->migration_service == null){
          require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/migration-service.php';
          $this->migration_service = new ADSENSEI_Ad_Migration();
        }

    }

    public function getConditionList($condition, $search, $diff = null){

        $choices = array();

        switch($condition){

          case "post_type":

              $post_type = array();
              $args['public'] = true;

              if(!empty($search) && $search != null){
                $args['name'] = $search;
              }
              $choices = get_post_types( $args, 'names', 'and' );
              unset($choices['attachment'], $choices['amp_acf'], $choices['adsensei-ads']);

              if($choices){
                foreach($choices as $key =>$value){
                  $post_type[] = array('label' => $value, 'value' => $key);
                }
              }

              $choices = $post_type;

            break;

          case "page_template" :

            $choices[] = array('label' => 'Default Template', 'value' => 'default');

            $templates = wp_get_theme()->get_page_templates();

            if($templates){

                foreach($templates as $k => $v){

                     $choices[] = array('label' => $v, 'value' => $k);

                }

            }

            break;

          case "post" :
          case "page" :

            if($condition == 'page'){

              $post_types['page'] = 'page';

            }else{

              $post_types = get_post_types();
              unset( $post_types['page'], $post_types['attachment'], $post_types['revision'] , $post_types['nav_menu_item'], $post_types['acf'] , $post_types['amp_acf'],$post_types['saswp']  );

            }

            if( $post_types )
            {
	            foreach( $post_types as $post_type ){

                $arg['post_type']      = $post_type;
                $arg['posts_per_page'] = 10;
                $arg['post_status']    = 'any';

                if(!empty($search)){
                  $arg['s']              = $search;
                }

                $posts = $this->getPostsByArg($arg);

                if(isset($posts['posts_data'])){

                  foreach($posts['posts_data'] as $post){

                    $choices[] = array('value' => $post['post']['post_id'], 'label' => $post['post']['post_title']);

                  }

                }


              }
	            $choices = array_map("unserialize", array_unique(array_map("serialize", $choices)));


            }

            break;

          case "post_category" :

            $args = array(
                        'hide_empty' => false,
                        'number'     => 10,
                      );

            if(!empty($search)){
              $args['name__like'] = $search;
            }

            $terms = get_terms( 'category', $args);

            if( !empty($terms) ) {

              foreach( $terms as $term ) {

                $choices[] = array('value' => $term->term_id, 'label' => $term->name);

              }

            }

            break;

          case "user_type" :
          case "post_format" :
          case "taxonomy" :
          case "general":
              $general_arr = array();
            if($condition == 'post_format'){
              $choices = get_post_format_strings();
            }else if($condition == 'user_type'){
              global $wp_roles;

              $choices = $wp_roles->get_names();

              if( is_multisite() ){

                $choices['super_admin'] = esc_html__('Super Admin','schema-and-structured-data-for-wp');

              }
            }else if($condition == 'taxonomy'){

              $choices    = array('all' => esc_html__('All','schema-and-structured-data-for-wp'));
              $taxonomies = $this->adsensei_post_taxonomy_generator();
              $choices    = array_merge($choices, $taxonomies);

            }else{
                $choices = array(
                  'homepage'      => 'HomePage',
                  'show_globally' => 'Show Globally',
                );
            }

            if(!empty($search) && $search != null){

                $search_user = array();

                foreach($choices as $key => $val){
                  if((strpos($key, $search) !== false) || strpos($key, $val) !== false){
                    $search_user[$key] = $val;
                  }
                }

                $choices = $search_user;
            }

            if($choices){
              foreach($choices as $key =>$value){
                $general_arr[] = array('label' => $value, 'value' => $key);
              }
            }

            $choices = $general_arr;

            break;

          case "tags" :

            $args = array(
              'hide_empty' => false,
              'number'     => 10,
            );

            if(!empty($search)){
              $args['name__like'] = $search;
            }

            $taxonomies = $this->adsensei_post_taxonomy_generator();

            foreach($taxonomies as $key => $val){

              if(strpos($key, 'tag') !== false){

                $terms = get_terms( $key, $args);

                if( !empty($terms) ) {

                  foreach( $terms as $term ) {

                   $choices[] = array('value' => $term->slug, 'label' => $term->name);

                  }

                }

              }

            }

            break;
        }

     return $choices;
    }

    public function adsensei_post_taxonomy_generator(){

        $taxonomies = '';
        $choices    = array();

        $taxonomies = get_taxonomies( array('public' => true), 'objects' );

        if($taxonomies){

          foreach($taxonomies as $taxonomy) {

            $choices[ $taxonomy->name ] = $taxonomy->labels->name;

          }

        }

          // unset post_format (why is this a public taxonomy?)
          if( isset($choices['post_format']) ) {

            unset( $choices['post_format']) ;

          }

        return $choices;
    }

    public function getAdById($ad_id){

        $response  = array();
        $meta_data = array();

        if($ad_id){

            $response['post']      = get_post($ad_id, ARRAY_A);
            $post_meta             = get_post_meta($ad_id, '', true);

            if($post_meta){

                foreach($post_meta as $key => $meta){
                    if(is_serialized($meta[0])){
                      $meta_data[$key] = unserialize($meta[0]);
                    }else{
                      $meta_data[$key] = $meta[0];
                    }

                }
            }
            if(isset($meta_data['enabled_on_amp'])){
            if( $meta_data['enabled_on_amp'] == 1 ){
              $meta_data['enabled_on_amp'] = true;
            }else{
              $meta_data['enabled_on_amp'] = false;
            }
          }
            $response['post_meta'] = $meta_data;

        }
        return $response;

    }

    public function getAdDataByParam($post_type, $attr = null, $rvcount = null, $paged = null, $offset = null, $search_param=null){

        $response   = array();
        $arg        = array();
        $meta_query = array();
        $posts_data = array();

        $arg['post_type']      = $post_type;
        $arg['posts_per_page'] = -1;
        $arg['post_status']    = array('publish', 'draft');

        if(isset($attr['in'])){
          $arg['post__in']    = $attr['in'];
        }
        if(isset($attr['id'])){
          $arg['attachment_id']    = $attr['id'];
        }
        if(isset($attr['title'])){
          $arg['title']    = $attr['title'];
        }

        if($rvcount){
            $arg['posts_per_page']    = $rvcount;
        }
        if($paged){
            $arg['paged']    = $paged;
        }
        if($offset){
            $arg['offset']    = $offset;
        }
        if($search_param){

            $meta_query_args = array(
                array(
                    'relation' => 'OR',
                    array(
                        'value'   => $search_param,
                        'compare' => '='
                    ),
                    array(
                        'value'   => $search_param,
                        'compare' => 'LIKE'
                    )
                    )
                );
                $arg['meta_query']          = $meta_query_args;
                $arg['paged']               = 1;
        }
        $response = $this->getPostsByArg($arg);
        return $response;
    }

    public function getPostsByArg($arg){

      $response = array();
      if(count($this->amp_front_loop)==0){
        $query_data =  get_posts($arg);
        $post_meta = array();
        $posts_data = array();
        foreach ($query_data as $key => $value) {
          $data = array();
          $data['post_id']       =  $value->ID;
          $data['post_title']    =  $value->post_title;
          $data['post_status']   =  $value->post_status;
          $data['post_modified'] =  $value->post_modified;
          $post_meta             = get_post_meta($data['post_id'], '', true);
          if($post_meta){
            foreach($post_meta as $key => $val ){
                $post_meta[$key] = $val[0];
            }
          }
          $posts_data[] = array(
                  'post'        => (array) $data,
                  'post_meta'   => $post_meta
                  );
        }
        $response['posts_data']  = $posts_data;
        $response['posts_found'] = $this->getTotalAds();

        $this->amp_front_loop = $response;
      }else{
        $response = $this->amp_front_loop;
      }

        return $response;

    }

    public function updateSettings($parameters){

        $response = false;
        $license_info =array();
        if($parameters){
          $adsensei_options = get_option('adsensei_settings');

          foreach($parameters as $key => $val){

             if($key == 'QckTags'){
              $adsensei_options['quicktags'] = array($key => $val);
             }else if($key == 'adsTxtText' ){
              if($parameters['adsTxtEnabled']){
                // Check if WP_Filesystem is loaded
                if ( ! function_exists( 'WP_Filesystem' ) ) {
                  require_once ABSPATH . 'wp-admin/includes/file.php';
                }

                // Initialize the WordPress filesystem
                $filesystem = WP_Filesystem();

                // Check if the filesystem is initialized successfully
                if ( $filesystem ) {
                  // Generate the content you want to write
                  $content = $val;

                  // Specify the file path where you want to write the content
                  $file_path = ABSPATH . 'ads.txt';

                  // Use the WordPress filesystem method to write the content to the file
                  if ( $filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE ) ) {
                      // show notice that ads.txt has been created
                      set_transient( 'adsensei_vi_ads_txt_notice', true, 300 );
                  } else {
                      set_transient( 'adsensei_vi_ads_txt_error', true, 300 );
                  }
                } else {
                  // WP_Filesystem couldn't be initialized, handle the error accordingly
                  // For example:
                  echo 'WP_Filesystem initialization failed.';
                }
              }
            } else{
              if($key == 'ad_blocker_support' && $val){
                $upload_dir = wp_upload_dir();
                $content_url = $upload_dir['basedir'].'/wpadsensei/tinymce_shortcode.js';
                  wp_mkdir_p($upload_dir['basedir'].'/wpadsensei', 755, true);
                  $sourc = ADSENSEI_PLUGIN_URL . 'assets/js/tinymce_shortcode_uploads.js';
                  if (!file_exists($content_url)) {
                    copy($sourc,$content_url);
                  }
                  $sourc = ADSENSEI_PLUGIN_URL . 'admin/assets/js/src/images/wpadsensei_classic_icon.png';
                  $content_url = $upload_dir['basedir'].'/wpadsensei/wpadsensei_classic_icon.png';
                  if (!file_exists($content_url)) {
                    copy($sourc,$content_url);
                  }
              }

              $adsensei_options[$key] = $val;

            }

          }
         $response =  update_option( 'adsensei_settings', $adsensei_options );

        }
if($license_info){
  $response =$license_info;
}


        return $response;
    }

    public function validateAdsTxt($content){

        $sanitized = array();
        $errors    = array();

        if($content){

          $lines     = preg_split( '/\r\n|\r|\n/', $content );

            foreach ( $lines as $i => $line ) {
              $line_number = $i + 1;
              $result      = adsensei_validate_ads_txt_line( $line, $line_number );

              $sanitized[] = $result['sanitized'];
              if ( ! empty( $result['errors'] ) ) {
                $errors = array_merge( $errors, $result['errors'] );
              }
            }
            $sanitized = implode( PHP_EOL, $sanitized );
        }
        return array('errors' => $errors, 'sanitized_content' => $sanitized);

    }
    public function updateAdData($parameters, $mode = null){

            $post_meta      = $parameters['adsensei_post_meta'];
            $ad_id          = isset($post_meta['ad_id']) ? $post_meta['ad_id'] : '';
            $post_status    = 'publish';

            if(isset($parameters['adsensei_ad_status'])){
              $post_status    = $parameters['adsensei_ad_status'];
            }

            if($mode){

              $post_status    = 'draft';

              if($post_meta['code']  || $post_meta['g_data_ad_slot']){
                $post_status    = 'publish';
              }

            }

            $arg = array(
                'post_title'   => wp_strip_all_tags( $post_meta['label']),
                'post_status'  => sanitize_text_field($post_status),
                'post_type'    => 'adsensei-ads',
            );

            if($ad_id && !is_null(get_post($ad_id))){

                $arg['ID'] = $ad_id;

                @wp_update_post( $arg );

            }else{
                  $ad_id =   wp_insert_post( $arg );
            }
            if($post_meta){

                $post_meta['ad_id'] = $ad_id;

                foreach($post_meta as $key => $val){


                    $filterd_meta = sanitize_post_meta($key, $val);
                    if($key == 'ad_blindness'){
                      $filterd_meta =$val;
                    }
                    if($key == 'ab_testing'){
                      $filterd_meta =$val;
                    }
                    if($key == 'popup_ads'){
                      $filterd_meta =$val;
                    }
                    if($key == 'video_ads'){
                      $filterd_meta =$val;
                    }
                    if($key == 'floating_slides'){
                      $filterd_meta =$val;
                    }

                    update_post_meta($ad_id, $key, $filterd_meta);
                }
            }

            //Saving post data to adsensei settings
            if( $mode == null ){

              $this->migration_service->adsenseiUpdateOldAd($ad_id, $post_meta);

            }

            return  $ad_id;
    }

    public function changeAdStatus($ad_id, $action){

      $response = wp_update_post(array(
                    'ID'            =>  $ad_id,
                    'post_status'   =>  $action
                  ));

      return $response;

    }
    public function duplicateAd($ad_id){

      $response = null;

      global $wpdb;
      $post = get_post( $ad_id);

      if ( isset( $post ) && $post != null ) {
         // args for new post
          $args = array(
            'post_title'       => $post->post_title,
            'post_status'      => $post->post_status,
            'post_type'        => $post->post_type,
            );

          $new_post_id = wp_insert_post( $args );

          $post_metas = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d",
                $ad_id
            ));

          if ( count( $post_metas )!=0 ) {

            $sql_query = $wpdb->prepare( "INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value ) ");

            foreach ( $post_metas as $post_meta ) {

             $meta_key = esc_sql($post_meta->meta_key);

             if( $meta_key == '_wp_old_slug' ) continue;

                if($meta_key == 'ad_id'){
                  $meta_value = esc_sql( $new_post_id);
                }else{
                  $meta_value = esc_sql( $post_meta->meta_value);
                }
                $sql_query_sel[] = $wpdb->prepare(
                  "SELECT %d, %s, %s",
                  $new_post_id,
                  $meta_key,
                  $meta_value
              );
              
             }

             $sql_query.= implode(" UNION ALL ", $sql_query_sel);
             $wpdb->query( $wpdb->prepare("%s", $sql_query ) );
             $post_meta= $this->getAdById($new_post_id);
             $this->migration_service->adsenseiUpdateOldAd($new_post_id, $post_meta['post_meta'],'update_old');
            }
            $response = $new_post_id;
      }

      return $response;
    }

	public function deleteAd($ad_id){
//  current_user_can already checked in class ADSENSEI_Ad_Setup_Api
		$adsensei_settings = get_option('adsensei_settings');

		$old_ad_id      = get_post_meta($ad_id, 'adsensei_ad_old_id', true);

		unset($adsensei_settings['ads'][$old_ad_id]);
		update_option('adsensei_settings', $adsensei_settings);
		$response = wp_delete_post($ad_id, true);
		return $response;

	}

    public function getPlugins($search){

      $response = array();
      $response[] = array('value' => 'woocommerce', 'label' => 'woocommerce');
      $response[] = array('value' => 'buddypress', 'label' => 'buddypress');
      return $response;

    }

    private function getTotalAds()
    {
      global $wpdb;
      $total_result = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(ID) as total_posts FROM $wpdb->posts Where post_type='adsensei-ads' AND (post_status='publish' OR post_status='draft')"),0,0);
       if($total_result)
       {
        return (int) $total_result;
       }
       return 0;
    }

}
