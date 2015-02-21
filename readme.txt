=== Google Maps Retailers ===
Contributors: graphicfusion
Tags: map, google map, google maps, retailers, store locator, custom post type, united states, retail
Requires at least: 3.0.1
Tested up to: 4.1.1
Stable tag: /trunk/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin to manage and output retailers by region on a Google Map.

== Description ==
This plugin lets you create state regions on top of a Google Map then add a popup box with external links. Currently supports only USA map

Features:
- Have unique color for a region 
- Ability to add external links to a state

Once the plugin is installed
Use [fusion_retailers_map] or place `<?php echo do_shortcode('[fusion_retailers_map]'); ?>` in your templates to output the necessary HTML markup for the map.

Got questions or comments? Visit us at [Graphic Fusion Design](http://graphicfusiondesign.com "Tucson Web Design”)


== Installation ==

1. Upload the `fusion-retailers` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the shortcode [fusion_retailers_map] or place `<?php echo do_shortcode('[fusion_retailers_map]'); ?>` in your templates to output the necessary HTML markup for the map.
1. Add retailers by clicking on the new 'Retailers' menu item in the WordPress admin.

== Frequently Asked Questions ==

= How do I add retailers to the map? =

You add retailers to the map much like you would a post or page in WordPress. Once the plugin is activated, you will get another menu item called 'Retailers' in the sidebar. Click here, and add a new retailer. Make sure to select the states you want the retailer to show up in.

= How can I change the colors of the states output to the map? =

In wp-admin, go to the 'Retailers' menu item and click on 'Retailer Settings'. Adjust the colors to your liking, and press 'Submit'

= What if I find a bug or have a suggestion for improvements? =

Let us know. You can contact us through our website [Graphic Fusion Design](http://graphicfusiondesign.com/support "Web Design Tucson")

== Screenshots ==

1. The map that is output using the `[fusion_retailers_map]` shortcode.
2. A list of retailers for a given state, brought up by clicking on the state.
3. Example of adding a new retailer to the map.
4. The admin page for managing the colors of the states.

== Changelog ==

= 1.0.1 =
* Added css fix for map when images have a max-width set.

= 1.0 =
* First public release

== Upgrade Notice ==

= 1.0.1 =
Fixed css display issues when images had a max-width set.

= 1.0 =
First release to public
