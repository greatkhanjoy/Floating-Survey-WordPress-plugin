<?php

namespace Floating\Survey\DB;

/**
 * The DBSurvey class
 */
class Survey
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'floating_surveys';
        $this->charset = $wpdb->get_charset_collate();
        $this->create_table();
    }

    /**
     * Create table
     *
     * @return void
     */
    public function create_table()
    {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                email_subject varchar(255) NOT NULL,
                form_fields text NOT NULL,
                email_template text NOT NULL,
                message text NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $this->charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
