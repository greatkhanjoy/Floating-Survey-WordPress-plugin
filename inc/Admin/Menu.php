<?php

namespace Floating\Survey\Admin;

/**
 * The Menu handler class
 */
class Menu
{
    /**
     * class constructor
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function admin_menu()
    {
        $capability = 'manage_options';
        $hook = add_menu_page(
            __('Floating Survey', 'floating-survey'),
            __('Floating Survey', 'floating-survey'),
            $capability,
            'floating-survey',
            [$this, 'plugin_page'],
            'dashicons-clipboard'
        );

        add_submenu_page(
            'floating-survey',
            __('Surveys', 'floating-survey'),
            __('Surveys', 'floating-survey'),
            $capability,
            'floating-survey',
            [$this, 'plugin_page']
        );

        add_submenu_page(
            'floating-survey',
            __('Results', 'floating-survey'),
            __('Results', 'floating-survey'),
            $capability,
            'floating-survey-results',
            [$this, 'plugin_page_settings']
        );

        add_submenu_page(
            'floating-survey',
            __('Settings', 'floating-survey'),
            __('Settings', 'floating-survey'),
            $capability,
            'floating-survey-settings',
            [$this, 'plugin_page_settings']
        );

        add_submenu_page(
            'floating-survey',
            __('License', 'floating-survey'),
            __('License', 'floating-survey'),
            $capability,
            'floating-survey-license',
            [$this, 'plugin_page_settings']
        );

        add_action('admin_head-' . $hook, [$this, 'enqueue_assets']);
    }

    /**
     * Enqueue admin assets
     *
     * @return void
     */
    public function enqueue_assets()
    {
        wp_enqueue_script('floating-survey-admin-script');
        wp_enqueue_style('floating-survey-admin-style');
        wp_localize_script('floating-survey-admin-script', 'floatingSurvey', array(
            'api_url' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    /**
     * Plugin page
     *
     * @return void
     */
    public function plugin_page()
    {
        $nonce = wp_create_nonce('wp_rest');
        $license = get_option('floating-survey-license');
        $license_type = get_option('floating-survey-license-type');
        $license_token = get_option('floating-survey-license-deactivation_token');
        $version = get_option('floating-survey-version');
        $name = get_option('floating-survey-name');
        global $wpdb;
        $table_name = $wpdb->prefix . 'floating_surveys';
        $query = "SELECT * FROM $table_name" . ($license_type == 'free' ? " LIMIT 1" : '');
        $surveys = $wpdb->get_results($query);

        if ($surveys) {
            foreach ($surveys as $survey) {
                $form_fields = json_decode($survey->form_fields, true);
                $email_template = wp_json_encode($survey->email_template, true);
                $survey->form_fields = $form_fields;
                $survey->email_template = $email_template;
            }
        }

        $settings = [
            'nonce' => $nonce,
            'version' => $version,
            'license' => $license,
            'license_type' => $license_type,
            'license_token' => $license_token,
            'name' => $name,
            'surveys' => $surveys,
            'button_styles' => array(
                'align' => get_option('floating-survey-button-alignment'),
                'color' => get_option('floating-survey-button-color'),
                'background_color' => get_option('floating-survey-button-background-color'),
                'font_size' => get_option('floating-survey-button-font-size')
            ),
            'menu_styles' => array(
                'color' => get_option('floating-survey-menu-color'),
                'background_color' => get_option('floating-survey-menu-background-color'),
                'hover_background_color' => get_option('floating-survey-menu-hover-background-color'),
                'font_size' => get_option('floating-survey-menu-font-size')
            ),
            'button_text' => get_option('floating-survey-button-text'),
            'close_button_text' => get_option('floating-survey-close-button-text'),
            'display_button' => get_option('floating-survey-display-button')
        ];

        echo '<div class="wrap" id="floating-survey-render">
            <pre id="floating-survey-settings-data" style="display: none;">' . wp_json_encode($settings) . '</pre>
        </div>';
    }

    /**
     * Plugin page settings
     *
     * @return void
     */
    public function plugin_page_settings()
    {
        echo 'Settings page';
    }
}
