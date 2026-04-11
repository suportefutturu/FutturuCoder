# Simple Centered Popup - Development Documentation

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [File Structure](#file-structure)
3. [Installation & Activation](#installation--activation)
4. [Usage Examples](#usage-examples)
5. [Hooks & Filters](#hooks--filters)
6. [Testing Guide](#testing-guide)
7. [Security Considerations](#security-considerations)
8. [Extensibility](#extensibility)
9. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

### Design Decisions

**Settings API Approach:**
This plugin uses WordPress Settings API instead of Custom Post Types for the following reasons:

1. **Simplicity**: Single popup per site is the most common use case
2. **Performance**: Fewer database queries (options vs. posts)
3. **User Experience**: All settings in one place, easier for non-technical users
4. **Maintenance**: Less code complexity, easier to maintain

**Future Multi-Popup Support:**
The architecture supports future migration to CPT by:
- Using modular template system
- Separating data retrieval from rendering
- Including `$id` parameter in shortcode and `sc_popup_render()` function

### Component Breakdown

```
simple-centered-popup/
├── simple-centered-popup.php    # Main plugin file (bootstrap, class, hooks)
├── readme.txt                    # WordPress.org readme format
├── README.md                     # Developer documentation
├── assets/
│   ├── css/
│   │   └── style.css            # Frontend styles
│   └── js/
│       └── script.js            # Frontend JavaScript (vanilla ES6+)
└── templates/
    └── popup.php                 # Popup HTML template
```

---

## File Structure

### Core Files

#### `simple-centered-popup.php`
Main plugin file containing:
- Plugin header and constants
- `Simple_Centered_Popup` class (singleton pattern)
- Settings API registration
- Admin menu and page rendering
- Asset enqueueing logic
- AJAX handlers
- Shortcode registration
- Public function `sc_popup_render()`

#### `assets/css/style.css`
Frontend styles featuring:
- CSS custom properties for dynamic theming
- Responsive breakpoints (768px, 480px)
- Animation variants (fade, scale, slide)
- Accessibility features (focus states, reduced motion)
- Print media query (hides popup)

#### `assets/js/script.js`
Frontend JavaScript with:
- ES6 class-based architecture (`PopupManager`)
- LocalStorage + Cookie fallback
- Focus trapping for accessibility
- Keyboard navigation (Tab, Escape)
- Screen reader announcements
- AJAX dismissal tracking

#### `templates/popup.php`
HTML template with:
- ARIA roles and labels
- Dynamic inline styles from options
- Sanitized output
- Conditional content rendering

---

## Installation & Activation

### Manual Installation

1. Download the plugin folder
2. Upload to `/wp-content/plugins/simple-centered-popup/`
3. Activate via WordPress admin → Plugins
4. Configure settings at **Simple Popup** menu

### Programmatic Installation (WP-CLI)

```bash
wp plugin install ./simple-centered-popup.zip --activate
```

### Activation Hook Behavior

On activation, the plugin:
1. Sets default option `scp_enabled` to `true` if not exists
2. Flushes rewrite rules

### Deactivation Hook Behavior

On deactivation:
1. Flushes rewrite rules
2. Does NOT delete options (preserves settings for reactivation)

---

## Usage Examples

### Shortcode Usage

**Basic:**
```php
[sc_popup]
```

**With ID (reserved for future multi-popup):**
```php
[sc_popup id="1"]
```

**In theme files:**
```php
<?php echo do_shortcode('[sc_popup]'); ?>
```

### PHP Function Usage

**Basic:**
```php
<?php sc_popup_render(); ?>
```

**With ID (reserved):**
```php
<?php sc_popup_render('1'); ?>
```

**Conditional display in theme:**
```php
<?php
if ( is_front_page() ) {
    sc_popup_render();
}
?>
```

### JavaScript Manual Control

**Open popup programmatically:**
```javascript
window.SCPPopup.open();
```

**Close popup programmatically:**
```javascript
window.SCPPopup.close();
```

**Trigger after user action:**
```javascript
document.querySelector('.my-button').addEventListener('click', function() {
    window.SCPPopup.open();
});
```

### Custom Styling Override

Add to your theme's `style.css`:
```css
/* Override popup max width */
.scp-popup {
    max-width: 800px !important;
}

/* Custom button style */
.scp-button {
    background-color: #ff6600 !important;
}

/* Hide on mobile */
@media (max-width: 768px) {
    .scp-overlay {
        display: none !important;
    }
}
```

---

## Hooks & Filters

### Available Filters

Currently, the plugin uses direct option retrieval. For extensibility, you can filter options:

```php
// Filter popup title
add_filter('option_scp_title', function($title) {
    return 'Custom Title: ' . $title;
});

// Filter content before display
add_filter('option_scp_content', function($content) {
    return $content . '<p>Additional content</p>';
});
```

### Future Hook System (Planned)

For version 2.0, consider adding:

```php
// Before popup renders
do_action('scp_before_popup_render', $popup_id);

// After popup renders
do_action('scp_after_popup_render', $popup_id);

// Filter popup data
$popup_data = apply_filters('scp_popup_data', $data, $popup_id);

// Modify display conditions
if (apply_filters('scp_should_show', true, $popup_id)) {
    // render popup
}
```

### AJAX Endpoint

**Action:** `scp_dismiss_popup`

**Request:**
```javascript
POST /wp-admin/admin-ajax.php
{
    action: 'scp_dismiss_popup',
    nonce: 'xxx'
}
```

---

## Testing Guide

### Manual Testing Checklist

#### Functional Tests

- [ ] Popup appears on page load (auto-open enabled)
- [ ] Popup respects delay setting
- [ ] Close button (X) closes popup
- [ ] Clicking overlay closes popup
- [ ] ESC key closes popup
- [ ] Button with URL navigates correctly
- [ ] Button with new tab opens in new tab
- [ ] Frequency setting prevents repeat views
- [ ] Popup shows on homepage (when enabled)
- [ ] Popup shows on posts (when enabled)
- [ ] Popup shows on pages (when enabled)
- [ ] Disabled popup doesn't show anywhere

#### Content Tests

- [ ] Title displays correctly
- [ ] HTML content renders properly
- [ ] Image displays with correct alt text
- [ ] YouTube embed works
- [ ] Vimeo embed works
- [ ] Self-hosted video plays

#### Design Tests

- [ ] Max width is respected
- [ ] Overlay opacity is correct
- [ ] Background color matches setting
- [ ] Button color matches setting
- [ ] Border radius is applied
- [ ] Fade animation works
- [ ] Scale animation works
- [ ] Slide animation works
- [ ] Animation duration is correct

#### Accessibility Tests

- [ ] Tab navigation cycles through focusable elements
- [ ] Shift+Tab reverses focus order
- [ ] ESC key closes popup
- [ ] Focus returns to trigger element on close
- [ ] Screen reader announces popup open/close
- [ ] ARIA attributes are present
- [ ] Reduced motion preference is respected
- [ ] Color contrast meets WCAG AA

#### Responsive Tests

- [ ] Desktop (1920px): Layout correct
- [ ] Laptop (1366px): Layout correct
- [ ] Tablet (768px): Responsive breakpoint triggers
- [ ] Mobile (480px): Small screen layout correct
- [ ] Touch interactions work on mobile

#### Browser Compatibility

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

#### Performance Tests

- [ ] Assets only load when popup is enabled
- [ ] No console errors
- [ ] No jQuery dependency
- [ ] PageSpeed Insights score acceptable
- [ ] Lighthouse accessibility score 100

#### Security Tests

- [ ] Nonce verification on AJAX
- [ ] Input sanitization (title, content, URLs)
- [ ] Output escaping (all echo statements)
- [ ] No XSS vulnerabilities
- [ ] CSRF protection active
- [ ] Capability checks on admin pages

### Automated Testing Setup

#### PHPUnit Tests (Example)

Create `tests/php/test-popup.php`:

```php
<?php
class PopupTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        // Activate plugin
        update_option('scp_enabled', true);
        update_option('scp_title', 'Test Title');
    }

    public function test_popup_enabled_option() {
        $this->assertTrue(get_option('scp_enabled'));
    }

    public function test_popup_title_sanitization() {
        update_option('scp_title', '<script>alert("xss")</script>Test');
        $title = get_option('scp_title');
        $this->assertEquals('Test', $title);
    }

    public function test_shortcode_exists() {
        $this->assertTrue(shortcode_exists('sc_popup'));
    }

    public function test_function_exists() {
        $this->assertTrue(function_exists('sc_popup_render'));
    }

    public function test_popup_not_shown_when_disabled() {
        update_option('scp_enabled', false);
        $output = do_shortcode('[sc_popup]');
        $this->assertEmpty($output);
    }
}
```

#### JavaScript Tests (Example with Jest)

Create `tests/js/popup.test.js`:

```javascript
import { PopupManager } from '../../assets/js/script';

describe('PopupManager', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="scp-popup-overlay" class="scp-overlay">
                <div class="scp-popup">
                    <button class="scp-close-btn"></button>
                </div>
            </div>
        `;
        global.scpConfig = {
            autoOpen: false,
            delay: 1000,
            frequencyDays: 7,
            cookieName: 'scp_popup_shown'
        };
    });

    test('should initialize', () => {
        const manager = new PopupManager();
        expect(manager).toBeDefined();
    });

    test('should open popup', () => {
        const manager = new PopupManager();
        manager.open();
        expect(document.querySelector('.scp-overlay').classList.contains('active')).toBe(true);
    });

    test('should close popup on ESC', () => {
        const manager = new PopupManager();
        manager.open();
        const escEvent = new KeyboardEvent('keydown', { key: 'Escape' });
        document.dispatchEvent(escEvent);
        expect(document.querySelector('.scp-overlay').classList.contains('active')).toBe(false);
    });
});
```

### Running Tests

**PHPUnit:**
```bash
cd /path/to/wordpress/wp-content/plugins/simple-centered-popup
phpunit tests/php/
```

**Jest:**
```bash
npm install --save-dev jest
npx jest tests/js/
```

---

## Security Considerations

### Implemented Security Measures

1. **Input Sanitization**
   ```php
   sanitize_text_field()    // Text inputs
   sanitize_hex_color()     // Color pickers
   esc_url_raw()           // URLs
   wp_kses_post()          // HTML content
   absint()                // Integers
   floatval()              // Floats
   rest_sanitize_boolean() // Booleans
   ```

2. **Output Escaping**
   ```php
   esc_html()      // HTML content
   esc_attr()      // Attributes
   esc_url()       // URLs
   esc_js()        // JavaScript
   ```

3. **Nonce Verification**
   ```php
   // Create nonce
   wp_create_nonce('scp_nonce');

   // Verify nonce
   check_ajax_referer('scp_nonce', 'nonce');
   ```

4. **Capability Checks**
   ```php
   if (!current_user_can('manage_options')) {
       return;
   }
   ```

5. **Direct Access Prevention**
   ```php
   if (!defined('ABSPATH')) {
       exit;
   }
   ```

### Security Audit Checklist

- [x] All user inputs sanitized
- [x] All outputs escaped
- [x] Nonces used for AJAX
- [x] Capability checks on admin pages
- [x] No direct file access
- [x] SQL injection prevented (using Options API)
- [x] XSS prevention (wp_kses_post for HTML)
- [x] CSRF protection (nonces)
- [x] SameSite cookie attribute set

---

## Extensibility

### Adding New Features

#### Example: Add Scheduling Feature

1. **Add new setting in `register_settings()`:**
   ```php
   register_setting('scp_settings_group', 'scp_schedule_start', array(
       'type' => 'string',
       'sanitize_callback' => 'sanitize_text_field',
   ));
   ```

2. **Add field in admin:**
   ```php
   add_settings_field(
       'scp_schedule_start',
       __('Start Date', 'simple-centered-popup'),
       array($this, 'render_text_field'),
       'simple-centered-popup',
       'scp_behavior_section',
       array('label_for' => 'scp_schedule_start', 'option_name' => 'scp_schedule_start')
   );
   ```

3. **Check in `should_show_popup()`:**
   ```php
   private function should_show_popup() {
       // ... existing checks

       $start = get_option('scp_schedule_start');
       if ($start && strtotime($start) > time()) {
           return false;
       }

       return true;
   }
   ```

#### Example: Add A/B Testing

1. **Create variant storage:**
   ```php
   // In main plugin file
   function scp_get_variant($popup_id) {
       $variants = array('A', 'B');
       $user_hash = md5($_SERVER['REMOTE_ADDR']);
       $index = hexdec(substr($user_hash, 0, 1)) % count($variants);
       return $variants[$index];
   }
   ```

2. **Filter content based on variant:**
   ```php
   add_filter('option_scp_title', function($title) {
       $variant = scp_get_variant(1);
       $titles = array(
           'A' => 'Welcome!',
           'B' => 'Special Offer!'
       );
       return $titles[$variant] ?? $title;
   });
   ```

#### Example: Add Analytics Integration

1. **Track impressions:**
   ```javascript
   // In script.js, after open()
   trackAnalytics('popup_impression', {
       popup_id: 1,
       timestamp: Date.now()
   });
   ```

2. **Track conversions:**
   ```javascript
   // On button click
   trackAnalytics('popup_conversion', {
       popup_id: 1,
       button_url: button.href
   });
   ```

### Creating Add-ons

Structure for add-on plugins:

```
simple-centered-popup-pro/
├── simple-centered-popup-pro.php
├── includes/
│   ├── class-scp-pro-features.php
│   └── class-scp-analytics.php
└── assets/
    ├── css/
    └── js/
```

Hook into main plugin:

```php
// Check if main plugin is active
if (class_exists('Simple_Centered_Popup')) {
    // Add pro features
    add_action('scp_before_popup_render', 'scp_pro_add_custom_fields');
}
```

---

## Troubleshooting

### Common Issues

#### Popup Not Showing

**Check:**
1. Is popup enabled in settings?
2. Are visibility settings correct for current page?
3. Has user already seen popup (check localStorage)?
4. Are there JavaScript errors in console?

**Debug:**
```javascript
console.log(localStorage.getItem('scp_popup_shown'));
console.log(scpConfig);
```

#### Styles Not Loading

**Check:**
1. Is `wp_head()` in theme?
2. Are there CSS conflicts?
3. Is caching preventing asset updates?

**Fix:**
```php
// Clear browser cache
// Or add version bump
define('SCP_VERSION', '1.0.1');
```

#### AJAX Not Working

**Check:**
1. Is nonce valid?
2. Is admin-ajax.php accessible?
3. Are there CORS issues?

**Debug:**
```javascript
fetch(scpConfig.ajaxUrl, {
    method: 'POST',
    body: new FormData()
}).then(r => r.json()).then(console.log);
```

#### Accessibility Issues

**Check:**
1. Is focus trapped correctly?
2. Are ARIA attributes present?
3. Does screen reader announce changes?

**Test:**
- Use keyboard only (no mouse)
- Enable screen reader (NVDA, VoiceOver)
- Check with axe DevTools extension

### Performance Optimization

If popup causes slowdown:

1. **Defer JavaScript:**
   ```php
   wp_script_add_data('scp-script', 'defer', true);
   ```

2. **Inline Critical CSS:**
   ```php
   wp_add_inline_style('scp-style', $critical_css);
   ```

3. **Lazy Load Assets:**
   ```javascript
   // Only load when needed
   if (shouldShowPopup()) {
       loadAssets();
   }
   ```

### Support Resources

- WordPress.org Support Forum
- GitHub Issues (if open source)
- Documentation: `/README.md`
- Code comments (inline documentation)

---

## Version History

### 1.0.0 (Current)
- Initial release
- All core features implemented
- Full accessibility support
- Translation ready

### Planned for 2.0
- Multiple popups support (CPT)
- Advanced scheduling
- A/B testing
- Analytics integration
- More animation options
- Template library

---

## Contributing

To contribute to this plugin:

1. Fork the repository
2. Create feature branch
3. Follow WordPress Coding Standards
4. Write tests
5. Submit pull request

### Coding Standards

- WordPress PHP Coding Standards
- PSR-12 for OOP code
- ES6+ for JavaScript
- BEM methodology for CSS

### Commit Messages

```
feat: add new animation type
fix: resolve focus trap issue
docs: update README with examples
test: add PHPUnit tests for settings
chore: update dependencies
```

---

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html
