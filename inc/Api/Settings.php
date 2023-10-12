<?php

namespace Floating\Survey\Api;

use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;

/**
 * The Settings class
 */
class Settings extends WP_REST_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->namespace = 'floating-survey/v1';
        $this->rest_base = 'settings';
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_settings'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'add_settings'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update_settings'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'delete_key'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/styles',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_styles'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update_styles'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );
    }

    /**
     * Check permissions for the read, write and update
     *
     * @param WP_REST_Request $request
     * @return boolean
     */
    public function permissions_check($request)
    {
        if (current_user_can('manage_options')) {
            return true;
        }
        return false;
    }

    /**
     * Get the settings
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_settings($request)
    {
        $version = get_option('floating-survey-version');
        $type = get_option('floating-survey-type');
        $name = get_option('floating-survey-name');

        $data = [
            'version' => sanitize_text_field($version),
            'type' => sanitize_text_field($type),
            'name' => sanitize_text_field($name)
        ];

        return new WP_REST_Response($data, 200);
    }

    /**
     * Add the settings
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function add_settings($request)
    {
        $license = sanitize_text_field($request->get_param('license'));
        $token = sanitize_text_field($request->get_param('token'));

        update_option('floating-survey-license', $license);
        update_option('floating-survey-license-date', date('Y-m-d H:i:s'));
        update_option('floating-survey-license-type', 'paid');
        update_option('floating-survey-license-deactivation_token', $token);

        $data = [
            'success' => true,
            'message' => 'Activated successfully',
            'license' => $license,
            'license_type' => 'paid',
            'license_token' => $token,
        ];

        return new WP_REST_Response($data, 200);
    }

    /**
     * Update the settings
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_settings($request)
    {
        // Sanitize and escape the input values
        $data = $request->get_params();
        $sanitized_data = array_map('sanitize_text_field', $data);
        $escaped_data = array_map('esc_html', $sanitized_data);

        // Update the settings using the sanitized and escaped data
        // ...

        $response_data = [
            'success' => true,
            'message' => 'Settings updated successfully',
        ];

        return new WP_REST_Response($response_data, 200);
    }

    /**
     * Delete the key and deactivate license
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_key($request)
    {
        update_option('floating-survey-license', '');
        update_option('floating-survey-license-type', 'free');
        update_option('floating-survey-license-deactivation_token', '');

        $data = [
            'success' => true,
            'message' => 'Deactivated successfully',
        ];

        return new WP_REST_Response($data, 200);
    }

    /**
     * Update styles
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_styles($request)
    {
        $alignment = sanitize_text_field($request->get_param('align'));
        $color = sanitize_text_field($request->get_param('color'));
        $background_color = sanitize_text_field($request->get_param('background_color'));
        $font_size = sanitize_text_field($request->get_param('font_size'));
        $menu_font_size = sanitize_text_field($request->get_param('menu_font_size'));
        $menu_color = sanitize_text_field($request->get_param('menu_color'));
        $menu_background_color = sanitize_text_field($request->get_param('menu_background_color'));
        $button_text = sanitize_text_field($request->get_param('button_text'));
        $close_button_text = sanitize_text_field($request->get_param('close_button_text'));
        $display_button = sanitize_text_field($request->get_param('display_button'));

        $update_result = true;
        $update_result = update_option('floating-survey-button-alignment', $alignment) && $update_result;
        $update_result = update_option('floating-survey-button-color', $color) && $update_result;
        $update_result = update_option('floating-survey-button-background-color', $background_color) && $update_result;
        $update_result = update_option('floating-survey-button-font-size', $font_size) && $update_result;
        $update_result = update_option('floating-survey-menu-font-size', $menu_font_size) && $update_result;
        $update_result = update_option('floating-survey-menu-color', $menu_color) && $update_result;
        $update_result = update_option('floating-survey-menu-background-color', $menu_background_color) && $update_result;
        $update_result = update_option('floating-survey-button-text', $button_text) && $update_result;
        $update_result = update_option('floating-survey-close-button-text', $close_button_text) && $update_result;
        $update_result = update_option('floating-survey-display-button', $display_button) && $update_result;

        if ($update_result) {
            return rest_ensure_response([
                'success' => true,
                'message' => 'Updated successfully'
            ]);
        } else {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Update failed'
            ]);
        }
    }
}
