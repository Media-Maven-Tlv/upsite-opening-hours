/**
 * Frontend Calendar JavaScript
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Initialize
     */
    function init() {
        // Initialize all calendar widgets on the page
        $('.upsite-hours-calendar-widget').each(function() {
            initCalendarWidget($(this));
        });
        
        // Add loaded class for fade-in animation
        $('.upsite-hours-calendar-widget, .upsite-hours-list').addClass('loaded');
    }
    
    /**
     * Initialize a calendar widget
     */
    function initCalendarWidget($widget) {
        const monthsData = $widget.data('months');
        if (!monthsData || !monthsData.length) {
            return;
        }
        
        let currentMonthIndex = 0;
        
        // Render initial month
        renderMonth($widget, monthsData[currentMonthIndex]);
        
        // Navigation buttons
        $widget.find('.upsite-prev-month').on('click', function() {
            if (currentMonthIndex > 0) {
                currentMonthIndex--;
                renderMonth($widget, monthsData[currentMonthIndex]);
            }
            updateNavButtons();
        });
        
        $widget.find('.upsite-next-month').on('click', function() {
            if (currentMonthIndex < monthsData.length - 1) {
                currentMonthIndex++;
                renderMonth($widget, monthsData[currentMonthIndex]);
            }
            updateNavButtons();
        });
        
        // Update navigation button states
        function updateNavButtons() {
            $widget.find('.upsite-prev-month').prop('disabled', currentMonthIndex === 0);
            $widget.find('.upsite-next-month').prop('disabled', currentMonthIndex === monthsData.length - 1);
        }
        
        updateNavButtons();
        
        // Modal close handler
        $widget.find('.upsite-hours-modal-close').on('click', function() {
            $widget.find('.upsite-hours-modal').fadeOut(200);
        });
        
        // Close modal on background click
        $widget.find('.upsite-hours-modal').on('click', function(e) {
            if ($(e.target).hasClass('upsite-hours-modal')) {
                $(this).fadeOut(200);
            }
        });
    }
    
    /**
     * Render a month
     */
    function renderMonth($widget, monthData) {
        // Update title
        $widget.find('.upsite-current-month-title').text(monthData.title);
        
        // Build calendar HTML
        let html = '';
        
        // Empty cells before first day
        for (let i = 0; i < monthData.first_day; i++) {
            html += '<div class="upsite-calendar-day empty"></div>';
        }
        
        // Days
        monthData.days.forEach(function(day) {
            let classes = 'upsite-calendar-day';
            if (day.enabled) {
                classes += ' enabled';
            } else if (day.disabled_marked) {
                classes += ' disabled-marked';
            } else {
                classes += ' disabled';
            }
            
            if (day.has_special) {
                classes += ' has-special';
            }
            
            let dataAttrs = 'data-date="' + escapeHtml(day.date) + '"';
            if (day.enabled || day.disabled_marked) {
                dataAttrs += ' data-opening="' + escapeHtml(day.opening) + '"';
                dataAttrs += ' data-closing="' + escapeHtml(day.closing) + '"';
                dataAttrs += ' data-note="' + escapeHtml(day.note) + '"';
                dataAttrs += ' data-day-name="' + escapeHtml(day.day_name) + '"';
            }
            
            html += '<div class="' + classes + '" ' + dataAttrs + '>';
            html += '<div class="day-number">' + day.day + '</div>';
            
            if (day.disabled_marked) {
                if (day.note) {
                    html += '<div class="day-status">' + escapeHtml(day.note) + '</div>';
                } else {
                    html += '<div class="day-status">סגור</div>'; // "Closed" in Hebrew
                }
            }
            
            html += '</div>';
        });
        
        // Update calendar
        $widget.find('.upsite-calendar-days').html(html);
        
        // Bind click events to days
        $widget.find('.upsite-calendar-day.enabled, .upsite-calendar-day.disabled-marked').on('click', function() {
            showModal($widget, $(this));
        });
    }
    
    /**
     * Show modal with hours
     */
    function showModal($widget, $day) {
        const date = $day.data('date');
        const opening = $day.data('opening');
        const closing = $day.data('closing');
        const note = $day.data('note');
        const dayName = $day.data('day-name');
        const isDisabled = $day.hasClass('disabled-marked');
        
        // Format date nicely
        const dateObj = new Date(date);
        const formatted = formatDateHebrew(dateObj);
        
        // Update modal content
        $widget.find('#upsite-modal-date').text(formatted);
        $widget.find('#upsite-modal-day').text(dayName);
        
        if (isDisabled) {
            $widget.find('.upsite-modal-hours').hide();
            $widget.find('#upsite-modal-times').html('<div class="modal-closed">סגור</div>');
        } else {
            $widget.find('.upsite-modal-hours').show();
            // RTL: Show closing time first, then opening time
            $widget.find('#upsite-modal-times').html(
                '<div class="modal-times">' + 
                '<span class="time-value">' + closing + '</span> - <span class="time-value">' + opening + '</span>' +
                '</div>'
            );
        }
        
        // Show note for both enabled and disabled dates
        if (note) {
            $widget.find('#upsite-modal-note').html(
                '<div class="modal-note">' + escapeHtml(note) + '</div>'
            ).show();
        } else {
            $widget.find('#upsite-modal-note').hide();
        }
        
        // Show modal
        $widget.find('.upsite-hours-modal').fadeIn(200);
    }
    
    /**
     * Format date in Hebrew format
     */
    function formatDateHebrew(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return day + '/' + month + '/' + year;
    }
    
    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Initialize on document ready
    $(document).ready(init);
    
})(jQuery);
