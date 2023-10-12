<?php

namespace Floating\Survey\Api;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;
use WPDB;

/**
 * The Survey class
 */
class Survey extends WP_REST_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->namespace = 'floating-survey/v1';
        $this->rest_base = 'surveys';
        $this->wpdb = $wpdb;
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
                    'callback' => [$this, 'get_surveys'],
                    'permission_callback' => [$this, 'read_permissions_check'],
                    'args' => $this->get_collection_params()
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'add_new_survey'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update_survey'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'delete_survey'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/survey-result',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_survey_result'],
                    'permission_callback' => [$this, 'read_permissions_check']
                ],
            ]
        );
    }

    /**
     * Check permissions for the read methods.
     *
     * @param WP_REST_Request $request
     * @return boolean
     */
    public function read_permissions_check($request)
    {
        return true;
    }

    /**
     * Check permissions for the read, write, update and delete methods.
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
     * Get a collection of items
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_surveys($request)
    {
        $table_name = $this->wpdb->prefix . 'floating_surveys';
        $params = $request->get_params();

        if (isset($params['id'])) {
            $query = $this->wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $params['id']);
            $surveys = $this->wpdb->get_results($query);
            return new WP_REST_Response($surveys);
        } else {
            $query = "SELECT * FROM $table_name";
            $surveys = $this->wpdb->get_results($query);

            foreach ($surveys as $survey) {
                $totalCount = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->wpdb->prefix}floating_survey_results WHERE survey_id = $survey->id");
                $survey->submission = $totalCount;
            }

            return new WP_REST_Response($surveys);
        }
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_survey_result($request)
    {
        $survey_table = $this->wpdb->prefix . 'floating_surveys';
        $submission_table = $this->wpdb->prefix . 'floating_survey_results';
        $params = $request->get_params();
        $survey_id = $params['id'];

        $query = $this->wpdb->prepare("SELECT * FROM $survey_table WHERE id = %d", $survey_id);
        $survey = $this->wpdb->get_results($query);

        $submission_query = $this->wpdb->prepare("SELECT * FROM $submission_table WHERE survey_id = %d", $survey_id);
        $submissions = $this->wpdb->get_results($submission_query);

        $survey[0]->submissions = $submissions;
        $survey[0]->total_submissions = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->wpdb->prefix}floating_survey_results WHERE survey_id = $survey_id");

        return new WP_REST_Response($survey[0]);
    }

    /**
     * Add a new survey
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function add_new_survey($request)
    {
        $table_name = $this->wpdb->prefix . 'floating_surveys';
        $data = $request->get_params();

        if (isset($data['form_fields'])) {
            $data['form_fields'] = json_encode($data['form_fields']);
        }

        $this->wpdb->insert($table_name, $data);
        $id = $this->wpdb->insert_id;

        $query = $this->wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
        $survey = $this->wpdb->get_results($query);

        return new WP_REST_Response($survey);
    }

    /**
     * Update Survey
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_survey($request)
    {
        $table_name = $this->wpdb->prefix . 'floating_surveys';
        $data = $request->get_params();

        if (isset($data['form_fields'])) {
            $data['form_fields'] = json_encode($data['form_fields']);
        }

        $this->wpdb->update($table_name, $data, array('id' => $data['id']));

        $query = $this->wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $data['id']);
        $survey = $this->wpdb->get_results($query);

        if ($survey) {
            $response = new WP_REST_Response(array('message' => 'Survey updated successfully'));
            $response->set_status(200);
            return $response;
        } else {
            $response = new WP_REST_Response(array('message' => 'Survey not updated'));
            $response->set_status(400);
            return $response;
        }
    }

    /**
     * Delete Survey
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function delete_survey($request)
    {
        // Delete survey from floating_surveys table and questions from floating_questions table
        $table_name = $this->wpdb->prefix . 'floating_surveys';
        $question_table_name = $this->wpdb->prefix . 'floating_questions';
        $data = $request->get_params();

        $this->wpdb->delete($question_table_name, array('survey_id' => $data['id']));
        $delete = $this->wpdb->delete($table_name, array('id' => $data['id']));

        if ($delete) {
            $response = new WP_REST_Response(array('message' => 'Survey deleted successfully'));
            $response->set_status(200);
            return $response;
        } else {
            $response = new WP_REST_Response(array('message' => 'Survey not deleted'));
            $response->set_status(400);
            return $response;
        }
    }
}
