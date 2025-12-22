<?php
/**
 * Plugin Name: Upsite Opening Hours
 * Plugin URI: https://upsiteapp.co.il
 * Description: Manage and display opening hours with an interactive calendar interface for Superland and Lunapark websites
 * Version: 1.3.0
 * Author: Upsite
 * Requires PHP: 7.4
 * Requires at least: 5.0
 * Text Domain: upsite-opening-hours
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('UPSITE_HOURS_VERSION', '1.3.0');
define('UPSITE_HOURS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UPSITE_HOURS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UPSITE_HOURS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load autoloader
require_once UPSITE_HOURS_PLUGIN_DIR . 'includes/autoloader.php';

// Load core classes
require_once UPSITE_HOURS_PLUGIN_DIR . 'includes/class-upsite-database.php';
require_once UPSITE_HOURS_PLUGIN_DIR . 'includes/class-upsite-admin.php';
require_once UPSITE_HOURS_PLUGIN_DIR . 'includes/class-upsite-rest-api.php';
require_once UPSITE_HOURS_PLUGIN_DIR . 'includes/class-upsite-shortcodes.php';
require_once UPSITE_HOURS_PLUGIN_DIR . 'includes/class-upsite-assets.php';

// GitHub Updater
if (is_admin()) {
    require_once UPSITE_HOURS_PLUGIN_DIR . 'updater.php';
    
    $config = array(
        'slug' => plugin_basename(__FILE__),
        'proper_folder_name' => 'upsite-opening-hours',
        'api_url' => 'https://api.github.com/repos/Media-Maven-Tlv/upsite-opening-hours',
        'raw_url' => 'https://raw.githubusercontent.com/Media-Maven-Tlv/upsite-opening-hours/main',
        'github_url' => 'https://github.com/Media-Maven-Tlv/upsite-opening-hours',
        'zip_url' => 'https://github.com/Media-Maven-Tlv/upsite-opening-hours/zipball/main',
        'sslverify' => true,
        'requires' => '5.0',
        'tested' => '6.4',
        'readme' => 'README.md',
        'access_token' => '', // Leave empty for public repos
    );
    
    if (class_exists('WP_GitHub_Updater')) {
        new WP_GitHub_Updater($config);
    }
}

/**
 * Main plugin class
 */
class Upsite_Opening_Hours {
    
    /**
     * Instance of this class
     *
     * @var Upsite_Opening_Hours|null
     */
    private static $instance = null;
    
    /**
     * Get instance
     *
     * @return Upsite_Opening_Hours
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Initialize components
        Upsite_Admin::init();
        Upsite_Assets::init();
        Upsite_Shortcodes::init();
        Upsite_REST_API::init();
        
        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Register deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize database on plugin load
        add_action('init', array($this, 'init_database'), 1);
    }
    
    /**
     * Initialize database
     */
    public function init_database() {
        // Ensure table exists
        Upsite_Database::ensure_table_exists();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database table
        Upsite_Database::create_table();
        
        // Set default color settings
        $this->set_default_settings();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag for admin notices
        set_transient('upsite_hours_activation_notice', true, 60);
    }
    
    /**
     * Set default settings
     */
    private function set_default_settings() {
        $defaults = array(
            'enabled_bg_color' => '#4CAF50',
            'disabled_bg_color' => '#f5f5f5',
            'text_color' => '#333333',
            'special_highlight_color' => '#FF9800',
            'primary_accent_color' => '#2196F3',
            'default_opening_time' => '10:00',
            'default_closing_time' => '18:00',
            'legend_text' => '',
        );
        
        foreach ($defaults as $key => $value) {
            $option_name = 'upsite_hours_' . $key;
            if (get_option($option_name) === false) {
                add_option($option_name, $value);
            }
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize plugin
Upsite_Opening_Hours::get_instance();


