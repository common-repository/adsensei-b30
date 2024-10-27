<?php
namespace ElementorAdsensei;

class Plugin {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function include_widgets_files() {
		require_once( __DIR__ . '/adsensei_elementor.php' );
	}

	public function register_widgets() {
		// Its is now safe to include Widgets files
		$this->include_widgets_files();

		// Register Widgets
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\Adsensei_Elementor() );
	}


	public function __construct() {
		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
	}
}

Plugin::instance();
