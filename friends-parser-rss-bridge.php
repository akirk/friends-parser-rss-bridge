<?php
/**
 * Plugin name: Friends Parser RSS-Bridge
 * Plugin author: Alex Kirk
 * Plugin URI: https://github.com/akirk/friends
 * Version: 1.0
 *
 * Description: A parser for the Friends plugin using RSS-Bridge.
 *
 * License: GPL2
 * Text Domain: friends-parser-rss-bridge
 * Domain Path: /languages/
 *
 * @package Friends
 */

/**
 * This file loads all the dependencies the Friends plugin.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'friends_register_parser',
	function( Friends_Feed $friends_feed ) {
		include __DIR__ . '/class-friends-feed-parser-rss-bridge.php';
		$friends_feed->register_parser( 'rss-bridge', new Friends_Feed_Parser_RSS_Bridge );
	}
);
