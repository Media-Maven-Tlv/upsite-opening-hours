/**
 * Frontend Calendar with Vanilla Calendar Pro
 *
 * @package UpsiteOpeningHours
 * @since 1.6.0
 */

(function($) {
    'use strict';

    /**
     * Initialize calendar widgets
     */
    function init() {
        $('.upsite-hours-calendar-widget').each(function() {
            initCalendarWidget($(this));
        });
        
        // Initialize list view
        $('.upsite-hours-list').addClass('loaded');
    }

    /**
     * Initialize a single calendar widget
     */
    function initCalendarWidget($widget) {
        const calendarEl = $widget.find('.upsite-calendar-container')[0];
        if (!calendarEl) {
            console.error('Calendar container not found');
            return;
        }

        // Wait for VanillaCalendar to be available
        if (typeof VanillaCalendar === 'undefined') {
            console.error('VanillaCalendar not loaded');
            return;
        }

        const monthsData = $widget.data('months');
        if (!monthsData || !monthsData.length) {
            console.error('No months data found');
            return;
        }

        // Build dates info object
        const datesInfo = {};
        const enabledDates = [];
        const disabledDates = [];
        
        monthsData.forEach(function(monthData) {
            monthData.days.forEach(function(day) {
                const dateStr = day.date;
                datesInfo[dateStr] = day;
                
                if (day.enabled) {
                    enabledDates.push(dateStr);
                } else if (day.disabled_marked) {
                    disabledDates.push(dateStr);
                }
            });
        });

        // Hebrew locale
        const hebrewLocale = {
            months: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני', 'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'],
            weekdays: ['א׳', 'ב׳', 'ג׳', 'ד׳', 'ה׳', 'ו׳', 'ש׳']
        };

        // Create popup content for dates
        const popups = {};
        Object.keys(datesInfo).forEach(function(dateStr) {
            const day = datesInfo[dateStr];
            if (day.enabled) {
                popups[dateStr] = {
                    modifier: 'upsite-open',
                    html: `<div class="vc-popup-content">
                        <span class="vc-popup-status open">פתוח</span>
                        <span class="vc-popup-times">${day.closing} - ${day.opening}</span>
                        ${day.note ? `<span class="vc-popup-note">${day.note}</span>` : ''}
                    </div>`
                };
            } else if (day.disabled_marked) {
                popups[dateStr] = {
                    modifier: 'upsite-closed',
                    html: `<div class="vc-popup-content">
                        <span class="vc-popup-status closed">סגור</span>
                        ${day.note ? `<span class="vc-popup-note">${day.note}</span>` : ''}
                    </div>`
                };
            }
        });

        // Initialize Vanilla Calendar
        const calendar = new VanillaCalendar(calendarEl, {
            type: 'default',
            settings: {
                lang: 'he',
                iso8601: false, // Week starts on Sunday
                visibility: {
                    theme: 'light',
                    weekend: false,
                    today: true,
                    daysOutside: false
                },
                selection: {
                    day: 'single'
                }
            },
            locale: hebrewLocale,
            popups: popups,
            actions: {
                clickDay(event, self) {
                    const selectedDate = self.selectedDates[0];
                    if (selectedDate && datesInfo[selectedDate]) {
                        showModal($widget, datesInfo[selectedDate]);
                    }
                },
                getDays(day, date, HTMLElement, HTMLButtonElement, self) {
                    const dayInfo = datesInfo[date];
                    
                    if (dayInfo) {
                        if (dayInfo.enabled) {
                            HTMLButtonElement.classList.add('upsite-day-open');
                            HTMLButtonElement.style.backgroundColor = '#5e35b1';
                            HTMLButtonElement.style.color = 'white';
                            HTMLButtonElement.style.borderRadius = '50%';
                            HTMLButtonElement.style.fontWeight = '600';
                        } else if (dayInfo.disabled_marked) {
                            HTMLButtonElement.classList.add('upsite-day-closed');
                            HTMLButtonElement.style.backgroundColor = '#b39ddb';
                            HTMLButtonElement.style.color = 'white';
                            HTMLButtonElement.style.borderRadius = '50%';
                        }
                    } else {
                        // Undefined dates - make them non-clickable
                        HTMLButtonElement.style.opacity = '0.3';
                        HTMLButtonElement.style.pointerEvents = 'none';
                    }
                }
            }
        });

        calendar.init();
        
        // Mark as loaded
        $widget.addClass('loaded');

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
     * Show modal with date information
     */
    function showModal($widget, dayInfo) {
        const isDisabled = !dayInfo.enabled;
        const dateObj = new Date(dayInfo.date);
        const formatted = formatDateHebrew(dateObj);
        
        // Update modal content
        $widget.find('#upsite-modal-date').text(formatted);
        $widget.find('#upsite-modal-day').text(dayInfo.day_name);
        
        // Add or update status badge
        let statusBadge = $widget.find('.upsite-modal-status');
        if (!statusBadge.length) {
            $widget.find('#upsite-modal-day').after('<div class="upsite-modal-status"></div>');
            statusBadge = $widget.find('.upsite-modal-status');
        }
        
        if (isDisabled) {
            statusBadge.removeClass('status-open').addClass('status-closed').text('סגור');
            $widget.find('.upsite-modal-hours').hide();
            $widget.find('#upsite-modal-times').html('');
        } else {
            statusBadge.removeClass('status-closed').addClass('status-open').text('פתוח');
            $widget.find('.upsite-modal-hours').show();
            // RTL: Show closing time first, then opening time
            $widget.find('#upsite-modal-times').html(
                '<div class="modal-times">' + 
                '<span class="time-value">' + dayInfo.closing + '</span> - <span class="time-value">' + dayInfo.opening + '</span>' +
                '</div>'
            );
        }
        
        // Show note for both enabled and disabled dates
        if (dayInfo.note) {
            $widget.find('#upsite-modal-note').html(
                '<div class="modal-note">' + escapeHtml(dayInfo.note) + '</div>'
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
