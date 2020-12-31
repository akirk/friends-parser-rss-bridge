<?php
/**
 * Friends RSS_Bridge Parser Wrapper
 *
 * With this parser, we can import RSS and Atom Feeds for a friend.
 *
 * @package Friends_Parser_RSS_Bridge
 */

/**
 * This is the class for the Friends Parser RSS_Bridge.
 *
 * @since 1.0
 *
 * @package Friends_Parser_RSS_Bridge
 * @author  Alex Kirk
 */
class Friends_Feed_Parser_RSS_Bridge extends Friends_Feed_Parser {

	const NAME = 'RSS Bridge';
	const URL  = 'https://github.com/akirk/friends-parser-rss-bridge';
	// referencing the fork to better explain where the code is coming from.

	/**
	 * Cache for bridge parameters.
	 *
	 * @var array
	 */
	private $cache = array();

	/**
	 * Is RSS Bridge functional?
	 *
	 * @var array
	 */
	private $enabled = true;


	/**
	 * Determines if this is a supported feed and to what degree we feel it's supported.
	 *
	 * @param      string      $url        The url.
	 * @param      string      $mime_type  The mime type.
	 * @param      string      $title      The title.
	 * @param      string|null $content    The content, it can't be assumed that it's always available.
	 *
	 * @return     int  Return 0 if unsupported, a positive value representing the confidence for the feed, use 10 if you're reasonably confident.
	 */
	public function feed_support_confidence( $url, $mime_type, $title, $content = null ) {
		if ( $this->get_bridge( $url ) ) {
			return get_option( 'friends-parser-rss-bridge_confidence', 10 );
		}

		return 0;
	}


	/**
	 * Gets the bridge factory.
	 *
	 * @return RSS_Bridge|false  The bridge factory.
	 */
	private function get_bridge_factory() {
		global $path_lib_bridges;
		static $bridge_factory;

		if ( ! $this->enabled ) {
			return false;
		}

		if ( isset( $bridge_factory ) ) {
			return $bridge_factory;
		}

		include_once __DIR__ . '/libs/rss-bridge/lib/rssbridge.php';

		try {
			RSS_Bridge\Configuration::verifyInstallation();
			RSS_Bridge\Configuration::loadConfiguration();
		} catch ( Exception $e ) {
			$this->enabled  = false;
			$bridge_factory = false;
			return $bridge_factory;
		}

		$bridge_factory = new RSS_Bridge\BridgeFactory();
		$bridge_factory->setWorkingDir( RSS_Bridge\Constants::PATH_LIB_BRIDGES );

		return $bridge_factory;
	}


	/**
	 * Get the bridge for the feed.
	 *
	 * @param string $url The url.
	 *
	 * @return BridgeAbstract|boolean  False if unsupported, the bridge otherwise.
	 */
	public function get_bridge( $url ) {
		if ( isset( $this->cache[ $url ] ) ) {
			return $this->cache[ $url ];
		}

		$bridge_factory = $this->get_bridge_factory();
		if ( ! $bridge_factory ) {
			return false;
		}

		foreach ( $bridge_factory->getBridgeNames() as $bridge_name ) {
			$bridge = $bridge_factory->create( $bridge_name );

			if ( false === $bridge ) {
				continue;
			}

			$bridge_params = $bridge->detectParameters( $url );

			if ( is_null( $bridge_params ) ) {
				continue;
			}

			$this->cache[ $url ] = $bridge;

			return $bridge;
		}

		$this->cache[ $url ] = false;

		return false;
	}


	/**
	 * Format the feed title and autoselect the posts feed.
	 *
	 * @param array $feed_details The feed details.
	 *
	 * @return array  The (potentially) modified feed details.
	 */
	public function update_feed_details( $feed_details ) {
		if ( ! isset( $feed_details['url'] ) ) {
			return $feed_details;
		}

		$bridge = $this->get_bridge( $feed_details['url'] );
		if ( false === $bridge ) {
			return $feed_details;
		}

		$bridge_name = $this->get_bridge_name( $bridge );

		$bridge_params = $bridge->detectParameters( $feed_details['url'] );
		if ( is_null( $bridge_params ) ) {
			return $feed_details;
		}

		$feed_details['type'] = 'application/rss-bridge-' . strtolower( $bridge_name );

		$bridge->setDatas( $bridge_params );

		if ( empty( $feed_details['title'] ) ) {
			$feed_details['title'] = $bridge->getName();
		}

		$feed_details['post-format'] = $this->get_post_format( $bridge_name );

		return $feed_details;
	}


	/**
	 * Gets the bridge name.
	 *
	 * @param RSS_Bridge\BridgeAbstract $bridge The bridge.
	 *
	 * @return string  The bridge name.
	 */
	public function get_bridge_name( $bridge = null ) {
		if ( is_null( $bridge ) ) {
			return __( 'Unknown Parser', 'friends-parser-rss-bridge' );
		}

		if ( is_object( $bridge ) ) {
			$bridge = get_class( $bridge );
		}

		return substr( $bridge, 11, -6 );
	}


	/**
	 * Gets all bridges.
	 *
	 * @return array  All bridges.
	 */
	public function get_all_bridges() {
		$bridge_factory = $this->get_bridge_factory();
		if ( ! $bridge_factory ) {
			return array();
		}

		$bridges = array();
		foreach ( $bridge_factory->getBridgeNames() as $bridge_name ) {
			$bridge = $bridge_factory->create( $bridge_name );

			if ( false === $bridge ) {
				continue;
			}

			$bridges[ $bridge_name ] = $bridge;
		}

		return $bridges;
	}


	/**
	 * Gets a suggested post format for a bridge.
	 *
	 * @param string $bridge_name The bridge name.
	 *
	 * @return string  The post format.
	 */
	public function get_post_format( $bridge_name ) {
		switch ( $bridge_name ) {
			case 'Twitter':
				return 'status';
			case 'Dilbert':
				return 'image';
		}

		return 'standard';
	}


	/**
	 * Fetches a feed and returns the processed items.
	 *
	 * @param string $url The url.
	 *
	 * @return array            An array of feed items.
	 */
	public function fetch_feed( $url ) {
		$bridge = $this->get_bridge( $url );
		if ( false === $bridge ) {
			// translators: %s is a URL.
			return new Wp_Error( 'RSS-Bridge Parser', sprintf( __( 'No suitable parser available for %s.', 'friends-parser-rss-bridge' ), $url ) );
		}

		$bridge_params = $bridge->detectParameters( $url );
		if ( is_null( $bridge_params ) ) {
			// translators: 1: is a URL, 2: a Parser name.
			return new Wp_Error( 'RSS-Bridge Parser', sprintf( __( 'Error analyzing %1$s with %2$s.', 'friends-parser-rss-bridge' ), $url, $this->get_bridge_name( $bridge ) ) );
		}

		try {
			$bridge->setDatas( $bridge_params );
			$bridge->collectData();
		} catch ( Exception $e ) {
			return new Wp_Error( 'RSS-Bridge Parser', $e->getMessage() );
		}

		$feed_items = array();
		foreach ( $bridge->getItems() as $item ) {
			$item = new RSS_Bridge\FeedItem( (object) $item );

			$feed_item = new Friends_Feed_Item(
				array(
					'permalink'   => $item->getURI(),
					'title'       => $item->getTitle(),
					'content'     => $item->getContent(),
					'post_format' => $item->getPostFormat(),
				)
			);

			$feed_item->date = gmdate( 'Y-m-d H:i:s', $item->getTimestamp() );

			$feed_items[] = $feed_item;
		}

		return $feed_items;
	}

}
