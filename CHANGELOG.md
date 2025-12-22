# Changelog

All notable changes to the Upsite Opening Hours plugin will be documented in this file.

## [1.2.0] - 2025-12-04

### Changed
- Calendar dates now display as full circles (dark purple for open, light purple for closed)
- Removed "חודש" prefix from month display (now shows "דצמבר 2025" instead of "חודש דצמבר 2025")
- Added legend below calendar showing open/closed status with purple circles
- Updated hover effects to match new circular design

## [1.1.0] - 2025-12-04

### Added
- Date range selector in admin for bulk adding multiple dates at once
- iOS-style calendar design with small circular dots for enabled/special dates
- Support for showing disabled dates with custom text/notes
- Hebrew locale throughout admin and frontend interfaces
- RTL support for admin calendar

### Changed
- Calendar widget now shows one month at a time with prev/next navigation
- Modal popup redesigned with iOS-style frosted glass effect
- List view redesigned with iOS Settings-style grouped cards
- Time display in RTL order (closing-opening) for Hebrew
- Improved responsive design for mobile devices
- Reduced calendar size and fixed overflow issues

### Fixed
- Hover effects now work correctly in RTL direction
- Calendar cells properly sized to prevent overflow
- Modal now shows special notes for both enabled and disabled dates

## [1.0.0] - 2025-12-03

### Added
- Initial release
- Interactive admin calendar interface for managing opening hours
- Color customization settings (5 customizable colors)
- Two shortcodes: `[upsite_hours_calendar]` (full calendar widget) and `[upsite_hours_list]` (text list)
- Full Hebrew language support with RTL compatibility
- Calendar widget displays full monthly grid with all dates
- Enabled dates highlighted with opening hours shown inline
- REST API endpoints for CRUD operations
- Database table for storing opening hours
- Support for special days/holidays with custom notes
- Responsive design that adapts to themes
- Default opening/closing time settings
- Modal-based date editor in admin
- Month/year navigation in admin calendar
- Date filtering by year, month, and limit parameters
- Visual color coding for enabled, disabled, and special dates
- Comprehensive documentation (README, SHORTCODES guide)

### Features
- **Admin Interface**
  - Calendar Manager tab with interactive month view
  - Color Settings tab with WordPress color pickers
  - General Settings tab for default times
  - Shortcodes reference tab
  - Click dates to add/edit opening hours
  - Delete functionality for dates
  
- **Frontend Display**
  - Calendar view with card-based layout
  - List view with simple text layout
  - Automatic Hebrew day and month names
  - Color customization via CSS custom properties
  - RTL support using logical CSS properties
  - Responsive grid layouts
  - Print-friendly styles
  - Dark mode support

- **REST API**
  - `GET /wp-json/upsite-hours/v1/dates` - Get all dates
  - `GET /wp-json/upsite-hours/v1/dates/{date}` - Get specific date
  - `POST /wp-json/upsite-hours/v1/dates` - Create/update date
  - `DELETE /wp-json/upsite-hours/v1/dates/{date}` - Delete date
  - `GET /wp-json/upsite-hours/v1/settings` - Get settings

### Security
- All inputs sanitized and outputs escaped
- Prepared statements for database queries
- Nonce verification for admin actions
- Capability checks (`manage_options`)
- REST API authentication

### Technical
- PHP 7.4+ compatible
- WordPress 5.0+ compatible
- Object-oriented architecture
- Singleton pattern for main classes
- Autoloader for classes
- No external dependencies
- Follows WordPress coding standards

