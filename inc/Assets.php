<?php

namespace Floating\Survey;

/**
 * The assets handler class
 */
class Assets
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function get_scripts()
    {
        return [
            'floating-survey-script' => [
                'src' => FLOATING_SURVEY_ASSETS . '/frontend.js',
                'version' => filemtime(FLOATING_SURVEY_PATH . '/assets/frontend.js'),
                'footer' => false,
                'deps' => ['wp-i18n', 'wp-element'],
            ],
            'floating-survey-admin-script' => [
                'src' => FLOATING_SURVEY_ASSETS . '/index.js',
                'version' => filemtime(FLOATING_SURVEY_PATH . '/assets/index.js'),
                'footer' => false,
                'deps' => ['wp-i18n', 'wp-plugins', 'wp-edit-post', 'wp-element'],
            ],
        ];
    }

    public function get_styles()
    {
        return [
            'floating-survey-style' => [
                'src' => FLOATING_SURVEY_ASSETS . '/frontend.css',
                'version' => filemtime(FLOATING_SURVEY_PATH . '/assets/frontend.css'),
                'footer' => false,
            ],
            'floating-survey-admin-style' => [
                'src' => FLOATING_SURVEY_ASSETS . '/index.css',
                'version' => filemtime(FLOATING_SURVEY_PATH . '/assets/index.css'),
                'footer' => false,
            ],
        ];
    }

    public function enqueue_assets()
    {
        $scripts = $this->get_scripts();
        $styles = $this->get_styles();

        foreach ($scripts as $handle => $script) {
            $deps = isset($script['deps']) ? $script['deps'] : false;
            wp_register_script($handle, $script['src'], $deps, $script['version']);
        }

        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;
            wp_register_style($handle, $style['src'], $deps, $style['version']);
        }
    }
}
