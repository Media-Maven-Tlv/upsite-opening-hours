<?php
/**
 * Admin functionality class
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Upsite_Admin
 */
class Upsite_Admin {
    
    /**
     * Initialize admin
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_notices', array(__CLASS__, 'activation_notice'));
    }
    
    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_options_page(
            __('Opening Hours', 'upsite-opening-hours'),
            __('Opening Hours', 'upsite-opening-hours'),
            'manage_options',
            'upsite-opening-hours',
            array(__CLASS__, 'render_admin_page')
        );
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        // Color settings
        register_setting('upsite_hours_colors', 'upsite_hours_enabled_bg_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '#4CAF50',
        ));
        
        register_setting('upsite_hours_colors', 'upsite_hours_disabled_bg_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '#f5f5f5',
        ));
        
        register_setting('upsite_hours_colors', 'upsite_hours_text_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '#333333',
        ));
        
        register_setting('upsite_hours_colors', 'upsite_hours_special_highlight_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '#FF9800',
        ));
        
        register_setting('upsite_hours_colors', 'upsite_hours_primary_accent_color', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '#2196F3',
        ));
        
        // General settings
        register_setting('upsite_hours_general', 'upsite_hours_default_opening_time', array(
            'type' => 'string',
            'sanitize_callback' => array(__CLASS__, 'sanitize_time'),
            'default' => '10:00',
        ));
        
        register_setting('upsite_hours_general', 'upsite_hours_default_closing_time', array(
            'type' => 'string',
            'sanitize_callback' => array(__CLASS__, 'sanitize_time'),
            'default' => '18:00',
        ));
        
        register_setting('upsite_hours_general', 'upsite_hours_legend_text', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        
        // Color settings section
        add_settings_section(
            'upsite_hours_colors_section',
            __('Color Customization', 'upsite-opening-hours'),
            array(__CLASS__, 'render_colors_section'),
            'upsite_hours_colors'
        );
        
        // General settings section
        add_settings_section(
            'upsite_hours_general_section',
            __('General Settings', 'upsite-opening-hours'),
            array(__CLASS__, 'render_general_section'),
            'upsite_hours_general'
        );
        
        // Add color fields
        add_settings_field(
            'enabled_bg_color',
            __('Enabled Dates Background', 'upsite-opening-hours'),
            array(__CLASS__, 'render_color_field'),
            'upsite_hours_colors',
            'upsite_hours_colors_section',
            array('field' => 'enabled_bg_color')
        );
        
        add_settings_field(
            'disabled_bg_color',
            __('Disabled Dates Background', 'upsite-opening-hours'),
            array(__CLASS__, 'render_color_field'),
            'upsite_hours_colors',
            'upsite_hours_colors_section',
            array('field' => 'disabled_bg_color')
        );
        
        add_settings_field(
            'text_color',
            __('Text Color', 'upsite-opening-hours'),
            array(__CLASS__, 'render_color_field'),
            'upsite_hours_colors',
            'upsite_hours_colors_section',
            array('field' => 'text_color')
        );
        
        add_settings_field(
            'special_highlight_color',
            __('Special Days Highlight', 'upsite-opening-hours'),
            array(__CLASS__, 'render_color_field'),
            'upsite_hours_colors',
            'upsite_hours_colors_section',
            array('field' => 'special_highlight_color')
        );
        
        add_settings_field(
            'primary_accent_color',
            __('Primary Accent Color', 'upsite-opening-hours'),
            array(__CLASS__, 'render_color_field'),
            'upsite_hours_colors',
            'upsite_hours_colors_section',
            array('field' => 'primary_accent_color')
        );
        
        // Add general fields
        add_settings_field(
            'default_opening_time',
            __('Default Opening Time', 'upsite-opening-hours'),
            array(__CLASS__, 'render_time_field'),
            'upsite_hours_general',
            'upsite_hours_general_section',
            array('field' => 'default_opening_time')
        );
        
        add_settings_field(
            'default_closing_time',
            __('Default Closing Time', 'upsite-opening-hours'),
            array(__CLASS__, 'render_time_field'),
            'upsite_hours_general',
            'upsite_hours_general_section',
            array('field' => 'default_closing_time')
        );
        
        add_settings_field(
            'legend_text',
            'טקסט מעל מקרא',
            array(__CLASS__, 'render_text_field'),
            'upsite_hours_general',
            'upsite_hours_general_section',
            array('field' => 'legend_text', 'placeholder' => 'השאר ריק כדי להסתיר')
        );
    }
    
    /**
     * Render colors section
     */
    public static function render_colors_section() {
        echo '<p>' . esc_html__('Customize the colors for the opening hours display on your website.', 'upsite-opening-hours') . '</p>';
    }
    
    /**
     * Render general section
     */
    public static function render_general_section() {
        echo '<p>' . esc_html__('Set default times that will be pre-filled when adding new dates.', 'upsite-opening-hours') . '</p>';
    }
    
    /**
     * Render color field
     */
    public static function render_color_field($args) {
        $field = $args['field'];
        $value = get_option('upsite_hours_' . $field);
        
        printf(
            '<input type="text" name="upsite_hours_%s" value="%s" class="upsite-color-picker" data-default-color="%s" />',
            esc_attr($field),
            esc_attr($value),
            esc_attr($value)
        );
    }
    
    /**
     * Render time field
     */
    public static function render_time_field($args) {
        $field = $args['field'];
        $value = get_option('upsite_hours_' . $field);
        
        printf(
            '<input type="time" name="upsite_hours_%s" value="%s" />',
            esc_attr($field),
            esc_attr($value)
        );
    }
    
    /**
     * Render text field
     */
    public static function render_text_field($args) {
        $field = $args['field'];
        $value = get_option('upsite_hours_' . $field);
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        
        printf(
            '<input type="text" name="upsite_hours_%s" value="%s" placeholder="%s" style="width: 400px;" />',
            esc_attr($field),
            esc_attr($value),
            esc_attr($placeholder)
        );
    }
    
    /**
     * Sanitize time input
     */
    public static function sanitize_time($time) {
        // Validate time format HH:MM
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            return $time;
        }
        return '10:00';
    }
    
    /**
     * Render admin page
     */
    public static function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get current tab
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'calendar';
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=upsite-opening-hours&tab=calendar" class="nav-tab <?php echo $active_tab === 'calendar' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Calendar Manager', 'upsite-opening-hours'); ?>
                </a>
                <a href="?page=upsite-opening-hours&tab=colors" class="nav-tab <?php echo $active_tab === 'colors' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Color Settings', 'upsite-opening-hours'); ?>
                </a>
                <a href="?page=upsite-opening-hours&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('General Settings', 'upsite-opening-hours'); ?>
                </a>
                <a href="?page=upsite-opening-hours&tab=shortcodes" class="nav-tab <?php echo $active_tab === 'shortcodes' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Shortcodes', 'upsite-opening-hours'); ?>
                </a>
            </h2>
            
            <?php
            switch ($active_tab) {
                case 'calendar':
                    self::render_calendar_tab();
                    break;
                case 'colors':
                    self::render_colors_tab();
                    break;
                case 'general':
                    self::render_general_tab();
                    break;
                case 'shortcodes':
                    self::render_shortcodes_tab();
                    break;
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Render calendar tab
     */
    private static function render_calendar_tab() {
        ?>
        <div class="upsite-calendar-manager">
            <!-- Date Range Selector -->
            <div class="upsite-range-selector">
                <h3>הוספה מהירה - טווח תאריכים</h3>
                <p class="description">בחר טווח תאריכים כדי להחיל שעות פתיחה זהות על מספר תאריכים בבת אחת.</p>
                
                <div class="upsite-range-form">
                    <div class="range-dates">
                        <label>
                            מתאריך:
                            <input type="date" id="upsite-range-start" />
                        </label>
                        <label>
                            עד תאריך:
                            <input type="date" id="upsite-range-end" />
                        </label>
                    </div>
                    
                    <div class="range-times">
                        <label>
                            שעת פתיחה:
                            <input type="time" id="upsite-range-opening" value="<?php echo esc_attr(get_option('upsite_hours_default_opening_time', '10:00')); ?>" />
                        </label>
                        <label>
                            שעת סגירה:
                            <input type="time" id="upsite-range-closing" value="<?php echo esc_attr(get_option('upsite_hours_default_closing_time', '18:00')); ?>" />
                        </label>
                    </div>
                    
                    <div class="range-options">
                        <label>
                            הערה מיוחדת (אופציונלי):
                            <input type="text" id="upsite-range-note" placeholder="לדוגמה: חג, אירוע מיוחד" />
                        </label>
                        <label>
                            <input type="checkbox" id="upsite-range-enabled" checked />
                            פעיל
                        </label>
                    </div>
                    
                    <button type="button" id="upsite-apply-range" class="button button-primary button-large">
                        החל על טווח התאריכים
                    </button>
                </div>
            </div>
            
            <hr style="margin: 30px 0;" />
            
            <div class="upsite-calendar-header">
                <button type="button" id="upsite-prev-month" class="button">‹ <?php esc_html_e('Previous', 'upsite-opening-hours'); ?></button>
                <h2 id="upsite-current-month"></h2>
                <button type="button" id="upsite-next-month" class="button"><?php esc_html_e('Next', 'upsite-opening-hours'); ?> ›</button>
            </div>
            
            <div id="upsite-calendar-grid"></div>
            
            <div class="upsite-calendar-legend">
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?php echo esc_attr(get_option('upsite_hours_enabled_bg_color', '#4CAF50')); ?>"></span>
                    <span><?php esc_html_e('Enabled dates', 'upsite-opening-hours'); ?></span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?php echo esc_attr(get_option('upsite_hours_special_highlight_color', '#FF9800')); ?>"></span>
                    <span><?php esc_html_e('Special days', 'upsite-opening-hours'); ?></span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: <?php echo esc_attr(get_option('upsite_hours_disabled_bg_color', '#f5f5f5')); ?>"></span>
                    <span><?php esc_html_e('Disabled dates', 'upsite-opening-hours'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Modal for editing dates -->
        <div id="upsite-date-modal" class="upsite-modal" style="display: none;">
            <div class="upsite-modal-content">
                <span class="upsite-modal-close">&times;</span>
                <h2 id="upsite-modal-title"></h2>
                
                <form id="upsite-date-form">
                    <input type="hidden" id="upsite-date-value" name="date" />
                    
                    <div class="form-field">
                        <label for="upsite-opening-time">שעת פתיחה</label>
                        <input type="time" id="upsite-opening-time" name="opening_time" required />
                    </div>
                    
                    <div class="form-field">
                        <label for="upsite-closing-time">שעת סגירה</label>
                        <input type="time" id="upsite-closing-time" name="closing_time" required />
                    </div>
                    
                    <div class="form-field">
                        <label for="upsite-special-note">הערה מיוחדת (למשל: שם חג)</label>
                        <input type="text" id="upsite-special-note" name="special_note" placeholder="אופציונלי" />
                    </div>
                    
                    <div class="form-field">
                        <label>
                            <input type="checkbox" id="upsite-is-enabled" name="is_enabled" checked />
                            התאריך פעיל
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button button-primary">שמור</button>
                        <button type="button" id="upsite-delete-date" class="button button-secondary" style="display: none;">מחק</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render colors tab
     */
    private static function render_colors_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('upsite_hours_colors');
            do_settings_sections('upsite_hours_colors');
            submit_button();
            ?>
        </form>
        <?php
    }
    
    /**
     * Render general tab
     */
    private static function render_general_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('upsite_hours_general');
            do_settings_sections('upsite_hours_general');
            submit_button();
            ?>
        </form>
        <?php
    }
    
    /**
     * Render shortcodes tab
     */
    private static function render_shortcodes_tab() {
        ?>
        <div class="upsite-shortcodes-info">
            <h3><?php esc_html_e('Available Shortcodes', 'upsite-opening-hours'); ?></h3>
            
            <div class="shortcode-example">
                <h4><?php esc_html_e('Calendar Widget (Full Calendar View)', 'upsite-opening-hours'); ?></h4>
                <code>[upsite_hours_calendar]</code>
                <p><?php esc_html_e('Displays a full interactive calendar widget showing all days of the month. Enabled dates are highlighted with opening hours.', 'upsite-opening-hours'); ?></p>
                
                <h5><?php esc_html_e('Parameters:', 'upsite-opening-hours'); ?></h5>
                <ul>
                    <li><code>year="2025"</code> - <?php esc_html_e('Start from specific year', 'upsite-opening-hours'); ?></li>
                    <li><code>month="12"</code> - <?php esc_html_e('Start from specific month (1-12)', 'upsite-opening-hours'); ?></li>
                    <li><code>months="3"</code> - <?php esc_html_e('Number of months to display (default: 3)', 'upsite-opening-hours'); ?></li>
                </ul>
                
                <h5><?php esc_html_e('Examples:', 'upsite-opening-hours'); ?></h5>
                <code>[upsite_hours_calendar]</code> - <?php esc_html_e('Shows next 3 months', 'upsite-opening-hours'); ?><br>
                <code>[upsite_hours_calendar year="2025" month="12" months="2"]</code> - <?php esc_html_e('Shows Dec 2025 and Jan 2026', 'upsite-opening-hours'); ?>
            </div>
            
            <div class="shortcode-example">
                <h4><?php esc_html_e('List View', 'upsite-opening-hours'); ?></h4>
                <code>[upsite_hours_list]</code>
                <p><?php esc_html_e('Displays opening hours as a simple list grouped by month.', 'upsite-opening-hours'); ?></p>
                
                <h5><?php esc_html_e('Parameters:', 'upsite-opening-hours'); ?></h5>
                <ul>
                    <li><code>year="2025"</code> - <?php esc_html_e('Filter by year', 'upsite-opening-hours'); ?></li>
                    <li><code>month="12"</code> - <?php esc_html_e('Filter by specific month (1-12)', 'upsite-opening-hours'); ?></li>
                    <li><code>limit="10"</code> - <?php esc_html_e('Limit number of entries', 'upsite-opening-hours'); ?></li>
                </ul>
                
                <h5><?php esc_html_e('Example:', 'upsite-opening-hours'); ?></h5>
                <code>[upsite_hours_list year="2025"]</code>
            </div>
        </div>
        <?php
    }
    
    /**
     * Activation notice
     */
    public static function activation_notice() {
        if (get_transient('upsite_hours_activation_notice')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Upsite Opening Hours plugin activated successfully! Go to Settings → Opening Hours to configure.', 'upsite-opening-hours'); ?></p>
            </div>
            <?php
            delete_transient('upsite_hours_activation_notice');
        }
    }
}

