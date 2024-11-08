<?php
/**
 * Welcome Page Class
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Welcome
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * adsensei_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */
class adsensei_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 1.0.1
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'welcome'    ) );
		add_filter( 'mce_buttons', array( $this, 'adsensei_register_buttons' ) );
		add_filter( 'tiny_mce_plugins', array( $this, 'tiny_mce_plugins' ) );
		add_filter( 'wp_tiny_mce_init', array( $this, 'print_shortcode_plugin' ) );
		add_action( 'print_default_editor_scripts', array( $this, 'print_shortcode_plugin' ) );


	}


	private function hooks_exist() {
		if (
			(
				has_action( 'wp_tiny_mce_init', array( $this, 'print_shortcode_plugin' ) )
				|| add_action( 'print_default_editor_scripts', array( $this, 'print_shortcode_plugin' ) )
			)
			&& has_filter( 'mce_buttons', array( $this, 'adsensei_register_buttons' ) )
			&& has_filter( 'tiny_mce_plugins', array( $this, 'tiny_mce_plugins' ) ) ) {
			return true;
		}
		return false;
	}
	/**
	 * Sends user to the Settings page on first activation of ADSENSEI as well as each
	 * time ADSENSEI is upgraded to a new version
	 *
	 * @access public
	 * @since 1.0.1
	 * @return void
	 */
	public function welcome() {
		// Bail if no activation redirect
		if ( false === get_transient( 'adsensei_activation_redirect' ) ){
			return;
                }

		// Delete the redirect transient
		delete_transient( 'adsensei_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ){
			return;
                }

		$upgrade = get_option( 'adsensei_version_upgraded_from' );
		wp_safe_redirect( admin_url( 'admin.php?page=adsensei-settings' ) ); exit;

	}

	public function tiny_mce_plugins( $plugins ) {
		if ( ! $this->hooks_exist() ) {
			return $plugins;
		}

		$plugins[] = 'adsensei_shortcode';
		return $plugins;
	}


	/**
	 * Add the plugin to array of external TinyMCE plugins
	 *
	 * @param array $plugin_array array with TinyMCE plugins.
	 *
	 * @return array
	 */
		public function print_shortcode_plugin(  ) {

			static $printed = null;

		if ( $printed !== null ) {
			return;
		}

		$printed = true;

		if ( ! $this->hooks_exist() ) {
			return;
		}
		echo "<script>\n"

			. file_get_contents( ADSENSEI_PLUGIN_DIR . 'assets/js/tinymce_shortcode.js' ) . "\n"
			. "</script>\n";

	}

	/**
	 * Add button to tinyMCE window.
	 *
	 * @param array $buttons array with existing buttons.
	 *
	 * @return array
	 */
	public function adsensei_register_buttons( $buttons ) {
		if ( ! is_array( $buttons ) ) {
			$buttons = array();
		}
		$buttons[] = 'adsensei_shortcode_button';
		return $buttons;
	}
}
new adsensei_Welcome();
