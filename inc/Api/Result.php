<?php

namespace Floating\Survey\Api;

use WP_REST_Controller;
use WP_REST_Response;

/**
 * The Result class
 */
class Result extends WP_REST_Controller
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->namespace = 'floating-survey/v1';
        $this->rest_base = 'result';
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
                    'callback' => [$this, 'get_results'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'add_new_result'],
                    'permission_callback' => [$this, 'write_permissions_check'],
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'delete_result'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>\d+)',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_single_result'],
                    'permission_callback' => [$this, 'permissions_check'],
                ],
            ]
        );
    }

    /**
     * Check permissions for the read
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function permissions_check($request)
    {
        if (current_user_can('manage_options')) {
            return true;
        }
        return false;
    }

    /**
     * Check permissions for the write
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function write_permissions_check($request)
    {
        return true;
    }

    /**
     * Get a collection of results
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_results($request)
    {
        global $wpdb;
        $data = $request->get_params();
        $limit = isset($data['limit']) ? intval($data['limit']) : 10;
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $offset = ($page - 1) * $limit;
        $table_name = $wpdb->prefix . 'floating_survey_results';
        $sql = $wpdb->prepare("SELECT * FROM $table_name" . (isset($data['sort']) ? " ORDER BY " . $data['sort'] : '') . (isset($data['order']) ? " " . $data['order'] : '') . " LIMIT %d OFFSET %d", $limit, $offset);
        $results = $wpdb->get_results($sql);
        $totalPosts = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        // Calculate meta data
        $totalPages = ceil($totalPosts / $limit);
        $nextPage = ($page < $totalPages) ? $page + 1 : null;
        $prevPage = ($page > 1) ? $page - 1 : null;
        $data = array(
            'results' => $results,
            'meta' => array(
                'pagination' => array(
                    'limit' => $limit,
                    'page'  => $page,
                    'pages' => $totalPages,
                    'total' => $totalPosts,
                    'links' => array(
                        'next' => $nextPage ? get_rest_url(null, 'floating-survey/v1' . '/result?limit=' . $limit . '&page=' . $nextPage) : null,
                        'prev' => $prevPage ? get_rest_url(null, 'floating-survey/v1' . '/result?limit=' . $limit . '&page=' . $prevPage) : null,
                    ),
                ),
            ),
        );
        return rest_ensure_response($data);
    }

    /**
     * Get a single result
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_single_result($request)
    {
        global $wpdb;
        $data = $request->get_params();
        $table_name = $wpdb->prefix . 'floating_survey_results';
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $data['id']);
        $result = $wpdb->get_results($sql);
        return rest_ensure_response($result);
    }

    /**
     * Add a new result
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function add_new_result($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'floating_survey_results';
        $data = $request->get_params();
        $insert = $wpdb->insert(
            $table_name,
            [
                'survey_id' => sanitize_text_field($data['survey_id']),
                'survey_name' => sanitize_text_field($data['survey_name']),
                'email' => sanitize_email($data['email']),
                'score' => $data['score'] === null ? 0 : intval($data['score']),
                'points' => sanitize_text_field($data['points']),
                'personal_info' => wp_json_encode($data['personal_info']),
                'questions' => wp_json_encode($data['questions']),
                'created_at' => current_time('mysql'),
            ]
        );
        if ($insert) {
            $response = new WP_REST_Response(['message' => 'Survey Submitted Successfully', 'mail' => $this->send_email($data)]);
            $response->set_status(200);
            return $response;
        } else {
            $response = new WP_REST_Response(['message' => 'Something went wrong', 'data' => $data, 'error' => $wpdb->last_error]);
            $response->set_status(400);
            return $response;
        }
    }

    /**
     * Delete a result
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_result($request)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'floating_survey_results';
        $data = $request->get_params();
        $ids = implode(',', array_map('intval', $data));
        $delete = $wpdb->query("DELETE FROM $table_name WHERE id IN ($ids)");
        if ($delete) {
            $response = new WP_REST_Response(['message' => 'Survey Deleted Successfully']);
            $response->set_status(200);
            return $response;
        } else {
            $response = new WP_REST_Response(['message' => 'Something went wrong']);
            $response->set_status(400);
            return $response;
        }
    }

    /**
     * Send email
     *
     * @param array $data Email data.
     */
    public function send_email($data)
    {
        $email = '';
        // Get the email from personal_info 
        foreach ($data['personal_info'] as $info) {
            if ($info['fieldId'] === 'email') {
                $email = $info['value'];
                break;
            }
        }
        $to = $email;
        $subject = $data['email_subject'];
        $body = $data['email_template'];
        $headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
        $headers .= 'From: ' . get_bloginfo('name') . ' <' . $data['email'] . '>' . "\r\n";
        $sent = wp_mail($to, $subject, $body, $headers);
    }
}
