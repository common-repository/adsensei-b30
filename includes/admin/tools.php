<?php
/**
 * Tools
 *
 * These are functions used for displaying ADSENSEI tools such as the import/export system.
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Tools
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains ADSENSEI-specific tools including the
 * built-in import/export system.
 *
 * @since       0.9.0
 * @return      void
 */
function adsensei_tools_page() {
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'import_export';
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 class="adsensei-nav-tab-wrapper">
			<?php
			foreach( adsensei_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab' => $tab_id
				) );

				$tab_url = remove_query_arg( array(
					'adsensei-message'
				), $tab_url );

				$active = $active_tab == $tab_id ? ' adsensei-nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="adsensei-nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'adsensei_tools_tab_' . $active_tab );
			?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
<?php
}


/**
 * Retrieve tools tabs
 *
 * @since       2.1.6
 * @return      array
 */
function adsensei_get_tools_tabs() {

	$tabs                  = array();
	$tabs['import_export'] = __( 'Import/Export', 'adsenseib30' );
       $tabs['system_info'] = __( 'System Info', 'adsenseib30' );

	return apply_filters( 'adsensei_tools_tabs', $tabs );
}



/**
 * Display the tools import/export tab
 *
 * @since       2.1.6
 * @return      void
 */
function adsensei_tools_import_export_display() {

        if( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	do_action( 'adsensei_tools_import_export_before' );
?>
        <!-- We have to close the old form first//-->

	<div class="adsensei-postbox">
		<h3><span><?php _e( 'Export Settings', 'adsenseib30' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Export the Adsensei settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'adsenseib30' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'admin.php?page=adsensei-settings&tab=imexport' ); ?>" id="adsensei-export-settings">
				<p><input type="hidden" name="adsensei-action" value="export_settings" /></p>
				<p>
					<?php wp_nonce_field( 'adsensei_export_nonce', 'adsensei_export_nonce' ); ?>
					<?php submit_button( __( 'Export', 'adsenseib30' ), 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->

	<div class="adsensei-postbox">
		<h3><span><?php _e( 'Import Settings', 'adsenseib30' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import the Adsensei settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'adsenseib30' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=adsensei-settings&tab=imexport' ); ?>">
				<p>
					<input type="file" name="import_file"/>
				</p>
				<p>
					<input type="hidden" name="adsensei-action" value="import_settings" />
					<?php wp_nonce_field( 'adsensei_import_nonce', 'adsensei_import_nonce' ); ?>
					<?php submit_button( __( 'Import', 'adsenseib30' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'adsensei_tools_import_export_after' );
}
add_action( 'adsensei_tools_tab_import_export', 'adsensei_tools_import_export_display' );



/* check if function is disabled or not
 *
 * @returns bool
 * @since 2.1.6
 */
function adsensei_is_func_disabled( $function ) {
  $disabled = explode( ',',  ini_get( 'disable_functions' ) );
  return in_array( $function, $disabled );
}

/**
 * Process a settings export that generates a .json file of the Adsensei settings
 *
 * @since       2.1.6
 * @return      void
 */
function adsensei_tools_import_export_process_export() {
	if( empty( $_POST['adsensei_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['adsensei_export_nonce'], 'adsensei_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_options' ) )
		return;

	$settings = array();
	$settings = get_option( 'adsensei_settings' );

	ignore_user_abort( true );

	if ( ! adsensei_is_func_disabled( 'set_time_limit' ) )
		@set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . apply_filters( 'adsensei_settings_export_filename', 'adsensei-settings-export-' . date( 'm-d-Y' ) ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'adsensei_export_settings', 'adsensei_tools_import_export_process_export' );

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since 1.0
 * @param unknown $str File name
 * @return mixed File extension
 */
 function adsensei_get_file_extension( $str ) {
     $parts = explode( '.', $str );
     return end( $parts );
}

/* Convert an object to an associative array.
 * Can handle multidimensional arrays
 *
 * @returns array
 * @since 2.1.6
 */
function adsensei_object_to_array( $data ) {
  if ( is_array( $data ) || is_object( $data ) ) {
    $result = array();
    foreach ( $data as $key => $value ) {
      $result[ $key ] = adsensei_object_to_array( $value );
    }
    return $result;
  }
  return $data;
}

/**
 * Process a settings import from a json file
 *
 * @since 2.1.6
 * @return void
 */
function adsensei_tools_import_export_process_import() {
	if( empty( $_POST['adsensei_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['adsensei_import_nonce'], 'adsensei_import_nonce' ) )
		return;

	if( ! current_user_can( 'update_plugins' ) )
		return;

    if( adsensei_get_file_extension( $_FILES['import_file']['name'] ) != 'json' ) {
        wp_die( __( 'Please upload a valid .json file', 'adsenseib30' ) );
    }

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'adsenseib30' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = adsensei_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'adsensei_settings', $settings );

	wp_safe_redirect( admin_url( 'admin.php?page=adsensei-settings&adsensei-message=settings-imported&tab=imexport' ) ); exit;

}
add_action( 'adsensei_import_settings', 'adsensei_tools_import_export_process_import' );


/**
 * Display the system info tab
 *
 * @since       2.1.6
 * @return      void
 * @change      2.3.1
 */
function adsensei_tools_sysinfo_display() {

    if( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

?>
	<!--<form action="<?php //echo esc_url( admin_url( 'admin.php?page=adsensei-settings&tab=system_info' ) ); ?>" method="post" dir="ltr">//-->
		<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" name="adsensei-sysinfo" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac)."><?php echo adsensei_tools_sysinfo_get(); ?></textarea>
		<!--
                <p class="submit">
			<input type="hidden" name="adsensei-action" value="download_sysinfo" />-->
			<?php //submit_button( 'Download System Info File', 'primary', 'adsensei-download-sysinfo', false ); ?>
		<!--</p>//-->
	<!--</form>//-->
<?php
       echo '<br>' . adsensei_render_backup_settings();

}
add_action( 'adsensei_tools_tab_system_info', 'adsensei_tools_sysinfo_display' );

/**
 * Render textarea with backup settings from previous version 1.5.2
 * @return string
 */
function adsensei_render_backup_settings(){
       if( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

       $settings = json_encode(get_option('adsensei_settings_1_5_2'));
       echo '<h3>' . __('Backup data from WP ADSENSEI 1.5.2', 'adsenseib30') .  '</h3>' . __('Copy and paste this data into an empty text file with extension *.json');
       ?>

       <textarea readonly="readonly" onclick="this.focus(); this.select()" id="backup-settings-textarea" name="adsensei-backupsettings" title="To copy the backup settings info, click below then press Ctrl + C (PC) or Cmd + C (Mac)."><?php echo $settings; ?></textarea>
<?php
}


/**
 * Get system info
 *
 * @since       2.1.6
 * @access      public
 * @global      object $wpdb Used to query the database using the WordPress Database API
 * @global      array $adsensei_options Array of all ADSENSEI options
 * @return      string $return A string containing the info to output
 */
function adsensei_tools_sysinfo_get() {
	global $wpdb, $adsensei_options;

	if( !class_exists( 'Browser' ) )
		require_once ADSENSEI_PLUGIN_DIR . 'includes/libraries/browser.php';

	$browser = new Browser();

	// Get theme info
	if( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
	}


	$return  = '### Begin System Info ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$return  = apply_filters( 'adsensei_sysinfo_after_site_info', $return );


	// The local users' browser information, handled by the Browser class
	$return .= "\n" . '-- User Browser' . "\n\n";
	$return .= $browser;

	$return  = apply_filters( 'adsensei_sysinfo_after_user_browser', $return );

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
	}

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'     => false,
		'timeout'       => 60,
		'user-agent'    => 'ADSENSEI/' . ADSENSEI_VERSION,
		'body'          => $request
	);

	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return  = apply_filters( 'adsensei_sysinfo_after_wordpress_config', $return );

	// ADSENSEI configuration
	$return .= "\n" . '-- ADSENSEI Configuration' . "\n\n";
	$return .= 'Version:                  ' . ADSENSEI_VERSION . "\n";
	$return .= 'Upgraded From:            ' . get_option( 'adsensei_version_upgraded_from', 'None' ) . "\n";

	$return  = apply_filters( 'adsensei_sysinfo_after_adsensei_config', $return );


	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";
	 if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach( $plugins as $plugin_path => $plugin ) {
		if( !in_array( $plugin_path, $active_plugins ) )
			continue;

		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
	}

	$return  = apply_filters( 'adsensei_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach( $plugins as $plugin_path => $plugin ) {
		if( in_array( $plugin_path, $active_plugins ) )
			continue;

		$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
	}

	$return  = apply_filters( 'adsensei_sysinfo_after_wordpress_plugins_inactive', $return );

	if( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if( !array_key_exists( $plugin_base, $active_plugins ) )
				continue;

			$plugin  = get_plugin_data( $plugin_path );
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
		}

		$return  = apply_filters( 'adsensei_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return  = apply_filters( 'adsensei_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

	$return  = apply_filters( 'adsensei_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return  = apply_filters( 'adsensei_sysinfo_after_php_ext', $return );

	$return .= "\n" . '### End System Info ###';

	return $return;
}


/**
 * Generates a System Info download file
 *
 * @since       2.0
 * @return      void
 */
function adsensei_tools_sysinfo_download() {

        if( ! current_user_can( 'update_plugins' ) )
		return;

	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="adsensei-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['adsensei-sysinfo'] );
	wp_die();
}
add_action( 'adsensei_download_sysinfo', 'adsensei_tools_sysinfo_download' );

/*
 * Import settings from Quick AdSense reloaded  v. 1.9.2
 */

function adsensei_import_quick_adsense_settings(){
    // Check first if Quick AdSense is installed and version matches
    if (!adsensei_check_quick_adsense_version())
        return;


        if( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	do_action( 'adsensei_import_quick_adsense_settings_before' );
?>
	<div class="adsensei-postbox" id="adsensei-import-settings">
		<h3><span><?php _e( 'Import from Quick AdSense', 'adsenseib30' ); ?></span></h3>
		<div class="inside">
			<p><?php _e( 'Import the settings for Adsensei from Quick AdSense v. 1.9.2.', 'adsenseib30' ); ?></p>

			<!--
                        <form id="adsensei_quick_adsense_input" method="post" action="<?php echo admin_url( 'admin.php?page=adsensei-settings&tab=imexport' ); ?>" onsubmit="return confirm('Importing the settings from Quick AdSense will overwrite all your current settings. Are you sure?');">
                        -->
				<p><input type="hidden" name="adsensei-action" value="import_quick_adsense" /></p>
				<p>
					<?php wp_nonce_field( 'adsensei_quick_adsense_nonce', 'adsensei_quick_adsense_nonce' ); ?>
					<?php submit_button( __( 'Start Import process', 'adsenseib30' ), 'primary adsensei-import-settings', 'submit', false ); ?>
				</p>
			<!--</form>-->
                        <div id="adsensei-error-details"></div>
		</div><!-- .inside -->
	</div><!-- .postbox -->
<?php
	do_action( 'adsensei_import_quick_adsense_settings_after' );
}
add_action( 'adsensei_import_quick_adsense_settings', 'adsensei_import_quick_adsense_settings' );

/**
 * Ajax process a settings import from Quick AdSense
 *
 * @since       0.9.0
 * @return      string json
 */
function adsensei_import_quick_adsense_process() {

        check_ajax_referer( 'adsensei_ajax_nonce', 'nonce' );

	if( ! current_user_can( 'manage_options' ) )
		return;

	ignore_user_abort( true );

	if ( ! adsensei_is_func_disabled( 'set_time_limit' ) )
		set_time_limit( 0 );


        $adsensei_settings          = get_quick_adsense_setting();
        $adsensei_reloaded_settings = get_option('adsensei_settings');


        if (update_option('adsensei_settings', $adsensei_settings ) ){
            $message = __('Most of the settings have been sucessfully imported from Quick AdSense <br> but due to some inconsistencies there are still some options which needs your attention and manual adjusting.','adsenseib30');
            wp_send_json ( $message );
        }

        $message = __('Most of settings have been already imported successfully! (If not we probably have an unknown issue here)', 'adsenseib30');
        //$message = get_quick_adsense_setting();
        wp_send_json ( $message );

}
//add_action( 'adsensei_import_quick_adsense', 'adsensei_import_quick_adsense_process' );
add_action('wp_ajax_adsensei_import_quick_adsense', 'adsensei_import_quick_adsense_process');

/**
 * Clear all the cache to apply latest changes
 *
 * @return boolean true when it is installed and version matches
 */
function adsensei_clear_cache(){
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}
	if ( defined( 'WPCACHEHOME' ) ) {
		global  $file_prefix;
		wp_cache_clean_cache( $file_prefix, true );
	}

}
add_action('wp_ajax_adsensei_clear_cache', 'adsensei_clear_cache');


/**
 * Check if Quick AdSense is installed and if version is 1.9.2
 *
 * @return boolean true when it is installed and version matches
 */
function adsensei_check_quick_adsense_version(){
    $plugin_file = 'quick-adsense/quick-adsense.php';
    $plugin_abs_path = get_home_path() . '/wp-content/plugins/quick-adsense/quick-adsense.php';
    $checkVersion = '1.9.2';

    if ( is_plugin_active( $plugin_file ) ) {
        $plugin_data = get_plugin_data( $plugin_abs_path, $markup = true, $translate = true );

        if ($plugin_data['Version'] === $checkVersion)
            return true;
    }

    if ( file_exists( $plugin_abs_path ) && is_plugin_inactive( $plugin_file ) ) {
        $plugin_data = get_plugin_data( $plugin_abs_path, $markup = true, $translate = true );

        if ($plugin_data['Version'] === $checkVersion)
            return true;
    }

}

/**
 * Get all Quick AdSense settings and convert them to a Quick AdSense reloaded compatible array
 *
 * @since 0.9.0
 * @return array
 */
function get_quick_adsense_setting() {
    $amountAds = 10;
    $amountWidgets = 10;
    $settings = array();
    $new_align = '';


    for ( $i = 1; $i <= $amountAds; $i++ ) {
        if( get_option( 'AdsCode' . $i ) != '' ) {
            $settings['ad' . $i]['code'] = get_option( 'AdsCode' . $i );
            $settings['ad' . $i]['margin'] = get_option( 'AdsMargin' . $i );
            //$settings['ad' . $i]['align'] = get_option( 'AdsAlign' . $i );
            // convert the old margin values into the new ones
            $old_align = get_option( 'AdsAlign' . $i );
            if (isset($old_align) && $old_align === '1'){ // right
                $new_align = '0';
            } else if(isset($old_align) && $old_align === '2'){ // center
                $new_align = '1';
            } else if(isset($old_align) &&$old_align === '3'){ // right
                $new_align = '2';
            } else if(isset($old_align) &&$old_align === '4'){ // none
                $new_align = '3';
            }
            $settings['ad' . $i]['align'] = $new_align;
        }
    }
    for ( $i = 1; $i <= $amountWidgets; $i++ ) {
        if( get_option( 'WidCode' . $i ) != '' ) {
            $settings['ad' . $i . '_widget'] = get_option( 'WidCode' . $i );
        }
    }
    $settings['maxads'] = get_option( 'AdsDisp' );
    $settings['pos1']['BegnAds'] = get_option( 'BegnAds' );
    $settings['pos1']['BegnRnd'] = get_option( 'BegnRnd' );
    $settings['pos2']['MiddAds'] = get_option( 'MiddAds' );
    $settings['pos2']['MiddRnd'] = get_option( 'MiddRnd' );
    $settings['pos3']['EndiAds'] = get_option( 'EndiAds' );
    $settings['pos3']['EndiRnd'] = get_option( 'EndiRnd' );
    $settings['pos4']['MoreAds'] = get_option( 'MoreAds' );
    $settings['pos4']['MoreRnd'] = get_option( 'MoreRnd' );
    $settings['pos5']['LapaAds'] = get_option( 'LapaAds' );
    $settings['pos5']['LapaRnd'] = get_option( 'LapaRnd' );
    $rc = 3;
    $value = 5;
    for ( $j = 1; $j <= $rc; $j++ ) {
        $key = $value + $j;
        $settings['pos' . $key]['Par' . $j . 'Ads'] = get_option( 'Par' . $j . 'Ads' );
        $settings['pos' . $key]['Par' . $j . 'Rnd'] = get_option( 'Par' . $j . 'Rnd' );
        $settings['pos' . $key]['Par' . $j . 'Nup'] = get_option( 'Par' . $j . 'Nup' );
        $settings['pos' . $key]['Par' . $j . 'Con'] = get_option( 'Par' . $j . 'Con' );
    }
    $settings['pos9']['Img1Ads'] = get_option( 'Img1Ads' );
    $settings['pos9']['Img1Rnd'] = get_option( 'Img1Rnd' );
    $settings['pos9']['Img1Nup'] = get_option( 'Img1Nup' );
    $settings['pos9']['Img1Con'] = get_option( 'Img1Con' );
    //$settings['visibility']['AppPost'] = get_option( 'AppPost' );
    //$settings['visibility']['AppPage'] = get_option( 'AppPage' );
    $settings['visibility']['AppHome'] = get_option( 'AppHome' );
    $settings['visibility']['AppCate'] = get_option( 'AppCate' );
    $settings['visibility']['AppArch'] = get_option( 'AppArch' );
    $settings['visibility']['AppTags'] = get_option( 'AppTags' );
    $settings['visibility']['AppMaxA'] = get_option( 'AppMaxA' );
    $settings['visibility']['AppSide'] = get_option( 'AppSide' );
    $settings['visibility']['AppLogg'] = get_option( 'AppLogg' );
    $settings['quicktags']['QckTags'] = get_option( 'QckTags' );
    $settings['quicktags']['QckRnds'] = get_option( 'QckRnds' );
    $settings['quicktags']['QckOffs'] = get_option( 'QckOffs' );
    $settings['quicktags']['QckOfPs'] = get_option( 'QckOfPs' );

    // Get previous settings for AppPost and AppPage
    $post_setting_old = (false !== get_option( 'AppPost' )) ? true : false;
    $page_setting_old = (false !== get_option( 'AppPage' )) ? true : false;
    // Store them in new array post_types
    if (true === $post_setting_old && true === $page_setting_old) {
        $settings['post_types'] = array('post', 'page');
    } else if (true === $post_setting_old && false === $page_setting_old) {
        $settings['post_types'] = array('post');
    } else if (false === $post_setting_old && true === $page_setting_old) {
        $settings['post_types'] = array('page');
    }

    $settings1 = adsensei_str_replace_json( "true", "1", $settings );
    return $settings1;
}

/**
 * A faster way to replace the strings in multidimensional array is to json_encode() it,
 * do the str_replace() and then json_decode() it
 *
 * @param string $search
 * @param string $replace
 * @param array $subject
 * @return array
 */
function adsensei_str_replace_json($search, $replace, $subject){
     $stdClass = json_decode(str_replace($search, $replace, json_encode($subject)));

     return adsensei_object_to_array($stdClass);
}
