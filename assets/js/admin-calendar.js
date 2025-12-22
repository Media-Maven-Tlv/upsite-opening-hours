/**
 * Admin Calendar JavaScript
 *
 * @package UpsiteOpeningHours
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    let currentDate = new Date();
    let allDates = {};
    
    /**
     * Initialize
     */
    function init() {
        // Initialize color pickers
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            $('.upsite-color-picker').wpColorPicker();
        }
        
        // Check if we're on the calendar tab
        if ($('#upsite-calendar-grid').length) {
            loadDates();
            bindEvents();
        }
    }
    
    /**
     * Bind events
     */
    function bindEvents() {
        $('#upsite-prev-month').on('click', prevMonth);
        $('#upsite-next-month').on('click', nextMonth);
        $('.upsite-modal-close').on('click', closeModal);
        $('#upsite-date-form').on('submit', saveDate);
        $('#upsite-delete-date').on('click', deleteDate);
        $('#upsite-apply-range').on('click', applyDateRange);
        
        // Close modal on background click
        $(window).on('click', function(e) {
            if ($(e.target).hasClass('upsite-modal')) {
                closeModal();
            }
        });
    }
    
    /**
     * Load dates from API
     */
    function loadDates() {
        $.ajax({
            url: upsiteHoursAdmin.apiUrl + '/dates',
            method: 'GET',
            data: {
                enabled_only: false
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', upsiteHoursAdmin.nonce);
            },
            success: function(response) {
                if (response.success && response.data) {
                    allDates = {};
                    response.data.forEach(function(date) {
                        allDates[date.date] = date;
                    });
                    renderCalendar();
                }
            },
            error: function() {
                alert(upsiteHoursAdmin.i18n.errorLoading);
            }
        });
    }
    
    /**
     * Render calendar
     */
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        // Update month title
        $('#upsite-current-month').text(
            upsiteHoursAdmin.i18n.months[month] + ' ' + year
        );
        
        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Build calendar HTML
        let html = '<div class="upsite-calendar">';
        
        // Day headers
        html += '<div class="calendar-header">';
        upsiteHoursAdmin.i18n.days.forEach(function(day) {
            html += '<div class="calendar-day-header">' + day + '</div>';
        });
        html += '</div>';
        
        // Calendar grid
        html += '<div class="calendar-grid">';
        
        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="calendar-cell empty"></div>';
        }
        
        // Days of month
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = formatDateString(year, month + 1, day);
            const dateData = allDates[dateStr];
            
            let classes = 'calendar-cell';
            let style = '';
            
            if (dateData) {
                if (dateData.is_enabled == 1) {
                    classes += ' enabled';
                    if (dateData.special_note) {
                        classes += ' special';
                        style = 'background-color: ' + upsiteHoursAdmin.colors.special;
                    } else {
                        style = 'background-color: ' + upsiteHoursAdmin.colors.enabled;
                    }
                } else {
                    classes += ' disabled';
                    style = 'background-color: ' + upsiteHoursAdmin.colors.disabled;
                }
            }
            
            html += '<div class="' + classes + '" data-date="' + dateStr + '" style="' + style + '">';
            html += '<div class="calendar-day-number">' + day + '</div>';
            
            if (dateData) {
                html += '<div class="calendar-day-info">';
                html += dateData.opening_time.substring(0, 5) + '-' + dateData.closing_time.substring(0, 5);
                if (dateData.special_note) {
                    html += '<br><small>' + escapeHtml(dateData.special_note) + '</small>';
                }
                html += '</div>';
            }
            
            html += '</div>';
        }
        
        html += '</div></div>';
        
        $('#upsite-calendar-grid').html(html);
        
        // Bind click events to cells
        $('.calendar-cell:not(.empty)').on('click', function() {
            openModal($(this).data('date'));
        });
    }
    
    /**
     * Format date string
     */
    function formatDateString(year, month, day) {
        return year + '-' + 
               String(month).padStart(2, '0') + '-' + 
               String(day).padStart(2, '0');
    }
    
    /**
     * Previous month
     */
    function prevMonth() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    }
    
    /**
     * Next month
     */
    function nextMonth() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    }
    
    /**
     * Open modal
     */
    function openModal(dateStr) {
        const dateData = allDates[dateStr];
        const date = new Date(dateStr);
        const dateFormatted = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        $('#upsite-modal-title').text(dateFormatted);
        $('#upsite-date-value').val(dateStr);
        
        if (dateData) {
            // Edit existing date
            $('#upsite-opening-time').val(dateData.opening_time.substring(0, 5));
            $('#upsite-closing-time').val(dateData.closing_time.substring(0, 5));
            $('#upsite-special-note').val(dateData.special_note);
            $('#upsite-is-enabled').prop('checked', dateData.is_enabled == 1);
            $('#upsite-delete-date').show();
        } else {
            // New date
            $('#upsite-opening-time').val(upsiteHoursAdmin.defaultOpeningTime);
            $('#upsite-closing-time').val(upsiteHoursAdmin.defaultClosingTime);
            $('#upsite-special-note').val('');
            $('#upsite-is-enabled').prop('checked', true);
            $('#upsite-delete-date').hide();
        }
        
        $('#upsite-date-modal').fadeIn(200);
    }
    
    /**
     * Close modal
     */
    function closeModal() {
        $('#upsite-date-modal').fadeOut(200);
    }
    
    /**
     * Save date
     */
    function saveDate(e) {
        e.preventDefault();
        
        const formData = {
            date: $('#upsite-date-value').val(),
            opening_time: $('#upsite-opening-time').val(),
            closing_time: $('#upsite-closing-time').val(),
            special_note: $('#upsite-special-note').val(),
            is_enabled: $('#upsite-is-enabled').is(':checked')
        };
        
        $.ajax({
            url: upsiteHoursAdmin.apiUrl + '/dates',
            method: 'POST',
            data: formData,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', upsiteHoursAdmin.nonce);
            },
            success: function(response) {
                if (response.success) {
                    allDates[formData.date] = response.data;
                    closeModal();
                    renderCalendar();
                } else {
                    alert(response.message || upsiteHoursAdmin.i18n.errorSaving);
                }
            },
            error: function() {
                alert(upsiteHoursAdmin.i18n.errorSaving);
            }
        });
    }
    
    /**
     * Delete date
     */
    function deleteDate() {
        if (!confirm(upsiteHoursAdmin.i18n.confirmDelete)) {
            return;
        }
        
        const dateStr = $('#upsite-date-value').val();
        
        $.ajax({
            url: upsiteHoursAdmin.apiUrl + '/dates/' + dateStr,
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', upsiteHoursAdmin.nonce);
            },
            success: function(response) {
                if (response.success) {
                    delete allDates[dateStr];
                    closeModal();
                    renderCalendar();
                } else {
                    alert(response.message || upsiteHoursAdmin.i18n.errorDeleting);
                }
            },
            error: function() {
                alert(upsiteHoursAdmin.i18n.errorDeleting);
            }
        });
    }
    
    /**
     * Apply date range
     */
    function applyDateRange() {
        const startDate = $('#upsite-range-start').val();
        const endDate = $('#upsite-range-end').val();
        const openingTime = $('#upsite-range-opening').val();
        const closingTime = $('#upsite-range-closing').val();
        const specialNote = $('#upsite-range-note').val();
        const isEnabled = $('#upsite-range-enabled').is(':checked');
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }
        
        if (!openingTime || !closingTime) {
            alert('Please enter both opening and closing times.');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            alert('Start date must be before or equal to end date.');
            return;
        }
        
        // Confirm action
        const start = new Date(startDate);
        const end = new Date(endDate);
        const dayCount = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        
        if (!confirm('Apply these hours to ' + dayCount + ' date(s)?')) {
            return;
        }
        
        // Show loading
        $('#upsite-apply-range').prop('disabled', true).text('Applying...');
        
        // Generate array of dates
        const dates = [];
        const current = new Date(startDate);
        while (current <= end) {
            dates.push(current.toISOString().split('T')[0]);
            current.setDate(current.getDate() + 1);
        }
        
        // Save each date
        let completed = 0;
        let errors = 0;
        
        dates.forEach(function(date) {
            $.ajax({
                url: upsiteHoursAdmin.apiUrl + '/dates',
                method: 'POST',
                data: {
                    date: date,
                    opening_time: openingTime,
                    closing_time: closingTime,
                    special_note: specialNote,
                    is_enabled: isEnabled
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', upsiteHoursAdmin.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        allDates[date] = response.data;
                    } else {
                        errors++;
                    }
                },
                error: function() {
                    errors++;
                },
                complete: function() {
                    completed++;
                    if (completed === dates.length) {
                        // All done
                        $('#upsite-apply-range').prop('disabled', false).text(upsiteHoursAdmin.i18n.applyRange || 'Apply to Date Range');
                        
                        if (errors > 0) {
                            alert('Completed with ' + errors + ' error(s). Some dates may not have been saved.');
                        } else {
                            alert('Successfully applied hours to ' + dates.length + ' date(s)!');
                            // Clear form
                            $('#upsite-range-start').val('');
                            $('#upsite-range-end').val('');
                            $('#upsite-range-note').val('');
                        }
                        
                        // Refresh calendar
                        renderCalendar();
                    }
                }
            });
        });
    }
    
    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Initialize on document ready
    $(document).ready(init);
    
})(jQuery);


