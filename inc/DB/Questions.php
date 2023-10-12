<?php

namespace Floating\Survey\DB;

/**
 * The DB Question class
 */
class Questions
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'floating_questions';
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
                title varchar(255) NOT NULL,
                type varchar(255) NOT NULL,
                multiple BOOLEAN NOT NULL DEFAULT FALSE,
                answers TEXT NOT NULL,
                item_order int(11) NOT NULL DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                FOREIGN KEY (survey_id) REFERENCES {$wpdb->prefix}floating_surveys(id)
            ) $this->charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
