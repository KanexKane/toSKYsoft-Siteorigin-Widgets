<?php

/**
 * Plugin Name: toSKYsoft SiteOrigin Widgets
 * Plugin URI: https://www.toskysoft.com/
 * Description: A collection of premium quality widgets for use in any widgetized area or in SiteOrigin page builder. SiteOrigin Widgets Bundle is required.
 * Author: toSKYsoft
 * Author URI: https://www.toskysoft.com/
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Version: 1.0.0
 * Text Domain: tss-so-widgets
 * Domain Path: languages
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if (!class_exists('Toskysoft_Siteorigin_Widgets')) :

class Toskysoft_Siteorigin_Widgets
{
    private static $instance;

    public static function instance()
    {
        if (!isset(self::$instance) && !(self::$instance instanceof Toskysoft_Siteorigin_Widgets)) 
        {
            self::$instance = new Toskysoft_Siteorigin_Widgets;
            self::$instance->setup_constants();

            add_action('plugins_loaded', array(self::$instance, 'load_plugin_textdomain'));

            self::$instance->includes();

            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Throw error on object clone
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     */
    public function __clone()
    {
        // Cloning instances of the class is forbidden
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'livemesh-so-widgets'), '1.7.3');
    }

    /**
     * Disable unserializing of the class
     *
     */
    public function __wakeup()
    {
            // Unserializing instances of the class is forbidden
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'livemesh-so-widgets'), '1.7.3');
    }

    /**
     * Setup plugin constants
     *
     */
    private function setup_constants()
    {

        // Plugin version
        if (!defined('TSS_SOW_VERSION')) 
        {
            define('TSS_SOW_VERSION', '1.0.0');
        }

            // Plugin Folder Path
        if (!defined('TSS_SOW_PLUGIN_DIR')) 
        {
            define('TSS_SOW_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }

            // Plugin Folder URL
        if (!defined('TSS_SOW_PLUGIN_URL')) 
        {
            define('TSS_SOW_PLUGIN_URL', plugin_dir_url(__FILE__));
        }

            // Plugin Root File
        if (!defined('TSS_SOW_PLUGIN_FILE')) 
        {
            define('TSS_SOW_PLUGIN_FILE', __FILE__);
        }

            // Plugin Help Page URL
        // if (!defined('TSS_SOW_PLUGIN_HELP_URL')) 
        // {
        //     define('TSS_SOW_PLUGIN_HELP_URL', admin_url() . 'admin.php?page=livemesh_so_widgets_documentation');
        // }

        $this->setup_debug_constants();
    }

    private function setup_debug_constants()
    {

        $enable_debug = false;

        $settings = get_option('tss_sow_settings');

        if ($settings && isset($settings['tss_sow_enable_debug']) && $settings['tss_sow_enable_debug'] == "true")
        {
            $enable_debug = true;
        }

            // Enable script debugging
        if (!defined('TSS_SOW_SCRIPT_DEBUG')) 
        {
            define('TSS_SOW_SCRIPT_DEBUG', $enable_debug);
        }

            // Minified JS file name suffix
        if (!defined('TSS_SOW_JS_SUFFIX')) 
        {
            if ($enable_debug)
            {
                define('TSS_SOW_JS_SUFFIX', '');
            }
            else
            {
                define('TSS_SOW_JS_SUFFIX', '.min');
            }
        }
    }

    /**
     * Include required files
     *
     */
    private function includes()
    {
        add_filter('siteorigin_widgets_widget_folders', function( $folders ) {
            $folders[] = TSS_SOW_PLUGIN_DIR . 'widgets/';
            return $folders;
        });

        // if (is_admin()) {
        //     require_once LSOW_PLUGIN_DIR . 'admin/admin-init.php';
        // }

    }

    

    /**
     * Load Plugin Text Domain
     *
     * Looks for the plugin translation files in certain directories and loads
     * them to allow the plugin to be localised
     */
    public function load_plugin_textdomain()
    {

        $lang_dir = apply_filters('tss_sow_so_widgets_lang_dir', trailingslashit(TSS_SOW_PLUGIN_DIR . 'languages'));

            // Traditional WordPress plugin locale filter
        $locale = apply_filters('plugin_locale', get_locale(), 'tss-so-widgets');
        $mofile = sprintf('%1$s-%2$s.mo', 'tss-so-widgets', $locale);

            // Setup paths to current locale file
        $mofile_local = $lang_dir . $mofile;

        if (file_exists($mofile_local)) 
        {
                // Look in the /wp-content/plugins/livemesh-so-widgets/languages/ folder
            load_textdomain('tss-so-widgets', $mofile_local);
        } 
        else 
        {
                // Load the default language files
            load_plugin_textdomain('tss-so-widgets', false, $lang_dir);
        }

        return false;
    }

    /**
     * Setup the default hooks and actions
     */
    private function hooks()
    {
        // add_action('wp_enqueue_scripts', array($this, 'load_frontend_scripts'), 10);

        // add_action('wp_enqueue_scripts', array($this, 'localize_scripts'), 999999);
    }

    /**
     * Load Frontend Scripts/Styles
     *
     */
    public function load_frontend_scripts()
    {

            // Use minified libraries if LSOW_SCRIPT_DEBUG is turned off
        $suffix = (defined('LSOW_SCRIPT_DEBUG') && LSOW_SCRIPT_DEBUG) ? '' : '.min';

        wp_register_style('lsow-frontend-styles', LSOW_PLUGIN_URL . 'assets/css/lsow-frontend.css', array(), LSOW_VERSION);
        wp_enqueue_style('lsow-frontend-styles');

        wp_register_style('lsow-icomoon-styles', LSOW_PLUGIN_URL . 'assets/css/icomoon.css', array(), LSOW_VERSION);
        wp_enqueue_style('lsow-icomoon-styles');

        wp_register_script('lsow-modernizr', LSOW_PLUGIN_URL . 'assets/js/modernizr-custom' . $suffix . '.js', array(), LSOW_VERSION, true);
        wp_enqueue_script('lsow-modernizr');

        wp_register_script('lsow-waypoints', LSOW_PLUGIN_URL . 'assets/js/jquery.waypoints' . $suffix . '.js', array('jquery'), LSOW_VERSION, true);
        wp_enqueue_script('lsow-waypoints');

        wp_register_script('lsow-frontend-scripts', LSOW_PLUGIN_URL . 'assets/js/lsow-frontend' . $suffix . '.js', array('jquery'), LSOW_VERSION, true);
        wp_enqueue_script('lsow-frontend-scripts');

    }

    public function localize_scripts()
    {

        $panels_mobile_width = 780; // default

        if (function_exists('siteorigin_panels_setting')) {

            $settings = siteorigin_panels_setting();

            $panels_mobile_width = $settings['mobile-width'];

        }

        $custom_css = lsow_get_option('lsow_custom_css', '');

        wp_localize_script('lsow-frontend-scripts', 'lsow_settings', array('mobile_width' => $panels_mobile_width, 'custom_css' => $custom_css));

    }

}

endif; // End if class_exists check


/**
 * The main function responsible for returning the one true Livemesh_SiteOrigin_Widgets
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $lsow = LSOW(); ?>
 */
function TSS_SOW()
{
    return Toskysoft_SiteOrigin_Widgets::instance();
}

// Get LSOW Running
TSS_SOW();