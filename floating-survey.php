<?php

/**
 * Plugin Name: Floating Survey
 * Plugin URI: https://browter.com
 * Description: Survey Plugin for WordPress. Opens in a popup and floats.
 * Version: 1.0.0
 * Author: Greatkhanjoy (Imran Hosein Khan Joy)
 * Author URI: https://greatkhanjoy.browter.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: floating-survey
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main Plugin Class
 */
final class Floating_Survey
{
    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0.0';

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->define_constants();

        // Check permalink structure before activation
        if ($this->is_permalink_structure_valid()) {
            register_activation_hook(__FILE__, [$this, 'activate']);
            add_action('plugins_loaded', [$this, 'init_plugin']);
        } else {
            add_action('admin_init', [$this, 'deactivate']);
            add_action('admin_notices', [$this, 'plugin_activation_notice']);
            return;
        }

        add_action('admin_notices', [$this, 'plugin_activation_notice']);
    }

    /**
     * Initialize a singleton instance
     *
     * @return \Floating_Survey
     */
    public static function init()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define necessary constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('FLOATING_SURVEY_VERSION', self::version);
        define('FLOATING_SURVEY_FILE', __FILE__);
        define('FLOATING_SURVEY_PATH', __DIR__);
        define('FLOATING_SURVEY_URL', plugins_url('', FLOATING_SURVEY_FILE));
        define('FLOATING_SURVEY_ASSETS', FLOATING_SURVEY_URL . '/assets');
    }

    /**
     * Admin Notice
     */
    public function plugin_activation_notice()
    {
        $current_permalink_structure = get_option('permalink_structure');

        if ($current_permalink_structure === '/%postname%/') {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>Please update your permalink structure to "Post name" for optimal plugin functionality. You can change this under Settings > Permalinks.</p>';
        echo '</div>';
    }

    /**
     * Check if the permalink structure is valid
     *
     * @return bool
     */
    private function is_permalink_structure_valid()
    {
        $current_permalink_structure = get_option('permalink_structure');
        return $current_permalink_structure === '/%postname%/';
    }

    /**
     * Deactivate the plugin
     *
     * @return void
     */
    public function deactivate()
    {
        deactivate_plugins(plugin_basename(__FILE__));
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin()
    {
        new Floating\Survey\Assets();
        new Floating\Survey\Api();
        new Floating\Survey\Database();

        if (is_admin()) {
            new Floating\Survey\Admin();
        } else {
            new Floating\Survey\Frontend();
        }
    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate()
    {
        $installed = get_option('floating_survey_installed');

        if (!$installed) {
            update_option('floating_survey_installed', time());
        }

        update_option('floating_survey_version', FLOATING_SURVEY_VERSION);

        new Floating\Survey\Activation();
    }
}

/**
 * Initialize the plugin
 *
 * @return \Floating_Survey
 */
function floating_survey()
{
    return Floating_Survey::init();
}

// Kick-off the plugin
floating_survey();
