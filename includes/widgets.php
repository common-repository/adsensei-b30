<?php

/**
 * Widget Functions
 *
 * @package     ADSENSEI
 * @subpackage  Functions/Widgets
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.1
 */

function adsensei_get_inline_widget_ad_style( $id ) {
    global $adsensei_options;

    if( empty($id) ) {
        return '';
    }

    // Basic style
    $styleArray = array(
        'float:left;margin:%1$dpx %1$dpx %1$dpx 0;',
        'float:none;margin:%1$dpx 0 %1$dpx 0;text-align:center;',
        'float:right;margin:%1$dpx 0 %1$dpx %1$dpx;',
        'float:none;margin:%1$dpx;');

    // Alignment
    $adsalign = ( int )$adsensei_options['ads']['ad' . $id . '_widget']['align'];

    // Margin
    $adsmargin = '0';
    $padding = 'padding:';
     if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['margin'] )){
       $adsmargin = $adsensei_options['ads']['ad' . $id . '_widget']['margin'] ;
        $margin = sprintf( $styleArray[$adsalign], $adsmargin );
     }else{
          $margin = 'margin:';
          if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['margintop'] )){
           $margin .=$adsensei_options['ads']['ad' . $id . '_widget']['margintop'] ."px " ;
         }else{
            $margin .= "0px ";
         }
          if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['marginright'] )){
           $margin .=$adsensei_options['ads']['ad' . $id . '_widget']['marginright'] ."px " ;
         }else{
            $margin .= "0px ";
         }
          if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['marginbottom'] )){
          $margin .=$adsensei_options['ads']['ad' . $id . '_widget']['marginbottom'] ."px " ;
         }else{
            $margin .= "0px ";
         }
          if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['marginleft'] )){
          $margin .=$adsensei_options['ads']['ad' . $id . '_widget']['marginleft'] ."px" ;
         }else{
            $margin .= "0px ";
         }
     }
        if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['paddingtop'] )){
           $padding .=$adsensei_options['ads']['ad' . $id . '_widget']['paddingtop'] ."px " ;
         }else{
            $padding .= "0px ";
         }
          if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['paddingright'] )){
           $padding .=$adsensei_options['ads']['ad' . $id . '_widget']['paddingright'] ."px " ;
         }else{
            $padding .= "0px ";
         }
          if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['paddingbottom'] )){
          $padding .=$adsensei_options['ads']['ad' . $id . '_widget']['paddingbottom'] ."px " ;
         }else{
            $padding .= "0px ";
         }
          if(isset( $adsensei_options['ads']['ad' . $id . '_widget']['paddingleft'] )){
          $padding .=$adsensei_options['ads']['ad' . $id . '_widget']['paddingleft'] ."px" ;
         }else{
            $padding .= "0px ";
         }

        $css =$margin.'; '.$padding .'; ';

    // Do not create any inline style on AMP site
    $style =  !adsensei_is_amp_endpoint() ? apply_filters( 'adsensei_filter_widget_margins', $css, 'ad' . $id . '_widget') : '';

    return $style;
}

/**
 * Register Widgets
 *
 * @return void
 * @since 0.9.2
 */

function adsensei_register_widgets() {
    global $adsensei_options;

    $amountWidgets = 10;
    for ( $i = 1; $i <= $amountWidgets; $i++ ) {
        if( !empty( $adsensei_options['ads']['ad' . $i . '_widget']['code']) || !empty( $adsensei_options['ads']['ad' . $i . '_widget']['g_data_ad_slot']) ) {
            register_widget( 'adsensei_widgets_' . $i );
        }
    }
}
add_action( 'widgets_init', 'adsensei_register_widgets', 1 );



class adsensei_widgets_1 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        $this->adsID = '1';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );


        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    /**
     * Create widget
     *
     * @global array $adsensei_options
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        global $adsensei_options, $ad_count_widget;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }

        extract( $args );

        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            //$codetxt = $adsensei_options['ad' . $this->adsID . '_widget'];
            $style = adsensei_get_inline_widget_ad_style($this->adsID);
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget" style="'.$style.'">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget1

class adsensei_widgets_2 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        $this->adsID = '2';

        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }
        extract( $args );

        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        //if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_ad_reach_max_count() ) {
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            //if (array_key_exists('before_widget', $args))
            echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            //if (array_key_exists('after_widget', $args))
            echo $args['after_widget'];
        };
    }

}

// class My_Widget2
class adsensei_widgets_3 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '3';

        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }
        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget3

class adsensei_widgets_4 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '4';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }

        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget4

class adsensei_widgets_5 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '5';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }
        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget5

class adsensei_widgets_6 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '6';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }

        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget6

class adsensei_widgets_7 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '7';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }

        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget7

class adsensei_widgets_8 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '8';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }

        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget8

class adsensei_widgets_9 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '9';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }

        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage() ) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget9

class adsensei_widgets_10 extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {

        $this->adsID = '10';
        $this->AdsWidName = sprintf( 'AdsWidget%d (Quick Adsense Reloaded)', $this->adsID );
        $this->AdsWidID = sanitize_title( str_replace( array('(', ')'), '', $this->AdsWidName ) );
        parent::__construct(
                $this->AdsWidID, // Base ID
                str_replace('Quick Adsense Reloaded', 'WP ADSENSEI', $this->AdsWidName) , // Name
                array(
                    'description' => __( 'Widget contains ad code', 'adsenseib30' ),
                    'classname' => 'adsensei-ad'.$this->adsID.'_widget'
                    ) // Args
        );
    }

    public function widget( $args, $instance ) {
        global $adsensei_options;

        // All widget ads are deactivated via post meta settings
        if( adsensei_check_meta_setting( 'NoAds' ) === '1' ){
            return false;
        }

        extract( $args );
        $cont = adsensei_post_settings_to_quicktags( get_the_content() );
        if( strpos( $cont, "<!--OffAds-->" ) === false && strpos( $cont, "<!--OffWidget-->" ) === false && adsensei_widget_ad_is_allowed() && !adsensei_hide_ad_widget_on_homepage()) {

            //adsensei_set_ad_count_widget();
            $code = adsensei_render_ad( 'ad' . $this->adsID . '_widget', $adsensei_options['ads']['ad' . $this->adsID . '_widget']['code'] );
            echo "\n" . "<!-- Quick Adsense Reloaded -->" . "\n";
            if( array_key_exists( 'before_widget', $args ) )
                echo $args['before_widget'];
            echo '<div id="adsensei-ad' . $this->adsID . '_widget">';
            echo $code;
            echo '</div>';
            if( array_key_exists( 'after_widget', $args ) )
                echo $args['after_widget'];
        };
    }

}

// class My_Widget10
