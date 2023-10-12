<?php

namespace Floating\Survey\Frontend;

/**
 * The Shortcode handler class
 */

class Shortcode
{

    /**
     * Intialize the class
     */
    function __construct()
    {
        add_shortcode('floating-survey', [$this, 'render_shortcode']);
    }

    /**
     * Render shortcode
     *
     * @param array $atts
     * @param string $content
     * 
     * @return string
     */
    public function render_shortcode($atts, $content = '')
    {
        return 'Floating Survey';
    }
}
