<?php
/**
 * Admin Options Page
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Settings
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function adsensei_get_tab_header($page, $section){
    global $adsensei_options;
    global $wp_settings_fields;

    if (!isset($wp_settings_fields[$page][$section]))
        return;

    echo '<ul>';
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
    $sanitizedID = str_replace('[', '', $field['id'] );
    $sanitizedID = str_replace(']', '', $sanitizedID );
     if ( strpos($field['callback'],'header') !== false && !adsensei_is_excluded(array('help') ) ) {
         echo '<li class="adsensei-tabs"><a href="#' . $sanitizedID . '">' . $field['title'] .'</a></li>';
     }
    }
    echo '</ul>';
}

/**
 * Check if current page is excluded
 *
 * @param array $pages
 * @return boolean
 */
function adsensei_is_excluded($pages){
    if (isset($_GET['tab'])){
        $currentpage = $_GET['tab'];
        if (isset($currentpage) && in_array($currentpage, $pages))
                return true;
    }
}

function adsensei_do_settings_fields($page, $section) {
    global $wp_settings_fields;
    $header = false;
    $firstHeader = false;

    if (!isset($wp_settings_fields[$page][$section]))
        return;

    // Check first if any callback header registered
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
       strpos($field['callback'],'header') !== false ? $header = true : $header = false;

       if ($header === true)
               break;
    }

    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
       $sanitizedID = str_replace('[', '', $field['id'] );
       $sanitizedID = str_replace(']', '', $sanitizedID );

       // Check if header has been created previously
       if (strpos($field['callback'],'header') !== false && $firstHeader === false) {

           echo '<div id="' . $sanitizedID . '">';
           echo '<table class="adsensei-form-table"><tbody>';
           $firstHeader = true;

       } elseif (strpos($field['callback'],'header') !== false && $firstHeader === true) {
       // Header has been created previously so we have to close the first opened div
           echo '</table></div><div id="' . $sanitizedID . '">';
           echo '<table class="adsensei-form-table"><tbody>';
       }

        if (!empty($field['args']['label_for']) && !adsensei_is_excluded_title( $field['args']['id'] )){
            echo '<tr class="adsensei-row">';
            echo '<td class="adsensei-row th">';
            echo '<label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label>';
            echo '</td></tr>';
        }else if (!empty($field['title']) && !empty($field['args']['helper-desc']) && !adsensei_is_excluded_title( $field['args']['id'] ) ){
            echo '<tr class="adsensei-row">';
            echo '<td class="adsensei-row th">';//xss ok
            echo '<div class="col-title">' . $field['title'] . '<a class="adsensei-general-helper" href="#"></a><div class="adsensei-message">' . $field['args']['helper-desc']. '</div></div>';
            echo '</td></tr>';
        }else if (!empty($field['title']) && !empty($field['args']['id']) && !adsensei_is_excluded_title( $field['args']['id'] ) ){
            echo '<tr class="adsensei-row">';
            echo '<td class="adsensei-row th">'; //xss ok
            echo '<div class="col-title" id="'.$field['args']['id'].'">' . $field['title'] . '</div>';
            echo '</td></tr>';
        }

        else {
            echo '';
        }


        echo '<tr><td>';
            call_user_func($field['callback'], $field['args']);
        echo '</td></tr>';
    }
    echo '</tbody></table>';
    if ($header === true){
    echo '</div>';
    }
}

/**
 * If title is one of these entries do not show it
 */
function adsensei_is_excluded_title($string){
    $haystack = array('ad1','ad2','ad3','ad4','ad5','ad6','ad7','ad8','ad9','ad10',
        'ad1_widget',
        'ad2_widget',
        'ad3_widget',
        'ad4_widget',
        'ad5_widget',
        'ad6_widget',
        'ad7_widget',
        'ad8_widget',
        'ad9_widget',
        'ad10_widget',
        'vi_header',
        'vi_signup'
        );

    if (in_array($string, $haystack)){
            return true;
    }
    return false;
}



/**
 * Options Page New
 *
 * Renders the options page contents.
 *
 * @since 2.0
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_options_page_new() {

        global $adsensei_options;

        wp_enqueue_style('adsensei-admin-ad-style', ADSENSEI_PLUGIN_URL.'admin/assets/js/dist/style.css');
        wp_enqueue_style('adsensei-material-ui-font', 'https://fonts.googleapis.com/icon?family=Material+Icons');

        $get_ip =  get_option('add_blocked_ip') ?  get_option('add_blocked_ip')  : 0 ;
        $get_e_p_p_p = '20';
        if(is_user_logged_in()){
            $current_user = get_current_user_id();
        }
        if( is_user_logged_in() && $current_user){
        $user_info = get_user_meta($current_user);
        if( isset($user_info['edit_post_per_page']) ){
            $get_specific_user_meta = $user_info['edit_post_per_page'];
            // get edit_post_per_page
            $get_e_p_p_p = implode("",$get_specific_user_meta);
        }
        else{
            $get_specific_user_meta = '20' ;
        }
    }
        $ajax_call = admin_url( 'admin-ajax.php' );
        $get_admin_url = admin_url('admin.php');
        $get_activated_data = is_plugin_active('sitepress-multilingual-cms/sitepress.php') ? is_plugin_active('sitepress-multilingual-cms/sitepress.php') : 0 ;
        $data = array(
            'adsensei_plugin_url'     => ADSENSEI_PLUGIN_URL,
            'rest_url'             => esc_url_raw( rest_url() ),
            'nonce'                => wp_create_nonce( 'wp_rest' ),
            'is_amp_enable'        => function_exists('is_amp_endpoint') ? true : false,
            'is_bbpress_exist'     => class_exists( 'bbPress' )? true : false,
            'is_newsPapertheme_exist'     => class_exists( 'tagdiv_config' )? true : false,
            'adsensei_get_ips'     => $get_ip,
            'ajax_url' => $ajax_call,
            'num_of_ads_to_display' => $get_e_p_p_p,
            'get_admin_url' => $get_admin_url,
            'wpml_activation' => $get_activated_data

        );
        $data = apply_filters('adsensei_localize_filter',$data,'adsensei_localize_data');
        wp_register_script( 'adsensei-admin-ad-script', ADSENSEI_PLUGIN_URL . 'admin/assets/js/dist/adminscript.js', array( 'wp-i18n' ), ADSENSEI_VERSION );

        wp_localize_script( 'adsensei-admin-ad-script', 'adsensei_localize_data', $data );

        wp_enqueue_script('adsensei-admin-ad-script');

        echo '<div id="adsensei-ad-content"></div>';

        echo '<div class="adsensei-admin-debug">'.adsensei_get_debug_messages().'</div>';

}

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function adsensei_version_switch(){
    $adsensei_mode = get_option('adsensei-mode');
    if($adsensei_mode == 'new'){
        update_option('adsensei-mode','old');
    }else{
         update_option('adsensei-mode','new');
    }
    echo '<script>window.location="'.admin_url("admin.php?page=adsensei-settings").'";</script>';

}

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @global $adsensei_options Array of all the ADSENSEI Options
 * @return void
 */
function adsensei_options_page() {
	global $adsensei_options;

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], adsensei_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap adsensei_admin">
             <h1 style="text-align:center;"> <?php echo ADSENSEI_NAME . ' ' . ADSENSEI_VERSION; ?></h1>
		<h2 class="adsensei-nav-tab-wrapper">
			<?php
			foreach( adsensei_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = esc_url(add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) ));

				$active = $active_tab == $tab_id ? ' adsensei-nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="adsensei-nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="adsensei_tab_container" class="adsensei_tab_container">
                        <?php adsensei_get_tab_header( 'adsensei_settings_' . $active_tab, 'adsensei_settings_' . $active_tab ); ?>
                    <div class="adsensei-panel-container"> <!-- new //-->
			<form method="post" action="options.php" id="adsensei_settings">

				<?php
				settings_fields( 'adsensei_settings' );
				adsensei_do_settings_fields( 'adsensei_settings_' . $active_tab, 'adsensei_settings_' . $active_tab );
				?>
                                <?php  settings_errors(); ?>
				<?php
                                // do not show save button on add-on page
                                if ($active_tab !== 'addons' && $active_tab !== 'imexport' && $active_tab !== 'help'){
                                    $other_attributes = array( 'id' => 'adsensei-submit-button' );
                                    submit_button(null, 'primary', 'adsensei-save-settings' , true, $other_attributes );
                                }
                                ?>
			</form>
                        <div id="adsensei-footer">
                        <?php

                        if ($active_tab !== 'addons'){
                        echo sprintf( __( '<strong>If you like this plugin please do us a BIG favor and give us a 5 star rating <a href="%s" target="_blank">here</a> . If you have issues, open a <a href="%2s" target="_blank">support ticket</a>, so that we can sort it out. Thank you!</strong>', 'adsenseib30' ),
                           'https://wordpress.org/support/plugin/adsenseib30/reviews/#new-post',
                           'http://wpadsensei.com/support/'
                        );
                        echo '<br/><br/>' . sprintf( __( '<strong>Ads are not showing? Read the <a href="%s" target="_blank">troubleshooting guide</a> to find out how to resolve it', 'adsenseib30' ),
			                     'http://wpadsensei.com/docs/adsense-ads-are-not-showing/?utm_source=plugin&utm_campaign=wpadsensei-settings&utm_medium=website&utm_term=bottomlink',
                           'https://wp-staging.com/?utm_source=wpadsensei_plugin&utm_campaign=footer&utm_medium=website&utm_term=bottomlink'
                        );
                        }
                        ?>
                        </div>
                    </div>
                    <div style="display: inline-block;width: 242px;">
                    <div class="switch_to_v2">
                    <h3>WPAdsensei 2.0 has the better User interface</h3>
                    <p>We have improved the WPAdsensei and made it better than ever! Step into the future with one-click!</p>
                    <div onclick="adsensei_switch_version('new',this);" class="switch_to_v2_btn"><a  href="#">Switch to New Panel</a></div>
                    </div>
                </div>
		</div><!-- #tab_container-->
                <div id="adsensei-save-result"></div>
                <div class="adsensei-admin-debug"><?php echo adsensei_get_debug_messages(); ?></div>
                <?php echo adsensei_render_adsense_form(); ?>
	</div><!-- .wrap -->
	<?php
    echo ob_get_clean();

}

function adsensei_get_debug_messages(){
    global $adsensei_options;

    if (isset($adsensei_options['debug_mode']) && $adsensei_options['debug_mode'] == 1){
        echo '<pre style="clear:both;">';
        var_dump($adsensei_options);
        echo '</pre>';
    }
}

/**
 * Render social buttons
 *
 * @return void
 */
function adsensei_render_social(){
    ob_start()?>

        <div class='adsensei-share-button-container'>
                        <div class='adsensei-share-button adsensei-share-button-twitter' data-share-url="https://wordpress.org/plugins/adsenseib30">
                            <div clas='box'>
                                <a href="https://twitter.com/share?url=https://wordpress.org/plugins/adsenseib30&text=Quick%20AdSense%20reloaded%20-%20a%20brand%20new%20fork%20of%20the%20popular%20AdSense%20Plugin%20Quick%20Adsense!" target='_blank'>
                                    <span class='adsensei-share'><?php echo __('Tweet','adsenseib30'); ?></span>
                                </a>
                            </div>
                        </div>

                        <div class="adsensei-share-button adsensei-share-button-facebook" data-share-url="https://wordpress.org/plugins/adsenseib30">
                            <div class="box">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=https://wordpress.org/plugins/adsenseib30" target="_blank">
                                    <span class='adsensei-share'><?php echo __('Share','adsenseib30'); ?></span>
                                </a>
                            </div>
                        </div>
            </div>

        <?php
        echo ob_get_clean();
}


/**
 * Render AdSense Form
 */
function adsensei_render_adsense_form(){

?>
<div id="adsensei-adsense-bg-div" style="display: none;">
    <div id="adsensei-adsense-container">
        <h3><?php _e( 'Enter <a ahref="https://adsplugin.net/help/how-to-create-and-where-to-get-adsense-code/" target="_blank">AdSense text & display ad code</a> here', 'adsenseib30' ); ?></h3>
        <?php _e('Do not enter <a href="https://adsplugin.net/help/integrate-page-level-ads-wordpress/" target="_blank">AdSense page level ads</a> or <a href="https://adsplugin.net/introducing-new-adsense-auto-ads/" target="_blank">Auto ads!</a> <br> <a href="https://adsplugin.net/help/how-to-create-and-where-to-get-adsense-code/" target="_blank">Learn how to create AdSense ad code</a>', 'adsenseib30'); ?>
        <textarea rows="15" cols="55" id="adsensei-adsense-form"></textarea><hr />
        <button class="button button-primary" id="adsensei-paste-button"><?php _e( 'Get Code', 'adsenseib30' ); ?></button>&nbsp;&nbsp;
        <button class="button button-secondary" id="adsensei-close-button"><?php _e( 'Close', 'adsenseib30' ); ?></button>
        <div id="adsensei-msg"></div>
        <input type="hidden" id="adsensei-adsense-id" value="">
    </div>
</div>
<?php
}
