<?php


function migrate_content_settings_page(){
    
    $adsenseib30_migrated = get_option('adsenseib30_migrated');
    if($adsenseib30_migrated == 1){
        //quito option adsenseib30_migrated
        delete_option('adsenseib30_migrated');
        //elimino post type adsensei-ads y sus postmeta, postname like migrado
        $args = array(
            'post_type' => 'adsensei-ads',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'name__like' => 'migrated'
        );
        $ads = get_posts($args);
        foreach ($ads as $ad) {
            wp_delete_post($ad->ID, true);
        }
        
        redigir();
    }else{

        $adsenseib30_settings = get_option('adsenseib30_settings');
        for ($i=1; $i < 11; $i++) { 
            if(isset($adsenseib30_settings['adCode'.$i]) && $adsenseib30_settings['adCode'.$i] != ''){

                $code = $adsenseib30_settings['adCode'.$i];
                $adClient = substr($code, strpos($code, "data-ad-client=") + 16, 21);
                $adSlot = substr($code, strpos($code, "data-ad-slot=") + 14, 10);
                $adWidth = substr($code, strpos($code, "width:") + 6, 3);
                $adHeight = substr($code, strpos($code, "height:") + 7, 3);
                $adWidth = trim($adWidth, 'p');
                $adHeight = trim($adHeight, 'p');
                //parsear el ancho y alto a int 
                $adWidth = intval($adWidth);
                $adHeight = intval($adHeight);


                if($adClient != '' && $adSlot != ''){
                    //Creamos un post con el anuncio
                    $post = array(
                        'post_title'    => 'Migrated ad'.$i,
                        'post_content'  => '',
                        'post_status'   => 'publish',
                        'post_author'   => 1,
                        'post_type'     => 'adsensei-ads',
                    );
                    $post_id = wp_insert_post( $post);
                    
                    //Agregamos los postmeta al post
                    //label
                    add_post_meta($post_id, 'label', 'Migrated ad'.$i);
                    add_post_meta($post_id, 'ad_id', $post_id);
                    add_post_meta($post_id, 'ad_type', 'adsense');
                    add_post_meta($post_id, 'label', 'Migrated ad'.$i);
                    add_post_meta($post_id, 'adsense_ad_type', 'display_ads');

                    add_post_meta($post_id, 'g_data_ad_slot', $adSlot);
                    add_post_meta($post_id, 'g_data_ad_client', $adClient);
                    add_post_meta($post_id, 'adsense_type', 'adsense');
                    if($adWidth != ''){
                        add_post_meta($post_id, 'g_data_ad_width', $adWidth);
                    }else{
                        add_post_meta($post_id, 'g_data_ad_width', '');
                    }
                    if($adHeight != ''){
                        add_post_meta($post_id, 'g_data_ad_height', $adHeight);
                    }else{
                        add_post_meta($post_id, 'g_data_ad_height', '');
                    }

                    //adPosition1
                    $adPosition = $adsenseib30_settings['adPosition'.$i];

                    if($adPosition == '0'){
                        add_post_meta($post_id, 'position', 'beginning_of_post');
                    }else if($adPosition == 'middle'){
                        add_post_meta($post_id, 'position', 'middle_of_post');
                    }else if($adPosition == 'end'){
                        add_post_meta($post_id, 'position', 'end_of_post');
                    }else if($adPosition == '1'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '1');
                    }else if($adPosition == '2'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '2');
                    }else if($adPosition == '3'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '3');
                    }else if($adPosition == '4'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '4');
                    }else if($adPosition == '5'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '5');
                    }else if($adPosition == '6'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '6');
                    }else if($adPosition == '7'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '7');
                    }else if($adPosition == '8'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '8');
                    }else if($adPosition == '9'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '9');
                    }else if($adPosition == '10'){
                        add_post_meta($post_id, 'position', 'after_paragraph');
                        add_post_meta($post_id, 'paragraph_number', '10');
                    }else if($adPosition == 'before end'){
                        add_post_meta($post_id, 'position', 'before_last_paragraph');
                    }else if($adPosition == 'H2 first'){
                        add_post_meta($post_id, 'position', 'after_html_tag');
                        add_post_meta($post_id, 'count_as_per', 'h2');
                        add_post_meta($post_id, 'paragraph_number', '1');
                    }else if($adPosition == 'H2 second'){
                        add_post_meta($post_id, 'position', 'after_html_tag');
                        add_post_meta($post_id, 'count_as_per', 'h2');
                        add_post_meta($post_id, 'paragraph_number', '2');
                    }else if($adPosition == 'H2 third'){
                        add_post_meta($post_id, 'position', 'after_html_tag');
                        add_post_meta($post_id, 'count_as_per', 'h2');
                        add_post_meta($post_id, 'paragraph_number', '3');
                    }else if($adPosition == 'H3 first'){
                        add_post_meta($post_id, 'position', 'after_html_tag');
                        add_post_meta($post_id, 'count_as_per', 'h3');
                        add_post_meta($post_id, 'paragraph_number', '1');
                    }else if($adPosition == 'H3 second'){
                        add_post_meta($post_id, 'position', 'after_html_tag');
                        add_post_meta($post_id, 'count_as_per', 'h3');
                        add_post_meta($post_id, 'paragraph_number', '2');
                    }else if($adPosition == 'H3 third'){
                        add_post_meta($post_id, 'position', 'after_html_tag');
                        add_post_meta($post_id, 'count_as_per', 'h3');
                        add_post_meta($post_id, 'paragraph_number', '3');
                    }


                    //adAlign1
                    $adAlign = $adsenseib30_settings['adAlign'.$i];
                    if($adAlign == 'left'){
                        add_post_meta($post_id, 'align', '0');
                    }else if($adAlign == 'wrapleft'){
                        add_post_meta($post_id, 'align', '0');
                    }else if($adAlign == 'center'){
                        add_post_meta($post_id, 'align', '1');
                    }else if($adAlign == 'right'){
                        add_post_meta($post_id, 'align', '2');
                    }else if($adAlign == 'wrapright'){
                        add_post_meta($post_id, 'align', '2');
                    }

                    //adMargin1
                    $adMargin = $adsenseib30_settings['adMargin'.$i];
                    add_post_meta($post_id, 'margin', $adMargin);

                    //showOn1
                    $showOn = $adsenseib30_settings['showOn'.$i];

                    //adCategory1
                    $adCategory = $adsenseib30_settings['adCategory'.$i];
                    if($adCategory != '-1'){
                        $category = get_category($adCategory);
                        $arrayCategory = array(
                            array(
                                'type' => array(
                                    'label' => 'Post Category',
                                'value' => 'post_category',
                                ),
                                'value' => array(
                                    'label' => $category->name,
                                    'value' => $category->term_id,
                                ),
                                'condition' => 'AND',
                            )
                        );
                    }

                    if($showOn == 'posts'){
                        $array = array(
                            array(
                                'type' => array(
                                    'label' => 'Post Type',
                                    'value' => 'post_type',
                                ),
                                'value' => array(
                                    'label' => 'post',
                                    'value' => 'post',
                                )
                            )
                        );
                        //append category array
                        if($adCategory != '-1'){
                            $array = array_merge($array, $arrayCategory);
                        }
                        add_post_meta($post_id, 'show_on', $array);
                    }else if($showOn == 'pages'){
                        $array = array(
                            array(
                                'type' => array(
                                    'label' => 'Post Type',
                                    'value' => 'post_type',
                                ),
                                'value' => array(
                                    'label' => 'page',
                                    'value' => 'page',
                                )
                            )
                        );
                        if($adCategory != '-1'){
                            $array = array_merge($array, $arrayCategory);
                        }
                        add_post_meta($post_id, 'show_on', $array);
                    }else if($showOn == 'both'){
                        $array = array(
                            array(
                                'type' => array(
                                    'label' => 'Post Type',
                                    'value' => 'post_type',
                                ),
                                'value' => array(
                                    'label' => 'post',
                                    'value' => 'post',
                                ),
                                'condition' => 'AND',
                            ),
                            array(
                                'type' => array(
                                    'label' => 'Post Type',
                                    'value' => 'post_type',
                                ),
                                'value' => array(
                                    'label' => 'page',
                                    'value' => 'page',
                                )
                            )
                        );
                        if($adCategory != '-1'){
                            $array = array_merge($array, $arrayCategory);
                        }
                        add_post_meta($post_id, 'show_on', $array);
                    }

                    //adDevice1
                    $adDevice = $adsenseib30_settings['adDevice'.$i];
                    if($adDevice == 'desktop'){
                        $array = array(
                            array(
                                'type' => array(
                                    'label' => 'Device Type',
                                    'value' => 'device_type',
                                ),
                                'value' => array(
                                    'label' => 'Desktop',
                                    'value' => 'desktop',
                                )
                            )
                        );
                        add_post_meta($post_id, 'device', $array);
                    }else if($adDevice == 'mobile'){
                        $array = array(
                            array(
                                'type' => array(
                                    'label' => 'Device Type',
                                    'value' => 'device_type',
                                ),
                                'value' => array(
                                    'label' => 'Mobile',
                                    'value' => 'mobile',
                                )
                            )
                        );
                        add_post_meta($post_id, 'device', $array);
                    }else if($adDevice == 'desktop and mobile'){
                        $array = array(
                            array(
                                'type' => array(
                                    'label' => 'Device Type',
                                    'value' => 'device_type',
                                ),
                                'value' => array(
                                    'label' => 'Desktop',
                                    'value' => 'desktop',
                                ),
                                'condition' => 'AND',
                            ),
                            array(
                                'type' => array(
                                    'label' => 'Device Type',
                                    'value' => 'device_type',
                                ),
                                'value' => array(
                                    'label' => 'Mobile',
                                    'value' => 'mobile',
                                )
                            )
                        );
                        add_post_meta($post_id, 'device', $array);
                    }
                }
            }    
        }

        //agrego en options que se a migrado de versión
        update_option('adsenseib30_migrated', '1');
        
    }
    redigir();
            

}

function redigir(){
    //Seras redirigido en 2 segundos
    echo '<div class="notice notice-success is-dismissible">
            <p>AdsenseiB30 has been updated successfully.</p>
        </div> 
        <script type="text/javascript">
            setTimeout(function(){
                window.location.href = "'.admin_url('').'"; 
            }, 100);
        </script>';
}