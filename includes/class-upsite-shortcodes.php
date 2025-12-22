<?php
/**
 * Shortcodes class
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Upsite_Shortcodes
 */
class Upsite_Shortcodes {
    
    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode('upsite_hours_calendar', array(__CLASS__, 'render_calendar_shortcode'));
        add_shortcode('upsite_hours_list', array(__CLASS__, 'render_list_shortcode'));
    }
    
    /**
     * Render calendar shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public static function render_calendar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'year' => null,
            'month' => null,
            'months' => 3,
            'title' => 'Opening Hours',
        ), $atts, 'upsite_hours_calendar');
        
        // If no year/month specified, start from current month
        if (!$atts['year'] || !$atts['month']) {
            $start_date = new DateTime();
        } else {
            $start_date = new DateTime($atts['year'] . '-' . str_pad($atts['month'], 2, '0', STR_PAD_LEFT) . '-01');
        }
        
        // Get all dates (including disabled ones)
        $all_dates = Upsite_Database::get_dates(array('enabled_only' => false));
        
        // Index dates by date string for quick lookup
        $dates_by_date = array();
        foreach ($all_dates as $date) {
            $dates_by_date[$date->date] = $date;
        }
        
        // Get colors
        $colors = self::get_colors();
        
        // Prepare all months data as JSON for JavaScript
        $months_data = array();
        $months_to_show = intval($atts['months']);
        
        for ($i = 0; $i < $months_to_show; $i++) {
            $current_month = clone $start_date;
            $current_month->modify("+$i months");
            
            $year = $current_month->format('Y');
            $month = $current_month->format('m');
            $month_name = self::get_hebrew_month_name($year . '-' . $month);
            $first_day = intval($current_month->format('w'));
            $days_in_month = intval($current_month->format('t'));
            
            $days = array();
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date_str = sprintf('%s-%s-%02d', $year, $month, $day);
                $date_data = isset($dates_by_date[$date_str]) ? $dates_by_date[$date_str] : null;
                
                $days[] = array(
                    'day' => $day,
                    'date' => $date_str,
                    'enabled' => $date_data && $date_data->is_enabled,
                    'disabled_marked' => $date_data && !$date_data->is_enabled,
                    'has_special' => $date_data && !empty($date_data->special_note),
                    'opening' => $date_data ? self::format_time($date_data->opening_time) : '',
                    'closing' => $date_data ? self::format_time($date_data->closing_time) : '',
                    'note' => $date_data ? $date_data->special_note : '',
                    'day_name' => self::get_hebrew_day_name($date_str),
                );
            }
            
            $months_data[] = array(
                'year' => $year,
                'month' => $month,
                'title' => $month_name,
                'first_day' => $first_day,
                'days' => $days,
            );
        }
        
        // Generate unique ID for this calendar instance
        $calendar_id = 'upsite-calendar-' . uniqid();
        
        // Start output
        ob_start();
        ?>
        <div class="upsite-hours-calendar-widget" id="<?php echo esc_attr($calendar_id); ?>" style="<?php echo esc_attr(self::get_css_vars($colors)); ?>" data-months='<?php echo esc_attr(json_encode($months_data)); ?>'>
            <?php if (!empty($atts['title'])): ?>
                <h2 class="upsite-hours-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>
            
            <!-- Month Navigation -->
            <div class="upsite-calendar-navigation">
                <button type="button" class="upsite-nav-btn upsite-prev-month" aria-label="<?php esc_attr_e('Previous month', 'upsite-opening-hours'); ?>">
                    <span>‹</span>
                </button>
                <h3 class="upsite-current-month-title"></h3>
                <button type="button" class="upsite-nav-btn upsite-next-month" aria-label="<?php esc_attr_e('Next month', 'upsite-opening-hours'); ?>">
                    <span>›</span>
                </button>
            </div>
            
            <!-- Single Calendar Container -->
            <div class="upsite-calendar-month">
                <div class="upsite-calendar-grid">
                    <!-- Day headers -->
                    <div class="upsite-calendar-header">
                        <div class="upsite-day-header">א'</div>
                        <div class="upsite-day-header">ב'</div>
                        <div class="upsite-day-header">ג'</div>
                        <div class="upsite-day-header">ד'</div>
                        <div class="upsite-day-header">ה'</div>
                        <div class="upsite-day-header">ו'</div>
                        <div class="upsite-day-header">ש'</div>
                    </div>
                    
                    <!-- Calendar days (will be populated by JavaScript) -->
                    <div class="upsite-calendar-days"></div>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="upsite-calendar-legend">
                <?php 
                $legend_text = get_option('upsite_hours_legend_text', '');
                if (!empty($legend_text)): 
                ?>
                    <div class="legend-text"><?php echo esc_html($legend_text); ?></div>
                <?php endif; ?>
                <div class="legend-items">
                    <div class="legend-item">
                        <span class="legend-circle open"></span>
                        <span class="legend-label">פתוח</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-circle closed"></span>
                        <span class="legend-label">סגור</span>
                    </div>
                </div>
            </div>
            
            <!-- Modal for showing hours -->
            <div id="upsite-hours-modal" class="upsite-hours-modal" style="display: none;">
                <div class="upsite-hours-modal-content">
                    <span class="upsite-hours-modal-close">&times;</span>
                    <h3 id="upsite-modal-date"></h3>
                    <div id="upsite-modal-day"></div>
                    <div class="upsite-modal-hours">
                        <div class="hours-label"><?php esc_html_e('שעות פעילות:', 'upsite-opening-hours'); ?></div>
                        <div id="upsite-modal-times"></div>
                    </div>
                    <div id="upsite-modal-note"></div>
                </div>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render list shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public static function render_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'year' => null,
            'month' => null,
            'limit' => null,
            'title' => 'Opening Hours',
        ), $atts, 'upsite_hours_list');
        
        // Get dates (including disabled ones with notes)
        $args = array(
            'year' => $atts['year'] ? intval($atts['year']) : null,
            'month' => $atts['month'] ? intval($atts['month']) : null,
            'limit' => $atts['limit'] ? intval($atts['limit']) : null,
            'enabled_only' => false,
        );
        
        $dates_grouped = Upsite_Database::get_dates_grouped_by_month($args);
        
        if (empty($dates_grouped)) {
            return '<div class="upsite-hours-empty">' . esc_html__('No opening hours available at this time.', 'upsite-opening-hours') . '</div>';
        }
        
        // Get colors
        $colors = self::get_colors();
        
        // Start output
        ob_start();
        ?>
        <div class="upsite-hours-list" style="<?php echo esc_attr(self::get_css_vars($colors)); ?>">
            <?php if (!empty($atts['title'])): ?>
                <h2 class="upsite-hours-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>
            
            <?php foreach ($dates_grouped as $year_month => $dates): ?>
                <?php
                $month_name = self::get_hebrew_month_name($year_month);
                ?>
                <div class="upsite-list-month">
                    <h3 class="upsite-list-month-title"><?php echo esc_html($month_name); ?></h3>
                    <div class="upsite-list-dates">
                        <?php foreach ($dates as $date): ?>
                            <?php
                            $day_name = self::get_hebrew_day_name($date->date);
                            $formatted_date = self::format_date_hebrew($date->date);
                            $has_special = !empty($date->special_note);
                            $is_enabled = $date->is_enabled;
                            $is_disabled = !$date->is_enabled;
                            ?>
                            <div class="upsite-list-date <?php echo $has_special ? 'has-special' : ''; ?> <?php echo $is_disabled ? 'disabled' : ''; ?>">
                                <span class="list-date"><?php echo esc_html($formatted_date); ?></span>
                                <span class="list-day"><?php echo esc_html($day_name); ?></span>
                                <?php if ($is_enabled): ?>
                                    <span class="list-hours"><?php echo esc_html(self::format_time($date->closing_time)); ?>-<?php echo esc_html(self::format_time($date->opening_time)); ?></span>
                                    <?php if ($has_special): ?>
                                        <span class="list-special"><?php echo esc_html($date->special_note); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="list-hours list-closed">
                                        <?php echo $has_special ? esc_html($date->special_note) : esc_html__('סגור', 'upsite-opening-hours'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get colors from settings
     *
     * @return array
     */
    private static function get_colors() {
        return array(
            'enabled_bg' => get_option('upsite_hours_enabled_bg_color', '#4CAF50'),
            'disabled_bg' => get_option('upsite_hours_disabled_bg_color', '#f5f5f5'),
            'text' => get_option('upsite_hours_text_color', '#333333'),
            'special_highlight' => get_option('upsite_hours_special_highlight_color', '#FF9800'),
            'primary_accent' => get_option('upsite_hours_primary_accent_color', '#2196F3'),
        );
    }
    
    /**
     * Get CSS variables string
     *
     * @param array $colors
     * @return string
     */
    private static function get_css_vars($colors) {
        return sprintf(
            '--upsite-enabled-bg: %s; --upsite-disabled-bg: %s; --upsite-text: %s; --upsite-special: %s; --upsite-accent: %s;',
            esc_attr($colors['enabled_bg']),
            esc_attr($colors['disabled_bg']),
            esc_attr($colors['text']),
            esc_attr($colors['special_highlight']),
            esc_attr($colors['primary_accent'])
        );
    }
    
    /**
     * Get Hebrew month name
     *
     * @param string $year_month Format: YYYY-MM
     * @return string
     */
    private static function get_hebrew_month_name($year_month) {
        $months_hebrew = array(
            '01' => 'ינואר',
            '02' => 'פברואר',
            '03' => 'מרץ',
            '04' => 'אפריל',
            '05' => 'מאי',
            '06' => 'יוני',
            '07' => 'יולי',
            '08' => 'אוגוסט',
            '09' => 'ספטמבר',
            '10' => 'אוקטובר',
            '11' => 'נובמבר',
            '12' => 'דצמבר',
        );
        
        list($year, $month) = explode('-', $year_month);
        $month_name = isset($months_hebrew[$month]) ? $months_hebrew[$month] : '';
        
        return sprintf('%s %s', $month_name, $year);
    }
    
    /**
     * Get Hebrew day name
     *
     * @param string $date Date in YYYY-MM-DD format
     * @return string
     */
    private static function get_hebrew_day_name($date) {
        $days_hebrew = array(
            0 => 'יום ראשון',
            1 => 'יום שני',
            2 => 'יום שלישי',
            3 => 'יום רביעי',
            4 => 'יום חמישי',
            5 => 'יום שישי',
            6 => 'יום שבת',
        );
        
        $day_of_week = date('w', strtotime($date));
        return $days_hebrew[$day_of_week];
    }
    
    /**
     * Format date in Hebrew format (DD/MM/YYYY)
     *
     * @param string $date Date in YYYY-MM-DD format
     * @return string
     */
    private static function format_date_hebrew($date) {
        return date('d/m/Y', strtotime($date));
    }
    
    /**
     * Format time from HH:MM:SS to HH:MM
     *
     * @param string $time Time string
     * @return string
     */
    private static function format_time($time) {
        // If time is already in HH:MM format, return as is
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        // Otherwise, extract HH:MM from HH:MM:SS
        return substr($time, 0, 5);
    }
}

