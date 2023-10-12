<?php

namespace Floating\Survey;

/**
 * The Api class
 */

class Api
{


    /**
     * class constructor
     */
    function __construct()
    {
        add_action('rest_api_init', [$this, 'register_api']);
    }

    /**
     * Register the api
     *
     * @return void
     */
    public function register_api()
    {
        $survey =  new Api\Survey();
        $survey->register_routes();

        $settings =  new Api\Settings();
        $settings->register_routes();

        $questions =  new Api\Questions();
        $questions->register_routes();

        $result =  new Api\Result();
        $result->register_routes();
    }
}
