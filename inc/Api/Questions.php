<?php

namespace Floating\Survey\Api;

use WP_REST_Controller;
use WP_REST_Response;

/**
 * The Questions class
 */
class Questions extends WP_REST_Controller
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->namespace = 'floating-survey/v1';
        $this->rest_base = 'questions';
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
                    'callback' => [$this, 'get_questions'],
                    'permission_callback' => [$this, 'read_permissions_check'],
                    'args' => $this->get_collection_params(),
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'add_new_question'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update_item'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'delete_question'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
            ]
        );
    }

    /**
     * Check permissions for the read methods.
     *
     * @param \WP_REST_Request $request
     * @return boolean
     */
    public function read_permissions_check($request)
    {
        return true;
    }

    /**
     * Check permissions for the read, write, update and delete methods.
     *
     * @param \WP_REST_Request $request
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
     * Get a collection of surveys
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_questions($request)
    {
        global $wpdb;
        $params = $request->get_params();
        $table_name = $wpdb->prefix . 'floating_questions';

        if (isset($params['survey_id'])) {
            $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE survey_id = %d ORDER BY item_order ASC", $params['survey_id']);
            $results = $wpdb->get_results($sql);
            return rest_ensure_response($results);
        } elseif (isset($params['id'])) {
            $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $params['id']);
            $results = $wpdb->get_results($sql);
            return rest_ensure_response($results);
        } else {
            $response = new WP_REST_Response(['message' => 'Invalid Request!']);
            $response->set_status(400);
            return $response;
        }
    }

    /**
     * Add a new question
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function add_new_question($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'floating_questions';
        $params = $request->get_params();
        $insert = $wpdb->insert(
            $table_name,
            [
                'survey_id' => sanitize_text_field($params['survey_id']),
                'title' => sanitize_text_field($params['title']),
                'item_order' => sanitize_text_field($params['item_order']),
                'type' => sanitize_text_field($params['type']),
                'multiple' => sanitize_text_field($params['multiple']),
                'answers' => wp_json_encode($params['answers']),
            ]
        );
        if ($insert) {
            return rest_ensure_response('success');
        } else {
            return rest_ensure_response('failed', 500);
        }
    }

    /**
     * Update a question
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function update_item($request)
    {
        // Update question
        global $wpdb;
        $table_name = $wpdb->prefix . 'floating_questions';
        $params = $request->get_params();
        $update = $wpdb->update(
            $table_name,
            [
                'title' => sanitize_text_field($params['title']),
                'item_order' => sanitize_text_field($params['item_order']),
                'type' => sanitize_text_field($params['type']),
                'multiple' => sanitize_text_field($params['multiple']),
                'answers' => wp_json_encode($params['answers']),
            ],
            [
                'id' => sanitize_text_field($params['id']),
            ]
        );
    }

    /**
     * Delete a question
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function delete_question($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'floating_questions';
        $params = $request->get_params();
        $delete = $wpdb->delete(
            $table_name,
            [
                'id' => sanitize_text_field($params['id']),
            ]
        );
        if ($delete) {
            return rest_ensure_response('success');
        } else {
            $response = new WP_REST_Response(['message' => 'Unable to delete question']);
            $response->set_status(400);
            return $response;
        }
    }
}
