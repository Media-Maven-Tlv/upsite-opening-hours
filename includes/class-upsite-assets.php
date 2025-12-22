<?php
/**
 * Assets management class
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Upsite_Assets
 */
class Upsite_Assets {
    
    /**
     * Initialize assets
     */
    public static function init() {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_assets'));
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public static function enqueue_admin_assets($hook) {
        // Only load on our settings page
        if ($hook !== 'settings_page_upsite-opening-hours') {
            return;
        }
        
        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue admin CSS
        wp_enqueue_style(
            'upsite-hours-admin',
            UPSITE_HOURS_PLUGIN_URL . 'assets/css/admin-styles.css',
            array('wp-color-picker'),
            UPSITE_HOURS_VERSION
        );
        
        // Enqueue admin JS
        wp_enqueue_script(
            'upsite-hours-admin',
            UPSITE_HOURS_PLUGIN_URL . 'assets/js/admin-calendar.js',
            array('jquery', 'wp-color-picker'),
            UPSITE_HOURS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('upsite-hours-admin', 'upsiteHoursAdmin', array(
            'apiUrl' => rest_url('upsite-hours/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'defaultOpeningTime' => get_option('upsite_hours_default_opening_time', '10:00'),
            'defaultClosingTime' => get_option('upsite_hours_default_closing_time', '18:00'),
            'colors' => array(
                'enabled' => get_option('upsite_hours_enabled_bg_color', '#4CAF50'),
                'disabled' => get_option('upsite_hours_disabled_bg_color', '#f5f5f5'),
                'text' => get_option('upsite_hours_text_color', '#333333'),
                'special' => get_option('upsite_hours_special_highlight_color', '#FF9800'),
                'accent' => get_option('upsite_hours_primary_accent_color', '#2196F3'),
            ),
            'i18n' => array(
                'confirmDelete' => __('Are you sure you want to delete this date?', 'upsite-opening-hours'),
                'errorSaving' => __('Error saving date. Please try again.', 'upsite-opening-hours'),
                'errorDeleting' => __('Error deleting date. Please try again.', 'upsite-opening-hours'),
                'errorLoading' => __('Error loading dates. Please refresh the page.', 'upsite-opening-hours'),
                'months' => array(
                    'ינואר',
                    'פברואר',
                    'מרץ',
                    'אפריל',
                    'מאי',
                    'יוני',
                    'יולי',
                    'אוגוסט',
                    'ספטמבר',
                    'אוקטובר',
                    'נובמבר',
                    'דצמבר',
                ),
                'days' => array(
                    'א\'',
                    'ב\'',
                    'ג\'',
                    'ד\'',
                    'ה\'',
                    'ו\'',
                    'ש\'',
                ),
            ),
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public static function enqueue_frontend_assets() {
        // Only enqueue if shortcode is present
        global $post;
        
        if (!is_a($post, 'WP_Post')) {
            return;
        }
        
        // Check if shortcode exists in content
        $has_shortcode = has_shortcode($post->post_content, 'upsite_hours_calendar') || 
                        has_shortcode($post->post_content, 'upsite_hours_list');
        
        if (!$has_shortcode) {
            return;
        }
        
        // Enqueue frontend CSS
        wp_enqueue_style(
            'upsite-hours-frontend',
            UPSITE_HOURS_PLUGIN_URL . 'assets/css/frontend-styles.css',
            array(),
            UPSITE_HOURS_VERSION
        );
        
        // Enqueue frontend JS
        wp_enqueue_script(
            'upsite-hours-frontend',
            UPSITE_HOURS_PLUGIN_URL . 'assets/js/frontend-calendar.js',
            array('jquery'),
            UPSITE_HOURS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('upsite-hours-frontend', 'upsiteHoursFrontend', array(
            'apiUrl' => rest_url('upsite-hours/v1'),
        ));
    }
}


