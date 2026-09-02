# BannerMonster

Create accessible banners and popups for your WordPress site with advanced targeting rules and WCAG 2.2 compliance.

## Features

- **5 types:** Top banner, bottom banner, center popup, bottom-right popup, bottom-left popup
- **8 display rules:** All pages, all posts, all CPTs, specific pages/posts/CPTs, specific URLs, taxonomies
- **4 triggers:** Immediate, timer, scroll percentage, exit intent
- **Reappearance control:** Set how many minutes after closing a banner should reappear
- **Full styling:** Colors, padding, font size, border, custom CSS per banner
- **WCAG 2.2 accessible:** Native `<dialog>` element with focus trap, Escape key, ARIA attributes
- **Debug mode:** Append `?bm_debug=1` to bypass localStorage persistence
- **Zero frontend dependencies:** Vanilla JavaScript, no jQuery on the public side
- **Performance:** Assets loaded only when active banners exist on the page

## Accessibility (WCAG 2.2)

BannerMonster uses the native HTML `<dialog>` element for popups, providing:

- **Focus trap** — managed by the browser, Tab cycling stays inside the popup
- **Escape key** — closes popups natively via the `cancel` event
- **Backdrop click** — `::backdrop` pseudo-element replaces the manual overlay div
- **ARIA attributes** — `role="dialog"`, `aria-modal="true"`, and `aria-label` applied automatically
- **Keyboard navigation** — close button uses `<form method="dialog">` for native activation
- **Focus visible** — `:focus-visible` styling ensures keyboard users see focus indicators
- **Screen reader compatible** — works with VoiceOver, NVDA, and JAWS

## Installation

### From WordPress Admin

1. Go to **Plugins > Add New > Upload Plugin**
2. Upload `bannermonster.zip`
3. Click **Install Now** and then **Activate**

### Manual

1. Upload the `bannermonster` folder to `/wp-content/plugins/`
2. Activate through the **Plugins** menu in WordPress
3. Go to **BannerMonster** in your dashboard

## Usage

1. Go to **BannerMonster > Add New**
2. Write your banner/popup content using the WordPress editor
3. Choose a type (banner or popup)
4. Set display rules (where to show)
5. Configure triggers (when to show)
6. Customize the style (colors, padding, etc.)
7. Publish

## Debug Mode

Append `?bm_debug=1` to any URL to bypass the localStorage persistence. The banner will appear on every page load regardless of whether the visitor has closed it before.

## Development

### Requirements

- PHP 7.4+
- WordPress 5.8+

### File Structure

```
bannermonster/
├── bannermonster.php              # Main plugin file
├── includes/
│   ├── class-bannermonster-cpt.php       # Custom post type
│   ├── class-bannermonster-admin.php     # Admin metaboxes and save
│   └── class-bannermonster-frontend.php  # Frontend rendering
├── admin/
│   ├── css/admin.css                     # Admin styles
│   └── js/admin.js                       # Admin JS (jQuery)
└── public/
    ├── css/frontend.css                  # Frontend styles (WCAG 2.2 / dialog)
    └── js/frontend.js                    # Frontend JS (vanilla, dialog API)
```

### How It Works

**Banners** (top/bottom) use `<dialog open>` — always visible, no focus trap, no backdrop.

**Popups** (center, bottom-right, bottom-left) use `<dialog>` with `showModal()`:
- Browser manages focus trap and backdrop
- Escape key triggers the `cancel` event (saved to localStorage before close)
- Backdrop click detected via `e.target === dialog`
- Close button uses `<form method="dialog">` for native close

### Hooks

The plugin provides standard WordPress hooks for extensibility. All meta keys are prefixed with `bm_`.

## Changelog

### 1.5.0
- Migrated popups and banners from `<div>` to native HTML `<dialog>` element
- Full WCAG 2.2 compliance: focus trap, Escape key, backdrop click, aria-label
- Removed manual overlay DOM element — now using `::backdrop` pseudo-element
- Removed `show()`, `hide()`, `bindEvents()` JavaScript functions
- Added `setupBackdropClose()` for native dialog close and cancel events
- Added `isModal()` helper for popup detection
- Simplified CSS: removed `.bm-overlay`, `.bm-wrap`, `.bm-visible` selectors
- Close button now wrapped in `<form method="dialog">` for native close behavior
- Banners use `open` attribute; popups use `showModal()` for focus trap

### 1.0.0
- Initial release

## License

GPL-2.0+ - See [LICENSE](LICENSE) for details.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
