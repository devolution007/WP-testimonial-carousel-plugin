=== Testimonial Carousel ===
Contributors: testimonialcarousel
Tags: testimonials, carousel, shortcode, popup, school
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage testimonials in WordPress and display them in a responsive carousel with animated popups.

== Description ==

Testimonial Carousel adds a Testimonials area to the WordPress dashboard. Each testimonial has editable content, footer name, optional relationship line, campus, avatar initials, and display order.

The front-end carousel includes:

* Three cards on desktop, two on tablet, and one on mobile.
* Equal-height cards.
* Autoplay with pause on hover, focus, touch, and popup opening.
* Previous and next controls plus swipe support.
* An animated, accessible popup for the full testimonial.
* Escape-key, close-button, and backdrop closing.
* Reduced-motion support.

The plugin imports the 12 supplied testimonials once when first activated on a site with no existing plugin testimonials.

== Installation ==

1. Upload the `testimonial-carousel` folder to `/wp-content/plugins/`, or install the ZIP from Plugins > Add New > Upload Plugin.
2. Activate **Testimonial Carousel**.
3. Open **Testimonials** in the WordPress dashboard to manage the imported testimonials.
4. Add `[testimonial-carousel]` to a page, post, shortcode block, or Elementor Shortcode widget.

== Shortcode ==

Default:

`[testimonial-carousel]`

Disable autoplay:

`[testimonial-carousel autoplay="no"]`

Set the autoplay interval in milliseconds:

`[testimonial-carousel interval="6000"]`

Show one campus only:

`[testimonial-carousel campus="Hifz Campus"]`

Attributes can be combined.

== Managing Testimonials ==

* The post title is an internal label and fallback footer name.
* The main editor contains only the testimonial body.
* Footer name, relationship, campus, and avatar initials are stored in the Testimonial Footer Details box.
* Use the Order field in Page Attributes to set the card order. Lower numbers appear first.
* Publish a testimonial to show it. Draft or trash it to hide it.

== Import and Export ==

Open **Testimonials > Import / Export** in the WordPress dashboard.

* Export downloads all published and unpublished testimonials as a portable JSON file.
* The export includes testimonial content, footer fields, campus, initials, order, and publication status.
* The recommended import mode updates matching testimonials and adds new ones without duplicating existing records.
* The optional append mode imports every record as a new testimonial.
* Import files are limited to 5 MB and 2,000 testimonials.

== Changelog ==

= 1.1.3 =
* Neutralized theme-level button hover styles and the parent-card hover while View more is hovered.

= 1.1.2 =
* Removed the View more mouse-hover effect while retaining keyboard focus visibility.

= 1.1.1 =
* Added a clear hover and keyboard-focus treatment to the View more control.

= 1.1.0 =
* Added JSON import and export tools under Testimonials > Import / Export.
* Added migration identifiers for safe update-or-add imports.
* Added a ready-to-import file containing the 12 bundled testimonials.

= 1.0.2 =
* Updated the plugin author to Devolution.

= 1.0.1 =
* Initial release.
* Includes the managed testimonial post type, 12 seed testimonials, responsive carousel, autoplay, and animated full-text popup.


