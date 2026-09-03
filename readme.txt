=== BannerMonster ===
Contributors: inCod, marcods, ideagrafica
Tags: banner, popup, modal, marketing, conversion
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create accessible banners and popups with advanced targeting, WCAG 2.2 compliance, and native HTML dialog.

== Description ==

BannerMonster lets you create custom banners and popups for your WordPress site with advanced targeting rules.

**Types:**

* Top banner (fixed bar at top)
* Bottom banner (fixed bar at bottom)
* Center popup
* Bottom-right popup
* Bottom-left popup

**Display rules:**

* All pages
* All posts
* All custom post types
* Specific pages, posts or CPTs
* Specific URLs (partial match, case-insensitive, trailing-slash tolerant)
* Taxonomies (categories, tags, custom taxonomies)

**Triggers:**

* Immediate
* After X seconds
* Exit intent (mouse leaves viewport)
* At X% page scroll

**Reappearance control:**

* Set how many minutes after closing a banner should reappear
* 0 = never reappear after closing
* Close state stored in browser localStorage, persists across sessions

**Customization:**

* Background, text and border colors with color pickers
* Padding, font size, border width
* Width (%) and max-width (px) for popups
* Custom CSS classes per banner
* Custom CSS per banner (sanitized on save)
* Close button with configurable persistence
* Overlay for popups with optional close-on-click

**Accessibility (WCAG 2.2):**

* Native HTML `<dialog>` element for popups — focus trap, Escape key, backdrop managed by the browser
* `role="dialog"` and `aria-modal="true"` applied automatically
* `aria-label` on each dialog using the banner title
* Keyboard-only navigation: Escape to close, Tab cycling trapped inside popup
* Backdrop click closes popup (configurable)
* `:focus-visible` styling for keyboard users
* Screen reader compatible (VoiceOver, NVDA, JAWS)

**Debug mode:**

* Append ?bm_debug=1 to any URL to bypass localStorage persistence
* Banner appears on every page load regardless of close state

**Zero dependencies** on the frontend. jQuery is only used in the admin area.

**Performance:**

* Frontend assets loaded only when active banners exist on the page
* Vanilla JavaScript, no frameworks
* WordPress object cache for database queries
* Admin assets loaded only on BannerMonster screens

== Installation ==

1. Upload the `bannermonster` folder to `/wp-content/plugins/`
2. Activate the plugin through the Plugins menu in WordPress
3. Go to the BannerMonster menu in your dashboard
4. Create your first banner or popup

== Frequently Asked Questions ==

= Does the plugin load any external scripts? =

No. The frontend uses vanilla JavaScript with zero dependencies. No Google Fonts, no analytics, no tracking, no CDN-loaded libraries. jQuery is never loaded on the public side.

= How does the close button work? =

When a visitor closes a banner or popup, the plugin stores the ID and timestamp in the browser's localStorage. On subsequent page views, BannerMonster checks this and skips banners still within the configured reappear period. The data stays entirely in the visitor's browser.

= How does the reappear control work? =

In the Trigger metabox, set "Ricompari dopo (min)" to the number of minutes after which the banner should show again. Set to 0 to never show again after closing. The close state is stored locally in the browser with a timestamp, so it persists across page views and sessions.

= Can I show a banner only on specific pages? =

Yes. In the display rules you can choose from: all pages, all posts, all CPTs, specific pages, specific posts, specific CPTs, specific URLs, or specific taxonomy terms. URL matching is flexible: enter a full URL or just a path, and BannerMonster handles trailing slashes, case differences and protocol variations.

= How does debug mode work? =

Add ?bm_debug=1 to any URL on your site. When this parameter is present, all banners appear on every page load regardless of localStorage state. Useful during development and testing. A notice appears in the admin metabox when debug mode is active.

= Does it work with page builders like Elementor, Divi or WPBakery? =

Yes. BannerMonster uses high-specificity CSS selectors with !important declarations to ensure your banner styles are applied regardless of what your theme or page builder does.

= Can I use custom CSS? =

Yes. Each banner has a CSS Class field for adding classes, and a Custom CSS field for writing CSS scoped to that specific banner. The custom CSS is sanitized on save to prevent XSS.

= Does it conflict with caching plugins? =

No. BannerMonster uses standard WordPress hooks. The banner HTML is rendered in the footer, and JavaScript handles visibility based on localStorage. Page caching plugins work normally.

= How many banners can I create? =

There is no limit. BannerMonster uses a custom post type. The plugin loads up to 50 active banners per page view.

= Does it support WooCommerce product categories? =

Yes. If your WooCommerce product categories are registered as a public taxonomy, you can target banners to specific product categories using the taxonomy display rule.

= Is the plugin translation-ready? =

Yes. All user-facing strings use WordPress translation functions with the 'bannermonster' text domain.

== Screenshots ==

1. Banner type and configuration settings
2. Display rules with targeting options
3. Trigger configuration with reappear control
4. Style and customization options

== Changelog ==

= 1.5.0 =
* Migrated popups and banners from `<div>` to native HTML `<dialog>` element.
* Full WCAG 2.2 compliance: focus trap, Escape key, backdrop click, aria-label.
* Removed manual overlay DOM element — now using `::backdrop` pseudo-element.
* Removed `show()`, `hide()`, `bindEvents()` JavaScript functions.
* Added `setupBackdropClose()` for native dialog close and cancel events.
* Added `isModal()` helper for popup detection.
* Simplified CSS: removed `.bm-overlay`, `.bm-wrap`, `.bm-visible` selectors.
* Reduced frontend JS from ~169 to ~189 lines (net ~20 more, but removed 3 functions).
* Reduced frontend CSS from 157 to 176 lines.
* Removed `data-overlay` and `data-close` data attributes.
* Added `backdrop_close` to localized script data.
* Close button now wrapped in `<form method="dialog">` for native close behavior.
* Banners use `open` attribute; popups use `showModal()` for focus trap.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.5.0 =
Accessibility update: popups and banners now use native HTML `<dialog>` for full WCAG 2.2 compliance.

= 1.0.0 =
First release.
