# WooCommerce Adventist Store Lock

A WooCommerce plugin that blocks purchases during a recurring weekly time window and displays a native modal to visitors while the store is temporarily unavailable.

Built for a real-world use case where the store owner needed to prevent purchases from **Friday at 6:00 PM to Saturday at 6:00 PM**.

## Features

- Blocks purchases during a configurable weekly time window
- Native modal built into the plugin
- Admin settings panel inside **WooCommerce > Store Lock**
- Configurable:
  - start day and time
  - end day and time
  - modal title
  - modal message
  - button label
  - close button visibility
  - page interaction blocking
  - colors
  - custom CSS
- Prevents:
  - add to cart
  - product purchases
  - checkout continuation
- Adds a **Settings** link directly on the Plugins screen

## Why this plugin exists

This plugin was created to solve a practical business rule: keep the storefront visible, but temporarily prevent purchases during a recurring weekly schedule.

The idea was to separate:

- the **visual layer**: a modal communicating the temporary restriction
- the **functional layer**: actual purchase blocking in WooCommerce

## Admin panel

After activation, settings are available at:

**WooCommerce > Store Lock**

## Installation

1. Download the plugin ZIP
2. Go to **Plugins > Add New**
3. Click **Upload Plugin**
4. Upload the ZIP file
5. Activate the plugin
6. Go to **WooCommerce > Store Lock**
7. Configure the lock schedule and modal content

## Example use case

A store owner wants the website to remain online, but does not want customers to place orders during a specific weekly period.

This plugin allows the store to:

- remain accessible
- show a custom message to visitors
- technically block WooCommerce purchases

## Tech notes

- Uses the timezone configured in WordPress
- Does not require Elementor Pro
- Uses a native plugin modal instead of a builder-dependent popup
- Designed to be lightweight and reusable

## Roadmap / possible improvements

- Multiple schedule windows
- Optional redirection during blocked periods
- Per-page or per-category rules
- Translation support
- WordPress.org-ready packaging
- More styling controls in admin
- Optional Elementor integration as an add-on

## License

GPL-compatible license recommended for WordPress distribution.
