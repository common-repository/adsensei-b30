<?php
namespace ElementorAdsensei\Widgets;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Adsensei_Elementor extends Widget_Base {

	public function get_name() {
		return 'wp-adsensei';
	}

	public function get_title() {
		return __( 'WP ADSENSEI', 'wp-adsensei' );
	}

	public function get_icon() {
		return 'dashicons dashicons-welcome-widgets-menus';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_script_depends() {
		return [ 'elementor-wp-adsensei' ];
	}

	protected function _register_controls() {
		$options =array();
		foreach(adsensei_get_ads() as $key => $value){
			if($key == 0)
			$options['[adsensei id=RndAds]'] =$value;
			else
			 $options['[adsensei id='.$key.']'] =$value;
		}

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'wp-adsensei' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'seleted_add',
			[
				'label' => __( 'Select add to Display', 'wp-adsensei' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
			]
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		echo  $settings['seleted_add'] ;
	}

	protected function _content_template() {
		?>
	 {{ settings.seleted_add }}
		<?php
	}
}