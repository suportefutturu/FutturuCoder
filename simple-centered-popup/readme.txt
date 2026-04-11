=== Simple Centered Popup ===
Contributors: yourname
Tags: popup, modal, newsletter, optin, marketing, overlay, lightbox
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A reusable, customizable modal popup plugin with admin controls, cookie-based frequency settings, and accessibility features.

== Description ==

**Simple Centered Popup** is a lightweight, production-ready WordPress plugin that adds a beautiful, centered modal popup to your website. Perfect for newsletters, announcements, promotions, or any content you want to highlight to your visitors.

= Features =

* **Fully Customizable Content**
  * Editable title and HTML/WYSIWYG content
  * Support for images (upload/URL)
  * Support for videos (YouTube, Vimeo, embed, self-hosted)
  * Configurable call-to-action button with URL and new tab option

* **Smart Display Controls**
  * Auto-open on page load (configurable)
  * Configurable delay before showing
  * Frequency control: show once per visitor per X days
  * Conditional display: homepage, posts, pages

* **Design Customization**
  * Configurable max width
  * Overlay opacity control
  * Custom background and button colors
  * Border radius settings
  * Three animation types: Fade, Scale, Slide
  * Adjustable animation duration

* **Accessibility First**
  * Full keyboard navigation (Tab, Escape)
  * ARIA roles and labels
  * Focus trapping within popup
  * Screen reader announcements
  * Respects reduced motion preferences

* **Performance Optimized**
  * Vanilla JavaScript (no jQuery dependency)
  * Assets loaded only when needed
  * LocalStorage + Cookie fallback for frequency control
  * Minimal database queries

* **Developer Friendly**
  * Shortcode: `[sc_popup]`
  * PHP function: `sc_popup_render()`
  * AJAX endpoint for dismissal tracking
  * Well-documented code
  * Translation ready

= Requirements =

* WordPress 5.8 or higher
* PHP 7.4 or higher

= Installation =

1. Upload the `simple-centered-popup` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Simple Popup** in the admin menu to configure settings
4. The popup will automatically appear on your site based on your settings

= Usage =

**Admin Configuration:**
1. Navigate to **Simple Popup** in your WordPress admin
2. Configure your popup content, behavior, and design settings
3. Save changes

**Shortcode:**
Use `[sc_popup]` in any post, page, or widget to display the popup programmatically.

**PHP Function:**
Add `<?php sc_popup_render(); ?>` in your theme files where you want the popup to appear.

**Manual Control (JavaScript):**
```javascript
// Open popup manually
window.SCPPopup.open();

// Close popup manually
window.SCPPopup.close();
```

== Frequently Asked Questions ==

= How do I prevent the popup from showing on every page load? =

Set the "Show Again After (Days)" option to your desired frequency. The default is 7 days. Set to 0 to show on every page load.

= Can I use custom HTML in the popup content? =

Yes! The content field accepts HTML. You can add formatted text, lists, links, and other HTML elements.

= Does this work with caching plugins? =

Yes, the popup uses client-side storage (LocalStorage/Cookie) to track views, so it works correctly even with page caching enabled.

= Is the popup mobile-friendly? =

Absolutely! The popup is fully responsive and adapts to all screen sizes. It includes touch-friendly interactions and optimized layouts for mobile devices.

= How do I add a video? =

Paste the embed code from YouTube, Vimeo, or any video provider in the "Video Embed Code" field. The plugin will handle the responsive embedding automatically.

= Can I disable the popup on specific pages? =

Yes! Use the visibility settings to control where the popup appears (homepage, posts, pages). For more granular control, you can use conditional tags in your theme or create custom page templates.

== Changelog ==

= 1.0.0 - 2024-01-15 =
* Initial release
* Complete admin settings panel
* Frontend popup with animations
* Cookie/LocalStorage frequency control
* Accessibility features (keyboard nav, ARIA, focus trap)
* Responsive design
* Shortcode and PHP function support
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade necessary.

== Screenshots ==

1. Admin settings panel with all configuration options
2. Popup displayed on frontend with sample content
3. Mobile responsive view

== License ==

This plugin is licensed under GPL v2 or later.

== Credits ==

Developed by Your Name
Follow best practices for WordPress plugin development including:
* WordPress Coding Standards
* Security best practices (sanitization, escaping, nonces)
* Accessibility guidelines (WCAG 2.1)
* Performance optimization
