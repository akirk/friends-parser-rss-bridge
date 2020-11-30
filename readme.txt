=== Friends Parser RSS-Bridge ===
Contributors: akirk
Requires at least: 5.0
Tested up to: 5.6
Requires PHP: 5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: trunk

View feeds for websites that don't provide machine readable feeds using RSS-Bridge and potentially support the Friends plugin with that.

== Description ==
This parser is powered by the open source project [RSS Bridge](https://github.com/RSS-Bridge/rss-bridge) and allows to parse many external services that don't provide RSS feeds, such as Skimfeed, Twitter, Bandcamp, Wikipedia and more.

The RSS Bridge project provides a set of "bridges" that overcome the gap between a web sites content and a machine readable format, such as RSS. This plugin extends the bridge into the WordPress world, especially to the Friends Plugin](https://wordpress.org/plugins/friends) which can then be used to follow your friends on platforms that don't provide RSS feeds, or some news sites.

== Installation ==
1. Upload the `friends-parser-rss-bridge` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the \'Plugins\' menu in WordPress

== Frequently Asked Questions ==

= Why is the plugin called Friends Parser RSS Bridge? =

The plugin can supplement the [Friends Plugin](https://wordpress.org/plugins/friends) to allow subscribing to sites that don't provide RSS feeds.

By itself this plugin allows to see the result of such a conversion by providing your own URLs to check.

= Is this for reading RSS feeds? WordPress can do this out of the box, so why this plugin? =

This plugin is for sites that actually don't provide RSS feeds, or other machine readable ways of consuming their data. RSS Bridge consists of "bridges" that overcome the gap between a web sites content and a machine readable format, such as RSS.

== Changelog ==
= 1.0 =
- Initial submission
