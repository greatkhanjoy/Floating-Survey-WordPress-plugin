<?php

namespace Floating\Survey;

/**
 * The frontend class
 */
class Frontend
{
    /**
     * class constructor
     */
    public function __construct()
    {
        new Frontend\Shortcode();
        add_action('wp_footer', [$this, 'render_frontend']);
    }

    /**
     * Render frontend
     *
     * @return void
     */
    public function render_frontend()
    {
        wp_enqueue_script('floating-survey-script');
        wp_enqueue_style('floating-survey-style');

        $license = get_option('floating-survey-type');
        $version = get_option('floating-survey-version');
        global $wpdb;
        $table_name = $wpdb->prefix . 'floating_surveys';
        $query = "SELECT * FROM $table_name" . ($license == 'free' ? " LIMIT 1" : '');
        $surveys = $wpdb->get_results($wpdb->prepare($query));

        if ($surveys) {
            foreach ($surveys as $survey) {
                $form_fields = json_decode($survey->form_fields, true);
                $email_template = wp_json_encode($survey->email_template, true);
                $survey->form_fields = $form_fields;
                $survey->email_template = $email_template;
            }
        }

        $settings = [
            'version' => $version,
            'license' => $license,
            'surveys' => $surveys,
            'button_styles' => [
                'align' => esc_attr(get_option('floating-survey-button-alignment')),
                'color' => esc_attr(get_option('floating-survey-button-color')),
                'background_color' => esc_attr(get_option('floating-survey-button-background-color')),
                'font_size' => esc_attr(get_option('floating-survey-button-font-size'))
            ],
            'menu_styles' => [
                'color' => esc_attr(get_option('floating-survey-menu-color')),
                'background_color' => esc_attr(get_option('floating-survey-menu-background-color')),
                'hover_background_color' => esc_attr(get_option('floating-survey-menu-hover-background-color')),
                'font_size' => esc_attr(get_option('floating-survey-menu-font-size'))
            ],
            'button_text' => esc_html(get_option('floating-survey-button-text')),
            'close_button_text' => esc_html(get_option('floating-survey-close-button-text')),
            'display_button' => get_option('floating-survey-display-button')
        ];

        echo '<div id="floating-survey-render">
            <pre id="floating-survey-settings-data" style="display: none;">' . wp_json_encode($settings) . '</pre>
        </div>';
    }
}
