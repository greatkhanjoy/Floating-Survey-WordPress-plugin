<?php

namespace Floating\Survey;

/**
 * The Activation class
 */
class Activation
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->add_options_table();
    }

    /**
     * Add fields to the options table
     *
     * @return void
     */
    public function add_options_table()
    {
        $license_type = get_option('floating-survey-license-type');
        if (!$license_type) {
            update_option('floating-survey-license-type', 'free');
        }

        $license = get_option('floating-survey-license');
        if (!$license) {
            update_option('floating-survey-license', '');
        }

        $license_token = get_option('floating-survey-license-deactivation_token');
        if (!$license_token) {
            update_option('floating-survey-license-deactivation_token', '');
        }

        $name = get_option('floating-survey-name');
        if (!$name) {
            update_option('floating-survey-name', 'Floating Survey');
        }

        // Styles
        update_option('floating-survey-button-alignment', 'right');
        update_option('floating-survey-button-color', '#ffffff');
        update_option('floating-survey-button-background-color', '#9393f7');
        update_option('floating-survey-button-font-size', '16');
        update_option('floating-survey-menu-font-size', '16');
        update_option('floating-survey-menu-color', '#ffffff');
        update_option('floating-survey-menu-background-color', '#9393f7');
        update_option('floating-survey-button-text', 'Survey');
        update_option('floating-survey-close-button-text', 'Close');
        update_option('floating-survey-display-button', 'yes');
    }
}
