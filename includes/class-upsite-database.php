<?php
/**
 * Database operations class
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Upsite_Database
 */
class Upsite_Database {
    
    /**
     * Table name
     *
     * @var string
     */
    private static $table_name = 'upsite_opening_hours';
    
    /**
     * Get table name with prefix
     *
     * @return string
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }
    
    /**
     * Create table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            date date NOT NULL,
            opening_time time NOT NULL,
            closing_time time NOT NULL,
            special_note varchar(255) DEFAULT '' NOT NULL,
            is_enabled tinyint(1) DEFAULT 1 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY date (date),
            KEY is_enabled (is_enabled),
            KEY date_enabled (date, is_enabled)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Store version
        update_option('upsite_hours_db_version', '1.0.0');
    }
    
    /**
     * Ensure table exists
     */
    public static function ensure_table_exists() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            self::create_table();
        }
    }
    
    /**
     * Insert or update opening hours for a date
     *
     * @param string $date Date in YYYY-MM-DD format
     * @param string $opening_time Time in HH:MM format
     * @param string $closing_time Time in HH:MM format
     * @param string $special_note Optional special note
     * @param bool $is_enabled Whether the date is enabled
     * @return int|false The number of rows affected, or false on error
     */
    public static function save_date($date, $opening_time, $closing_time, $special_note = '', $is_enabled = true) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        // Check if date already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE date = %s",
            $date
        ));
        
        $data = array(
            'date' => $date,
            'opening_time' => $opening_time,
            'closing_time' => $closing_time,
            'special_note' => $special_note,
            'is_enabled' => $is_enabled ? 1 : 0,
        );
        
        $format = array('%s', '%s', '%s', '%s', '%d');
        
        if ($existing) {
            // Update existing record
            return $wpdb->update(
                $table_name,
                $data,
                array('date' => $date),
                $format,
                array('%s')
            );
        } else {
            // Insert new record
            return $wpdb->insert($table_name, $data, $format);
        }
    }
    
    /**
     * Get opening hours for a specific date
     *
     * @param string $date Date in YYYY-MM-DD format
     * @return object|null
     */
    public static function get_date($date) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE date = %s",
            $date
        ));
    }
    
    /**
     * Get all opening hours
     *
     * @param array $args Optional query arguments
     * @return array
     */
    public static function get_dates($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'year' => null,
            'month' => null,
            'enabled_only' => true,
            'order' => 'ASC',
            'limit' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = self::get_table_name();
        $where = array();
        $where_values = array();
        
        if ($args['enabled_only']) {
            $where[] = 'is_enabled = 1';
        }
        
        if ($args['year']) {
            $where[] = 'YEAR(date) = %d';
            $where_values[] = $args['year'];
        }
        
        if ($args['month']) {
            $where[] = 'MONTH(date) = %d';
            $where_values[] = $args['month'];
        }
        
        $where_clause = '';
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        }
        
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
        
        $limit_clause = '';
        if ($args['limit']) {
            $limit_clause = $wpdb->prepare('LIMIT %d', $args['limit']);
        }
        
        $query = "SELECT * FROM $table_name $where_clause ORDER BY date $order $limit_clause";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Delete opening hours for a date
     *
     * @param string $date Date in YYYY-MM-DD format
     * @return int|false The number of rows affected, or false on error
     */
    public static function delete_date($date) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        return $wpdb->delete(
            $table_name,
            array('date' => $date),
            array('%s')
        );
    }
    
    /**
     * Get dates grouped by month
     *
     * @param array $args Optional query arguments
     * @return array Grouped by year-month
     */
    public static function get_dates_grouped_by_month($args = array()) {
        $dates = self::get_dates($args);
        $grouped = array();
        
        foreach ($dates as $date) {
            $year_month = date('Y-m', strtotime($date->date));
            if (!isset($grouped[$year_month])) {
                $grouped[$year_month] = array();
            }
            $grouped[$year_month][] = $date;
        }
        
        return $grouped;
    }
    
    /**
     * Get count of enabled dates
     *
     * @return int
     */
    public static function get_enabled_count() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE is_enabled = 1"
        );
    }
}


