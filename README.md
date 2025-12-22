# Upsite Opening Hours

~Current Version:1.2.0~

A WordPress plugin for managing and displaying opening hours with an interactive calendar interface for Superland and Lunapark websites.

## Features

- **Interactive Admin Calendar** - Click dates to set opening/closing hours
- **Color Customization** - Fully customizable colors from the admin panel
- **Hebrew Support** - Full RTL support with Hebrew day and month names
- **Two Display Modes** - Calendar view and list view shortcodes
- **REST API** - Modern REST API endpoints for all operations
- **Special Days** - Mark holidays and special events with custom notes
- **Responsive Design** - Mobile-friendly and adapts to your theme

## Installation

1. Upload the `upsite-opening-hours` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Settings → Opening Hours** to configure

## Usage

### Admin Interface

Navigate to **Settings → Opening Hours** to access:

1. **Calendar Manager** - Interactive calendar to manage opening hours
   - Click any date to add/edit opening hours
   - Set opening and closing times
   - Add special notes (e.g., "חנוכה")
   - Enable/disable specific dates

2. **Color Settings** - Customize appearance colors
   - Enabled dates background color
   - Disabled dates background color
   - Text color
   - Special days highlight color
   - Primary accent color

3. **General Settings** - Default times
   - Default opening time
   - Default closing time

### Shortcodes

#### Calendar Widget (Interactive Monthly Calendar)
```
[upsite_hours_calendar]
```

Display a full interactive calendar widget with month navigation. Shows one month at a time with prev/next buttons. Click on enabled dates to see opening hours in a modal.

**Parameters:**
- `year="2025"` - Start from specific year (default: current year)
- `month="12"` - Start from specific month 1-12 (default: current month)
- `months="3"` - Number of months available for navigation (default: 3)
- `title=""` - Custom title or empty to hide (default: "Opening Hours")

**Examples:**
```
[upsite_hours_calendar]
[upsite_hours_calendar year="2025" month="12"]
[upsite_hours_calendar year="2025" month="11" months="4"]
[upsite_hours_calendar title=""]
[upsite_hours_calendar title="Park Schedule"]
```

#### List View (Compact List)
```
[upsite_hours_list]
```

Display opening hours as a compact, minimal list grouped by month.

**Parameters:**
- `year="2025"` - Filter by year
- `month="12"` - Filter by month (1-12)
- `limit="10"` - Limit number of entries
- `title=""` - Custom title or empty to hide (default: "Opening Hours")

**Examples:**
```
[upsite_hours_list year="2025"]
[upsite_hours_list year="2025" month="12"]
[upsite_hours_list title=""]
[upsite_hours_list title="Schedule" year="2025"]
```

### REST API Endpoints

All endpoints are available at `/wp-json/upsite-hours/v1/`

#### Public Endpoints

- `GET /dates` - Get all opening hours
  - Parameters: `year`, `month`, `enabled_only`, `limit`
- `GET /dates/{date}` - Get specific date (format: YYYY-MM-DD)
- `GET /settings` - Get color and default settings

#### Admin Endpoints (require `manage_options` capability)

- `POST /dates` - Create or update opening hours
  - Body: `date`, `opening_time`, `closing_time`, `special_note`, `is_enabled`
- `DELETE /dates/{date}` - Delete opening hours for a date

## Technical Details

### Database

Creates a custom table `wp_upsite_opening_hours` with the following structure:
- `id` - Primary key
- `date` - DATE field (YYYY-MM-DD)
- `opening_time` - TIME field (HH:MM:SS)
- `closing_time` - TIME field (HH:MM:SS)
- `special_note` - VARCHAR(255) for holidays/special days
- `is_enabled` - BOOLEAN (default 1)
- `created_at`, `updated_at` - TIMESTAMP fields

### RTL Support

The plugin fully supports RTL languages using CSS logical properties:
- `margin-inline-start` / `margin-inline-end` instead of left/right
- `gap` for flex layouts instead of `space-x`
- Proper RTL handling for Hebrew text

### Security

- All inputs are sanitized
- All outputs are escaped
- Prepared statements for database queries
- Nonce verification for admin forms
- Capability checks (`manage_options`)
- REST API authentication via WordPress nonces

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Author

Upsite - https://upsiteapp.co.il

## Version

1.0.0

## License

Proprietary - All rights reserved

