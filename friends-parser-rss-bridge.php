<?php
/**
 * Plugin name: Friends Parser RSS Bridge
 * Plugin author: Alex Kirk
 * Plugin URI: https://github.com/akirk/friends-parser-rss-bridge
 * Version: 1.3
 * Requires Plugins: friends
 *
 * Description: Provides the parsing capabilities of RSS Bridge.
 *
 * License: GPL2
 * Text Domain: friends
 *
 * @package Friends\Parser_RSS_Bridge
 */

/**
 * This file contains the main plugin functionality.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'friends_load_parsers',
	function( Friends\Feed $friends_feed ) {
		require_once __DIR__ . '/class-feed-parser-rss-bridge.php';
		$friends_feed->register_parser( 'rss-bridge', new Friends\Feed_Parser_RSS_Bridge );
	}
);

/**
 * Display an about page for the plugin.
 *
 * @param      bool $display_about_friends  The display about friends section.
 */
function friends_parser_rss_bridge_about_page( $display_about_friends = false ) {
	$nonce_value = 'friends-parser-rss-bridge';
	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], $nonce_value ) ) {
		update_option( 'friends-parser-rss-bridge_confidence', intval( $_POST['default_confidence'] ) );
	}

	?><h1><?php esc_html_e( 'Friends Parser RSS Bridge', 'friends' ); ?></h1>

	<form method="post">
		<?php wp_nonce_field( $nonce_value ); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Default Confidence', 'friends' ); ?></th>
					<td>
						<fieldset>
							<label for="default_confidence">
								<input name="default_confidence" type="number" id="default_confidence" placeholder="10" value="<?php echo esc_attr( get_option( 'friends-parser-rss-bridge_confidence', 10 ) ); ?>" min="0" max="100" />
							</label>
							<p class="description">
								<?php esc_html_e( 'If you set this to a higher value, this parser will take precedence over others that also say they can handle the URL.', 'friends' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'friends' ); ?>">
		</p>
	</form>

	<?php if ( $display_about_friends ) : ?>
		<p>
			<?php
			echo wp_kses(
				// translators: %s: URL to the Friends Plugin page on WordPress.org.
				sprintf( __( 'The Friends plugin is all about connecting with friends and news. Learn more on its <a href=%s>plugin page on WordPress.org</a>.', 'friends' ), '"https://wordpress.org/plugins/friends" target="_blank" rel="noopener noreferrer"' ),
				array(
					'a' => array(
						'href'   => array(),
						'rel'    => array(),
						'target' => array(),
					),
				)
			);
			?>
		</p>
	<?php endif; ?>
	<p>
	<?php
	echo wp_kses(
		// translators: %s: URL to the RSS Bridge.
		sprintf( __( 'This parser is powered by the open source project <a href=%s>RSS Bridge</a> and provides support to parse the following properties:', 'friends' ), '"https://github.com/RSS-Bridge/rss-bridge" target="_blank" rel="noopener noreferrer"' ),
		array(
			'a' => array(
				'href'   => array(),
				'rel'    => array(),
				'target' => array(),
			),
		)
	);
	?>
	</p>
	<ul>
		<?php
		if ( ! class_exists( 'Friends\Feed_Parser_RSS_Bridge' ) ) {
			if ( ! class_exists( 'Friends\Feed_Parser' ) ) {
				require_once __DIR__ . '/class-feed-parser.php';
			}
			if ( ! class_exists( 'Friends\Feed_Parser_V2' ) ) {
				require_once __DIR__ . '/class-feed-parser-v2.php';
			}
			if ( ! class_exists( 'Friends\Feed_Item' ) ) {
				require_once __DIR__ . '/class-feed-item.php';
			}
			require_once __DIR__ . '/class-feed-parser-rss-bridge.php';
		}
		$parser = new Friends\Feed_Parser_RSS_Bridge;
		foreach ( $parser->get_all_bridges() as $slug => $bridge ) {
			?>
			<li><a href="<?php echo esc_url( $bridge::URI ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $bridge::NAME ); ?></a> <?php echo esc_html( $bridge::DESCRIPTION ); ?></li>
			<?php
		}
		?>
	</ul>
	<?php
}

/**
 * Display an about page for the plugin with the friends section.
 */
function friends_parser_rss_bridge_about_page_with_friends_about() {
	return friends_parser_rss_bridge_about_page( true );
}

/**
 * Displays the RSS Bridge Tester.
 */
function friends_parser_rss_bridge_tester() {
	$url = false;
	if ( isset( $_GET['_wpnonce'], $_GET['url'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'friends_parser_rss_bridge_tester' ) ) {
		$url = $_GET['url'];
		if ( ! parse_url( $url, PHP_URL_SCHEME ) ) {
			$url = 'https://' . $url;
		}
	}
	?>
	<h1><?php esc_html_e( 'RSS Bridge Tester', 'friends' ); ?></h1>
	<p><?php esc_html_e( 'Here you can test what the parser makes of the URL you give it. ', 'friends' ); ?></h1>

	<form>
		<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>">
		<?php wp_nonce_field( 'friends_parser_rss_bridge_tester', '_wpnonce', false ); ?>
		<label><?php esc_html_e( 'Enter a URL:', 'friends' ); ?> <input type="text" name="url" value="<?php echo esc_attr( $url ); ?>" placeholer="https://" autofocus /></label>
		<input type="submit" class="button button-primary" value="<?php echo esc_attr_x( 'Parse Now', 'button', 'friends' ); ?>" />
	</form>
	<?php
	if ( $url ) {
		if ( ! class_exists( 'Friends\Feed_Parser_RSS_Bridge' ) ) {
			if ( ! class_exists( 'Friends\Feed_Parser' ) ) {
				require_once __DIR__ . '/class-feed-parser.php';
			}
			if ( ! class_exists( 'Friends\Feed_Item' ) ) {
				require_once __DIR__ . '/class-feed-item.php';
			}
			require_once __DIR__ . '/class-feed-parser-rss-bridge.php';
		}
		$parser = new Friends\Feed_Parser_RSS_Bridge;
		$bridge = $parser->get_bridge( $_GET['url'] );
		$items = $parser->fetch_feed( $_GET['url'] );
		?>
		<h2>
			<?php
			// translators: %s is a URL to be displayed verbatim.
			echo esc_html( sprintf( __( 'Parsing Result for %s', 'friends' ), $url ) );
			?>
		</h2>
		<?php
		if ( ! is_wp_error( $items ) && empty( $items ) ) {
			$items = new \WP_Error( 'empty-feed', __( "This feed doesn't contain any entries. There might be a problem parsing the feed.", 'friends' ) );
		}

		if ( is_wp_error( $items ) ) {
			?>
			<div id="message" class="updated notice is-dismissible"><p><?php echo esc_html( $items->get_error_message() ); ?></p>
			</div>
			<?php
			exit;
		}
		?>
		<h3><?php esc_html_e( 'Parser Details', 'friends' ); ?></h3>
		<ul id="parser">
			<li>
				<?php
				echo wp_kses(
					// translators: %s is the name of a Bridge = specific parser.
					sprintf( __( 'Using Bridge: %s', 'friends' ), '<a href="' . esc_url( $bridge::URI ) . '" target="_blank" rel="noopener noreferrer">' . $bridge::NAME . '</a>' ),
					array(
						'a' => array(
							'href'   => array(),
							'rel'    => array(),
							'target' => array(),
						),
					)
				);
				?>
			</li>
			<li>
				<?php
				// translators: %s is an explanation for the Bridge = specific parser.
				echo esc_html( sprintf( __( 'Bridge Description: %s', 'friends' ), $bridge::DESCRIPTION ) );
				?>
			</li>
		</ul>
		<h3><?php esc_html_e( 'Items in the Feed', 'friends' ); ?></h3>
		<ul id="items">
			<?php
			foreach ( $items as $item ) {
				?>
				<li><?php echo esc_html( $item->date ); ?>: <a href="<?php echo esc_url( $item->permalink ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $item->title ); ?></a></li>
				<?php
			}
			?>
		</ul>
		<?php
	}
}

add_action(
	'admin_menu',
	function () {
		// Only show the menu if installed standalone.
		$friends_settings_exist = '' !== menu_page_url( 'friends', false );
		if ( $friends_settings_exist ) {
			add_submenu_page(
				'friends',
				__( 'RSS Bridge', 'friends' ),
				__( 'RSS Bridge', 'friends' ),
				'edit_private_posts',
				'friends-rss-bridge',
				'friends_parser_rss_bridge_about_page'
			);
		} else {
			add_menu_page( 'friends', __( 'Friends', 'friends' ), 'edit_private_posts', 'friends', null, 'dashicons-groups', 3 );
			add_submenu_page(
				'friends',
				__( 'About', 'friends' ),
				__( 'About', 'friends' ),
				'edit_private_posts',
				'friends',
				'friends_parser_rss_bridge_about_page_with_friends_about'
			);
		}

		if ( apply_filters( 'friends_debug', false ) || ! $friends_settings_exist ) {
			add_submenu_page(
				'friends',
				__( 'RSS Bridge Tester', 'friends' ),
				__( 'RSS Bridge Tester', 'friends' ),
				'edit_private_posts',
				'friends-rss-bridge-tester',
				'friends_parser_rss_bridge_tester'
			);
		}
	},
	50
);
