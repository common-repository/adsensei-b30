<?php
/**
 * Admin Pages
 *
 * @package     ADSENSEI
 * @subpackage  Admin/Pages
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the Adsensei menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $adsensei_settings_page
 * @global $adsensei_add_ons_page
 * @return void
 */
function adsensei_add_options_link() {
    global $adsensei_options, $adsensei_parent_page, $adsensei_add_ons_page, $adsensei_add_ons_page2,$adsensei_permissions, $adsensei_settings_page, $adsensei_mode;

    $adsensei_mode = get_option('adsensei-mode');

    $label = 'Adsmonetizer';

    $create_settings = isset($adsensei_options['create_settings']) ? true : false;
    if ($create_settings && $adsensei_mode != 'new') {
        $adsensei_settings_page = add_submenu_page('options-general.php', __('WP ADSENSEI Settings', 'adsenseib30'), __('WPADSENSEI', 'adsenseib30'),$adsensei_permissions, 'adsensei-settings', 'adsensei_options_page');
    } else {
        $wpadsensei_logo = "PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIKICJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy1TVkctMjAwMTA5MDQvRFREL3N2ZzEwLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiB3aWR0aD0iMjA5LjAwMDAwMHB0IiBoZWlnaHQ9IjE4Mi4wMDAwMDBwdCIgdmlld0JveD0iMCAwIDIwOS4wMDAwMDAgMTgyLjAwMDAwMCIKIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPgoKPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsMTgyLjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSIKZmlsbD0iIzAwMDAwMCIgc3Ryb2tlPSJub25lIj4KPHBhdGggZD0iTTkyMCAxNjg0IGMtNjUgLTEzIC0xNTQgLTQ5IC0yMDcgLTgzIC0xMTUgLTc2IC0yMTQgLTIxMyAtMjMzIC0zMjQKLTE5IC0xMDggMTYgLTI1NiA4NiAtMzYyIDE4IC0yNyAyOSAtNTQgMjUgLTYwIC00IC01IC00MiAtNTYgLTg1IC0xMTIgLTQyCi01NyAtODAgLTEwMyAtODMgLTEwMyAtNCAwIC0yNiAxOSAtNDkgNDEgLTIzIDIzIC00NyAzOCAtNTMgMzUgLTcgLTUgLTkgLTMyCi01IC03OSBsNyAtNzMgLTcyIC0yOCBjLTQwIC0xNiAtNzEgLTM0IC03MSAtNDEgMCAtOCAxOCAtMTkgNDMgLTI1IDExMiAtMjgKMTAxIC0yMCAxMTAgLTg1IDE4IC0xMzAgMjEgLTEzMSA4MCAtMzkgNDAgNjMgNDMgNjYgNzYgNjAgMTggLTMgMzUgLTcgMzcgLTkKMiAtMSAtOCAtMzAgLTIxIC02MyAtMzQgLTgzIC0zMyAtMTI4IDYgLTE2NiAyOSAtMjkgMzMgLTMwIDk3IC0yNSAzNyAzIDExNQoyMSAxNzQgNDEgMTQ3IDQ5IDIyNSA0OSA0MTQgMSAxNTYgLTQxIDI2MSAtNDcgMzE0IC0yMCA2NCAzMyA4OCAxMjIgNTUgMjAyCi0xOSA0NSAtMTkgNDMgOCA0MyAxNiAwIDMzIC0xNyA2MiAtNjMgMjIgLTM0IDQ0IC02MiA0OSAtNjIgMTYgMCAyNSAyOCAzMSA5NgpsNiA2NyAzNyA2IGM2OSAxMSAxMTIgMjcgMTEyIDQxIDAgNyAtMzEgMjYgLTcxIDQxIGwtNzIgMjggNiA3MyBjMyA0NSAyIDc1Ci01IDc5IC01IDQgLTMyIC0xNSAtNTkgLTQyIC0yNyAtMjYgLTUwIC00NiAtNTIgLTQzIC0yIDIgLTQ1IDU5IC05NSAxMjYgbC05MgoxMjIgMzAgNDcgYzE2IDI2IDM3IDcwIDQ2IDk4IDkgMjggMjAgNTUgMjUgNjEgNSA1IDkgNDkgOSA5OCAwIDc5IC00IDk3IC0zNgoxNzIgLTg4IDIwNCAtMjU0IDMyMSAtNDY5IDMzMCAtNDkgMiAtMTAxIDEgLTExNSAtMXogbTE4MCAtNTI1IGM5NCAtMTEgMTI3Ci0yMyA5OCAtMzQgLTEwIC00IC0xOCAtMTMgLTE4IC0yMSAwIC0xOSA2OSAtMTkgOTYgMSAxOSAxMyAyOCAxMiA4OCAtNyAzNgotMTIgNjYgLTI3IDY2IC0zMyAwIC0yMyAtMTMwIC0xMDIgLTI0MyAtMTQ5IC01NiAtMjMgLTE5MCAtNDYgLTI2NiAtNDYgLTkxIDEKLTIyMCAyNSAtMjUwIDQ4IC0xNSAxMCAtNDEgNDcgLTU5IDgyIC0zNyA3MiAtMzUgODAgMzQgMTA2IDQwIDE1IDQ1IDE1IDY4IDAKMzEgLTIwIDk2IC0yMiA5NiAtMiAwIDcgLTEwIDIwIC0yMiAyOSAtMjIgMTQgLTIyIDE1IDI3IDIyIDI4IDMgNTUgOCA2MCAxMAoyMiA3IDE0NSA0IDIyNSAtNnogbTM4MSAtNDgxIGM5IC0xNyA5IC0yMCAtNCAtMTUgLTE5IDcgLTM3IC0xMSAtMzEgLTMyIDMgLTkKLTEgLTYgLTcgNyAtMTAgMTggLTkgMjUgNiA0MiAyMiAyNSAyMiAyNSAzNiAtMnogbS04NzMgLTcgYzI2IC0yMyAyOCAtMzcgNgotNDUgLTExIC00IC0xNSAtMiAtMTEgOCA4IDIxIC0xMyAzNyAtMzUgMjUgLTIwIC0xMSAtMjQgMSAtNiAxOSAxNiAxNiAxOSAxNQo0NiAtN3ogbS0xMTkgLTc0IGw2MiAyMiAtMzEgLTQ0IGMtMTYgLTI0IC0zMCAtNDggLTMwIC01MyAwIC00IDE3IC0yOCAzNyAtNTIKbDM3IC00MyAtMzIgNiBjLTE3IDQgLTQ1IDEwIC02MCAxMyAtMjUgNSAtMzEgMCAtNjIgLTQ4IC0xOSAtMjkgLTM1IC00NiAtMzYKLTM4IC0yIDggLTYgMzYgLTkgNjIgbC02IDQ3IC01NSAxMSBjLTYxIDEzIC02MiAxOCAtMSA0MSBsNDIgMTcgMyA2MCAzIDYwIDM5Ci00MSAzOCAtNDIgNjEgMjJ6IG0xMjU4IC03NiBjNjAgLTIzIDYwIC0yOCAwIC00MSAtNTAgLTEwIC01NSAtMTQgLTYxIC00MiAtMwotMTcgLTYgLTQzIC02IC01OSAwIC00MSAtMTEgLTM2IC00NSAyMiAtMjcgNDUgLTMzIDUwIC01NyA0NSAtMTYgLTMgLTQzIC05Ci02MCAtMTIgbC0zMyAtNyAyNiAyOSBjNTQgNjIgNTQgNTkgMjEgMTEzIGwtMzEgNDkgNjAgLTIxIDYwIC0yMiAzNyA0MCAzNyA0MAo1IC01OSBjNSAtNTggNSAtNTkgNDcgLTc1eiBtLTExNjcgLTggYzAgLTUgLTUgLTE1IC0xMCAtMjMgLTkgLTEzIC0xMSAtMTMKLTE5IDAgLTUgOCAtNyAxOCAtNSAyMiA2IDEwIDM0IDExIDM0IDF6Ii8+CjxwYXRoIGQ9Ik03MjcgMTA1MiBjLTIwIC0yMiAtMjIgLTUzIC01IC03MCAyMCAtMjAgNjUgLTE0IDgyIDEwIDIwIDI5IDIwIDM0Ci00IDU4IC0yNSAyNSAtNTIgMjYgLTczIDJ6Ii8+CjxwYXRoIGQ9Ik0xMTc3IDEwNTIgYy0yMCAtMjIgLTIyIC01MyAtNSAtNzAgMjAgLTIwIDY1IC0xNCA4MiAxMCAyMCAyOSAyMCAzNAotNCA1OCAtMjUgMjUgLTUyIDI2IC03MyAyeiIvPgo8cGF0aCBkPSJNMzg4IDUzMyBjLTExIC0xMyAtMTMgLTI1IC03IC00MCAxMyAtMzYgNjkgLTI3IDY5IDExIDAgNDMgLTM1IDU5Ci02MiAyOXogbTM3IC0zMyBjLTUgLTggLTExIC04IC0xNyAtMiAtNiA2IC03IDE2IC0zIDIyIDUgOCAxMSA4IDE3IDIgNiAtNiA3Ci0xNiAzIC0yMnoiLz4KPHBhdGggZD0iTTE2MDYgNTM3IGMtMjUgLTE4IC02IC02NyAyNyAtNjcgMzQgMCA1MiA0MiAyOCA2NCAtMjEgMTkgLTMyIDIwCi01NSAzeiBtMzYgLTM5IGMtMTUgLTE1IC0yNiAtNCAtMTggMTggNSAxMyA5IDE1IDE4IDYgOSAtOSA5IC0xNSAwIC0yNHoiLz4KPC9nPgo8L3N2Zz4K";

        if($adsensei_mode == 'new'){

            $adsensei_parent_page = add_menu_page('Adsensei Settings', $label, $adsensei_permissions, 'adsensei-settings', 'adsensei_options_page_new', 'data:image/svg+xml;base64,' . $wpadsensei_logo);

            $adsensei_settings_page = add_submenu_page('adsensei-settings', __('Ads', 'adsenseib30'), 'Ads', $adsensei_permissions, 'adsensei-settings', 'adsensei_options_page_new');

            $adsensei_settings_page = add_submenu_page('adsensei-settings', __('Settings', 'adsenseib30'), 'Settings', $adsensei_permissions, 'adsensei-settings&path=settings', 'adsensei_options_page_new');


            //agrego pagina migrate
            $adsensei_settings_page = add_submenu_page('adsensei-settings', __('Migrate', 'adsenseib30'), 'Migrate old version', $adsensei_permissions, 'migrate_content', 'migrate_content_settings_page');
            
            if( isset($adsensei_options['reports_settings']) && $adsensei_options['reports_settings'] == 1 )
            $adsensei_settings_page = add_submenu_page('adsensei-settings', __('Reports', 'adsenseib30'), 'Reports', $adsensei_permissions, 'adsensei-settings&path=reports', 'adsensei_options_page_new');

        }else{
            $adsensei_parent_page = add_menu_page('Adsensei Settings', $label, 'manage_options', 'adsensei-settings', 'adsensei_options_page', 'data:image/svg+xml;base64,' . $wpadsensei_logo);

            $adsensei_settings_page = add_submenu_page('adsensei-settings', __('Ad Settings', 'adsenseib30'), 'Ad Settings', 'manage_options', 'adsensei-settings', 'adsensei_options_page');

            add_submenu_page('adsensei-settings', __('Switch to New Interface', 'adsenseib30'), 'Switch to New Interface', 'manage_options', 'adsensei_switch_to_new', 'adsensei_version_switch');

        }
    }
}

add_action( 'admin_menu', 'adsensei_add_options_link', 10 );

/**
 *  Determines whether the current admin page is an ADSENSEI add-on page.
 *
 *  Only works after the `wp_loaded` hook, & most effective
 *  starting on `admin_menu` hook.
 *
 *  @since 1.4.9
 *  @return bool True if ADSENSEI admin page.
 */
function adsensei_is_addon_page() {
        $currentpage = isset($_GET['page']) ? $_GET['page'] : '';
	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		return false;
	}

	if ( 'adsensei-addons' == $currentpage ) {
		return true;
	}
}
