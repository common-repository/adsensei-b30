<?php

/**
 * Helper Functions
 *
 * @package     ADSENSEI
 * @subpackage  Helper/Templates
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

/**
 * Helper method to check if user is in the plugins page.
 * @since  1.4.0
 *
 * @return bool
 */
function adsensei_is_plugins_page() {
    global $pagenow;

    return ( 'plugins.php' === $pagenow );
}

/**
 * display deactivation logic on plugins page
 *
 * @since 1.4.0
 */
function adsensei_add_deactivation_feedback_modal() {

    $screen = get_current_screen();
    if( !is_admin() && !adsensei_is_plugins_page()) {
        return;
    }

    $current_user = wp_get_current_user();
    if( !($current_user instanceof WP_User) ) {
        $email = '';
    } else {
        $email = trim( $current_user->user_email );
    }

    include ADSENSEI_PLUGIN_DIR . 'includes/admin/views/deactivate-feedback.php';
}

/**
 * send feedback via email
 *
 * @since 1.4.0
 */
function adsensei_send_feedback() {

    if( isset( $_POST['data'] ) ) {
        parse_str( $_POST['data'], $form );
    }

    $text = '';
    if( isset( $form['adsensei_disable_text'] ) ) {
        $text = implode( "\n\r", $form['adsensei_disable_text'] );
    }

    $headers = array();

    $from = isset( $form['adsensei_disable_from'] ) ? $form['adsensei_disable_from'] : '';
    if( $from ) {
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
    }

    $subject = isset( $form['adsensei_disable_reason'] ) ? $form['adsensei_disable_reason'] : '(no reason given)';

    $success = wp_mail( 'team@magazine3.in', $subject, $text, $headers );

    //error_log(print_r($success, true));
    //error_log($from . $subject . var_dump($form));
    die();
}
add_action( 'wp_ajax_adsensei_send_feedback', 'adsensei_send_feedback' );
