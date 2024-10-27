<?php

/**
 * Admin Actions
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Actions
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Processes all ADSENSEI actions sent via POST and GET by looking for the 'adsensei-action'
 * request and running do_action() to call the function
 *
 * @since 1.0
 * @return void
 */
function adsensei_process_actions() {
    if (isset($_POST['adsensei-action'])) {
        do_action('adsensei_' . $_POST['adsensei-action'], $_POST);
    }

    if (isset($_GET['adsensei-action'])) {
        do_action('adsensei_' . $_GET['adsensei-action'], $_GET);
    }
}

add_action('admin_init', 'adsensei_process_actions');

/**
 * Update option adsensei_show_theme_notice
 * "no" means no further upgrade notices are shown
 */
function adsensei_close_upgrade_notice() {
    update_option('adsensei_show_theme_notice', 'no');
}

add_action('adsensei_close_upgrade_notice', 'adsensei_close_upgrade_notice');

/**
 * Close vi welcome notice and do not show again
 */
function adsensei_close_vi_welcome_notice() {
    update_option('adsensei_close_vi_welcome_notice', 'yes');
}

add_action('adsensei_close_vi_welcome_notice', 'adsensei_close_vi_welcome_notice');

/**
 * Close vi ads txt notice and do not show again
 */
function adsensei_close_adsensei_vi_ads_txt_notice() {

    delete_transient('adsensei_vi_ads_txt_notice');
}
add_action('adsensei_close_adsensei_vi_ads_txt_notice', 'adsensei_close_adsensei_vi_ads_txt_notice');


/**
 * Close vi update notice and show it one week later again
 */
function adsensei_show_vi_notice_later() {
    $nextweek = time() + (7 * 24 * 60 * 60);
    $human_date = date('Y-m-d h:i:s', $nextweek);
    update_option('adsensei_show_vi_notice_later', $human_date);
    update_option('adsensei_close_vi_notice', 'yes');
}

add_action('adsensei_show_vi_notice_later', 'adsensei_show_vi_notice_later');

/**
 * Save vi token
 */
function adsensei_save_vi_token() {
    global $adsensei_options;
    
    if (empty($_POST['token']) || !is_string($_POST['token'])) {
        echo json_encode(array("status" => "failed", "error" => "Invalid token format"));
        wp_die();
      }
    $token = esc_html($_POST['token']);
    // Save token before trying to create ads.txt
    update_option('adsensei_vi_token', $token );

    if (!isset($adsensei_options['adsTxtEnabled'])) {
        set_transient('adsensei_vi_ads_txt_disabled', true, 300);
        delete_transient('adsensei_vi_ads_txt_error');
        delete_transient('adsensei_vi_ads_txt_notice');
        echo json_encode(array("status" => "success", "token" => $token, "adsTxt" => 'disabled'));
        wp_die();
    }

    $vi = new wpadsensei\vi();

    if ($vi->createAdsTxt()) {
        set_transient('adsensei_vi_ads_txt_notice', true, 300);
        delete_transient('adsensei_vi_ads_txt_error');
    } else {
        set_transient('adsensei_vi_ads_txt_error', true, 300);
        delete_transient('adsensei_vi_ads_txt_notice');
    }


    // Create AdSense ads.txt entries
    $adsense = new \wpadsensei\adsense($adsensei_options);
    $adsense->writeAdsTxt();

    //sleep(5);
    echo json_encode(array("status" => "success", "token" => $token));
    wp_die();
}

add_action('wp_ajax_adsensei_save_vi_token', 'adsensei_save_vi_token');

add_action('wp_ajax_adsensei_id_delete', 'adsensei_id_delete');
function adsensei_id_delete(){
    delete_option('add_blocked_ip');
    echo 'Operation success';
    exit;
}

/**
 * Save vi ad settings and create ad code
 */
function adsensei_save_vi_ads() {
    global $adsensei;

    $return = $adsensei->vi->setAdCode();

    if ($return) {
        wp_die($return);
    } else {
        wp_die(array('status' => 'error', 'message' => 'Unknown API Error. Can not get vi ad code'));
    }
}
add_action('wp_ajax_adsensei_save_vi_ads', 'adsensei_save_vi_ads');

/**
 * Logout of vi
 */
function adsensei_logout_vi() {
    delete_option('adsensei_vi_token');
}
add_action('adsensei_logout_vi', 'adsensei_logout_vi');

/**
 * Hide ads txt error notice
 */
function adsensei_close_ads_txt_error() {
    delete_transient('adsensei_ads_txt_error');
}
add_action('adsensei_close_ads_txt_error', 'adsensei_close_ads_txt_error');
