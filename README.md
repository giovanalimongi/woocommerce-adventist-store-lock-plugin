# WooCommerce Adventist Store Lock

A WordPress plugin that blocks purchases on a recurring weekly schedule and displays a native full-screen modal during the blocked period.

## Features

- Recurring weekly lock schedule
- Native modal with no Elementor dependency
- Optional close button
- Optional cart/checkout notice
- Blocks add-to-cart and product purchases
- Admin settings page under **WooCommerce > Store Lock**
- Native modal with optional image support
- Countdown timer showing remaining time until the store reopens
- Improved admin settings UX with a more structured layout
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
  - countdown visibility

## Notes

- Uses the timezone configured in WordPress
- The lock state is checked on page load
- Designed as a lightweight, self-contained plugin


## Latest update

### v1.2.0

- Native modal now supports **text**, **image**, or **image + text** content modes.
- Admin settings now include a WordPress Media Library image selector.
- Added a direct **Settings** link on the Plugins screen.

### v1.3.0
- Added countdown timer to the modal during blocked periods
- Improved admin UX with a more organized settings interface
- Added color picker support
- Improved modal customization workflow
