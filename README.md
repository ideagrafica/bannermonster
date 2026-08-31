# BannerMonster

Create custom banners and popups for your WordPress site with advanced targeting rules.

## Features

- **5 types:** Top banner, bottom banner, center popup, bottom-right popup, bottom-left popup
- **8 display rules:** All pages, all posts, all CPTs, specific pages/posts/CPTs, specific URLs, taxonomies
- **4 triggers:** Immediate, timer, scroll percentage, exit intent
- **Reappearance control:** Set how many minutes after closing a banner should reappear
- **Full styling:** Colors, padding, font size, border, custom CSS per banner
- **Debug mode:** Append `?bm_debug=1` to bypass localStorage persistence
- **Zero frontend dependencies:** Vanilla JavaScript, no jQuery on the public side
- **Performance:** Assets loaded only when active banners exist on the page

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
    ├── css/frontend.css                  # Frontend styles
    └── js/frontend.js                    # Frontend JS (vanilla)
```

### Hooks

The plugin provides standard WordPress hooks for extensibility. All meta keys are prefixed with `bm_`.

## Changelog

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
