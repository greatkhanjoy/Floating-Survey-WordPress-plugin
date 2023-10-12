<?php

namespace Floating\Survey\DB;

/**
 * The ResultDB class
 */
class ResultDB
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'floating_survey_results';
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
                survey_id int(11) NOT NULL,
                survey_name varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                score int(11) NOT NULL,
                points int(11) NOT NULL,
                personal_info text NOT NULL,
                questions text NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $this->charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
