<?php
/**
 * REST API endpoints class
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Upsite_REST_API
 */
class Upsite_REST_API {
    
    /**
     * API namespace
     *
     * @var string
     */
    private static $namespace = 'upsite-hours/v1';
    
    /**
     * Initialize REST API
     */
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public static function register_routes() {
        // Get all dates
        register_rest_route(self::$namespace, '/dates', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_dates'),
            'permission_callback' => '__return_true',
            'args' => array(
                'year' => array(
                    'type' => 'integer',
                    'required' => false,
                ),
                'month' => array(
                    'type' => 'integer',
                    'required' => false,
                    'minimum' => 1,
                    'maximum' => 12,
                ),
                'enabled_only' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
                'limit' => array(
                    'type' => 'integer',
                    'required' => false,
                ),
            ),
        ));
        
        // Get specific date
        register_rest_route(self::$namespace, '/dates/(?P<date>[\d-]+)', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_date'),
            'permission_callback' => '__return_true',
            'args' => array(
                'date' => array(
                    'type' => 'string',
                    'required' => true,
                    'validate_callback' => array(__CLASS__, 'validate_date'),
                ),
            ),
        ));
        
        // Create or update date
        register_rest_route(self::$namespace, '/dates', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'save_date'),
            'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            'args' => array(
                'date' => array(
                    'type' => 'string',
                    'required' => true,
                    'validate_callback' => array(__CLASS__, 'validate_date'),
                ),
                'opening_time' => array(
                    'type' => 'string',
                    'required' => true,
                    'validate_callback' => array(__CLASS__, 'validate_time'),
                ),
                'closing_time' => array(
                    'type' => 'string',
                    'required' => true,
                    'validate_callback' => array(__CLASS__, 'validate_time'),
                ),
                'special_note' => array(
                    'type' => 'string',
                    'required' => false,
                    'default' => '',
                ),
                'is_enabled' => array(
                    'type' => 'boolean',
                    'required' => false,
                    'default' => true,
                ),
            ),
        ));
        
        // Delete date
        register_rest_route(self::$namespace, '/dates/(?P<date>[\d-]+)', array(
            'methods' => 'DELETE',
            'callback' => array(__CLASS__, 'delete_date'),
            'permission_callback' => array(__CLASS__, 'check_admin_permission'),
            'args' => array(
                'date' => array(
                    'type' => 'string',
                    'required' => true,
                    'validate_callback' => array(__CLASS__, 'validate_date'),
                ),
            ),
        ));
        
        // Get settings (public)
        register_rest_route(self::$namespace, '/settings', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_settings'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Check admin permission
     *
     * @return bool
     */
    public static function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Validate date format
     *
     * @param string $date Date string
     * @return bool
     */
    public static function validate_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Validate time format
     *
     * @param string $time Time string
     * @return bool
     */
    public static function validate_time($time) {
        return preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }
    
    /**
     * Get all dates
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function get_dates($request) {
        $args = array(
            'year' => $request->get_param('year'),
            'month' => $request->get_param('month'),
            'enabled_only' => $request->get_param('enabled_only'),
            'limit' => $request->get_param('limit'),
        );
        
        $dates = Upsite_Database::get_dates($args);
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $dates,
        ), 200);
    }
    
    /**
     * Get specific date
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function get_date($request) {
        $date = $request->get_param('date');
        $result = Upsite_Database::get_date($date);
        
        if ($result) {
            return new WP_REST_Response(array(
                'success' => true,
                'data' => $result,
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Date not found', 'upsite-opening-hours'),
            ), 404);
        }
    }
    
    /**
     * Save date
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function save_date($request) {
        $date = $request->get_param('date');
        $opening_time = $request->get_param('opening_time');
        $closing_time = $request->get_param('closing_time');
        $special_note = $request->get_param('special_note');
        $is_enabled = $request->get_param('is_enabled');
        
        // Validate that closing time is after opening time
        if (strtotime($closing_time) <= strtotime($opening_time)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Closing time must be after opening time', 'upsite-opening-hours'),
            ), 400);
        }
        
        $result = Upsite_Database::save_date($date, $opening_time, $closing_time, $special_note, $is_enabled);
        
        if ($result !== false) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Date saved successfully', 'upsite-opening-hours'),
                'data' => Upsite_Database::get_date($date),
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Failed to save date', 'upsite-opening-hours'),
            ), 500);
        }
    }
    
    /**
     * Delete date
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function delete_date($request) {
        $date = $request->get_param('date');
        $result = Upsite_Database::delete_date($date);
        
        if ($result !== false && $result > 0) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => __('Date deleted successfully', 'upsite-opening-hours'),
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => __('Failed to delete date or date not found', 'upsite-opening-hours'),
            ), 404);
        }
    }
    
    /**
     * Get settings
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function get_settings($request) {
        $settings = array(
            'colors' => array(
                'enabled_bg' => get_option('upsite_hours_enabled_bg_color', '#4CAF50'),
                'disabled_bg' => get_option('upsite_hours_disabled_bg_color', '#f5f5f5'),
                'text' => get_option('upsite_hours_text_color', '#333333'),
                'special_highlight' => get_option('upsite_hours_special_highlight_color', '#FF9800'),
                'primary_accent' => get_option('upsite_hours_primary_accent_color', '#2196F3'),
            ),
            'defaults' => array(
                'opening_time' => get_option('upsite_hours_default_opening_time', '10:00'),
                'closing_time' => get_option('upsite_hours_default_closing_time', '18:00'),
            ),
        );
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $settings,
        ), 200);
    }
}


