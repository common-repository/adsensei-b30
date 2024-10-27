<?php

/**
 * Main AdsenseiB30_10 Class
 *
 * @since 1.0.0
 */
class AdsenseiB30_10
{
    /** Singleton ************************************************************ */

    /**
     * @var AdsenseiB30_10 The one and only AdsenseiB30_10
     * @since 1.0
     */
    private static $instance;

    /**
     * ADSENSEI HTML Element Helper Object
     *
     * @var object
     * @since 2.0.0
     */
    public $html;

    /* ADSENSEI LOGGER Class
        *
        */
    public $logger;

    /**
     * Public vi class
     */
    public $vi;

    public function __construct()
    {
    }

    /**
     * Main AdsenseiB30_10 Instance
     *
     * Insures that only one instance of AdsenseiB30_10 exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0
     * @static
     * @static var array $instance
     * @uses AdsenseiB30_10::setup_constants() Setup the constants needed
     * @uses AdsenseiB30_10::includes() Include the required files
     * @uses AdsenseiB30_10::load_textdomain() load the language files
     * @see ADSENSEI()
     * @return The one true AdsenseiB30_10
     */
    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof AdsenseiB30_10)) {
            self::$instance = new AdsenseiB30_10;
            self::$instance->setup_constants();
            self::$instance->includes();
            self::$instance->load_textdomain();
            self::$instance->load_hooks();
            self::$instance->update_data();
            self::$instance->logger = new adsenseiLogger("quick_adsense_log_" . date("Y-m-d") . ".log", adsenseiLogger::INFO);
            self::$instance->html = new ADSENSEI_HTML_Elements();
            self::$instance->vi = new wpadsensei\vi();
            self::$instance->adsense = new wpadsensei\adsense(get_option('adsensei_settings'));
        }
        return self::$instance;
    }

    /**
     * Throw error on object clone
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     *
     * @since 1.0
     * @access protected
     * @return void
     */
    public function __clone()
    {
        // Cloning instances of the class is forbidden
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'ADSENSEI'), '1.0');
    }

    /**
     * Disable unserializing of the class
     *
     * @since 1.0
     * @access protected
     * @return void
     */
    public function __wakeup()
    {
        // Unserializing instances of the class is forbidden
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'ADSENSEI'), '1.0');
    }

    public function update_data()
    {

        $adsensei_settings = get_option('adsensei_settings');
        $adsensei_mode = get_option('adsensei-mode');
        if ($adsensei_mode && $adsensei_mode == 'new' && isset($adsensei_settings['ad_blocker_message']) && $adsensei_settings['ad_blocker_message'] && !isset($adsensei_settings['ad_blocker_support']) && !isset($adsensei_settings['notice_type'])) {
            $adsensei_settings['ad_blocker_support'] = true;
            $adsensei_settings['notice_type'] = 'ad_blocker_message';
            // update_option( 'adsensei_settings', $adsensei_settings );
        }
    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.0
     * @return void
     */
    private function setup_constants()
    {
        //global $wpdb;

        // Plugin Folder Path
        if (!defined('ADSENSEI_PLUGIN_DIR')) {
            define('ADSENSEI_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }

        // Plugin Folder URL
        if (!defined('ADSENSEI_PLUGIN_URL')) {
            define('ADSENSEI_PLUGIN_URL', plugin_dir_url(__FILE__));
        }

        // Plugin Root File
        if (!defined('ADSENSEI_PLUGIN_FILE')) {
            define('ADSENSEI_PLUGIN_FILE', __FILE__);
        }
    }

    /**
     * Include required files
     *
     * @access private
     * @since 1.0
     * @return void
     */
    private function includes()
    {
        global $adsensei_options, $adsensei_mode, $adsensei_permissions;

        $adsensei_mode = get_option('adsensei-mode');

        require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
        $adsensei_options = adsensei_get_settings();


        $permissions = "manage_options";
        if (isset($adsensei_options['RoleBasedAccess'])) {
            $user = wp_get_current_user();
            $rolename = $adsensei_options['RoleBasedAccess'];
            $rolename = array_map(function ($x) {
                return $x['value'];
            }, $rolename);
            if (in_array('administrator', $user->roles)) {
                $permissions = "manage_options";
            } elseif (in_array('editor', $user->roles) && in_array('editor', $rolename)) {
                $permissions = 'edit_pages';
            } elseif (in_array('author', $user->roles) && in_array('author', $rolename)) {
                $permissions = 'edit_posts';
            }
            if (class_exists('WPSEO_Options') && in_array('wpseo_manager', $user->roles) && in_array('wpseo_manager', $rolename)) {
                $permissions = 'edit_pages';
            }
        }
        $adsensei_permissions = $permissions;
        require_once ADSENSEI_PLUGIN_DIR . 'includes/post_types.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/user_roles.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/widgets.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/template-functions.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/logger.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/class-adsensei-html-elements.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/shortcodes.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/api.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/render-ad-functions.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/scripts.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/automattic-amp-ad.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/helper-functions.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/conditions.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/analytics.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/frontend-checks.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/Cron/Cron.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/vendor/vi/conditions.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/vendor/vi/vi.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/vendor/vi/render.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/vendor/google/adsense.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/vendor/google/AutoAds.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/class-template.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/adsTxt.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/elementor/widget.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/amp-condition-display.php';
        require_once ADSENSEI_PLUGIN_DIR . 'includes/reports/common.php';
        if ((isset($adsensei_options['ad_performance_tracking']) && $adsensei_options['ad_performance_tracking']) || isset($adsensei_options['ad_logging']) && $adsensei_options['ad_logging']) {
            require_once ADSENSEI_PLUGIN_DIR . 'includes/reports/analytics.php';
        }
        if (function_exists('has_blocks')) {
            require_once ADSENSEI_PLUGIN_DIR . 'includes/gutenberg/src/init.php';
        }

        if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/add-ons.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/admin-actions.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/admin-footer.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/admin-pages.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/plugins.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/welcome.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/tools.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/meta-boxes.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/quicktags.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/admin-notices.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/Forms/Form.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/Autoloader.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/vendor/vi/views/Forms/adSettings.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/settings/advanced-settings.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/settings/auto-ads.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/meta-box.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/scripts.php';
            require_once ADSENSEI_PLUGIN_DIR . 'includes/admin/ajax.php';
            $this->registerNamespaces();
        }

        //Includes new files
        require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/setup.php';
        require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/rest-api.php';
        require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/common-functions.php';
        require_once ADSENSEI_PLUGIN_DIR . '/admin/includes/widget.php';
        require_once ADSENSEI_PLUGIN_DIR . '/includes/migrate_content.php';

        $this->start_auto_ads();
    }

    private function start_auto_ads()
    {
        $init = new \wpadsensei\vendor\google\AutoAds();
    }

    /**
     * Register used namespaces
     */
    private function registerNamespaces()
    {
        $autoloader = new wpadsensei\Autoloader();

        // Autoloader
        $autoloader->registerNamespaces(array(
            "wpadsensei" => array(
                ADSENSEI_PLUGIN_DIR,
                ADSENSEI_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR,
                ADSENSEI_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'Forms',
                ADSENSEI_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'Forms' . DIRECTORY_SEPARATOR . 'Elements',
                ADSENSEI_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'Forms' . DIRECTORY_SEPARATOR . 'Elements' . DIRECTORY_SEPARATOR . 'Interfaces',
                ADSENSEI_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'vi',
                ADSENSEI_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'vi' . DIRECTORY_SEPARATOR . 'views',
            )
        ));


        // Register namespaces
        $autoloader->register();
    }



    public function load_hooks()
    {
        if (is_admin() && adsensei_is_plugins_page()) {
            add_filter('admin_footer', 'adsensei_add_deactivation_feedback_modal');
        }
    }

    /**
     * Loads the plugin language files
     *
     * @access public
     * @since 1.0
     * @return void
     */
    public function load_textdomain()
    {
        // Set filter for plugin's languages directory
        $adsensei_lang_dir = dirname(plugin_basename(ADSENSEI_PLUGIN_FILE)) . '/languages/';
        $adsensei_lang_dir = apply_filters('adsensei_languages_directory', $adsensei_lang_dir);
        // Traditional WordPress plugin locale filter
        $locale = apply_filters('plugin_locale', get_locale(), 'adsenseib30');
        $mofile = sprintf('%1$s-%2$s.mo', 'adsenseib30', $locale);
        // Setup paths to current locale file
        $mofile_local = $adsensei_lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/adsensei/' . $mofile;
        //echo $mofile_local;
        if (file_exists($mofile_global)) {
            // Look in global /wp-content/languages/adsensei folder
            load_textdomain('adsenseib30', $mofile_global);
        } elseif (file_exists($mofile_local)) {
            // Look in local /wp-content/plugins/adsenseib30/languages/ folder
            load_textdomain('adsenseib30', $mofile_local);
        } else {
            // Load the default language files
            load_plugin_textdomain('adsenseib30', false, $adsensei_lang_dir);
        }
    }

    /*
        * Activation function fires when the plugin is activated.
        * Checks first if multisite is enabled
        * @since 1.0.0
        *
        */

    public static function activation($networkwide)
    {
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite()) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ($networkwide) {
                $old_blog = $wpdb->blogid;
                // Get all blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
                    AdsenseiB30_10::during_activation();
                }
                switch_to_blog($old_blog);
                return;
            }
        }
        AdsenseiB30_10::during_activation();
    }

    /**
     * This function is fired from the activation method.
     *
     * @since 2.1.1
     * @access public
     *
     * @return void
     */
    public static function during_activation()
    {

        // Add cron event
        require_once plugin_dir_path(__FILE__) . 'includes/Cron/Cron.php';
        $cron = new adsenseiCron();
        $cron->schedule_event();

        // Create vi api endpints and settings
        self::instance()->vi->setSettings();

        // Add Upgraded From Option
        $current_version = get_option('adsensei_version');
        if ($current_version) {
            update_option('adsensei_version_upgraded_from', $current_version);
        }
        // First time installation
        // Get all settings and update them only if they are empty
        $adsensei_options = get_option('adsensei_settings');
        if (!$adsensei_options) {
            $adsensei_options['post_types'] = array('post', 'page');
            $adsensei_options['visibility']['AppHome'] = "1";
            $adsensei_options['visibility']['AppCate'] = "1";
            $adsensei_options['visibility']['AppArch'] = "1";
            $adsensei_options['visibility']['AppTags'] = "1";
            $adsensei_options['quicktags']['QckTags'] = "1";
            add_option('adsensei-mode', 'new');
            update_option('adsensei_settings', $adsensei_options);
        }

        // Update the current version
        //update_option( 'adsensei_version', ADSENSEI_VERSION );
        // Add plugin installation date and variable for rating div
        add_option('adsensei_install_date', date('Y-m-d h:i:s'));
        add_option('adsensei_rating_div', 'no');
        add_option('adsensei_show_theme_notice', 'yes');

        // Add the transient to redirect (not for multisites)
        set_transient('adsensei_activation_redirect', true, 3600);
    }

    /**
     * Get all wp adsensei settings
     * @return array
     */
    private function startAdsense()
    {
        new wpadsensei\adsense(get_option('adsensei_settings'));
    }
}
