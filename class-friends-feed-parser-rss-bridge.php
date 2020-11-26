<?php
/**
 * Friends RSS_Bridge Parser Wrapper
 *
 * With this parser, we can import RSS and Atom Feeds for a friend.
 *
 * @package Friends
 */

/**
 * This is the class for the feed part of the Friends Plugin.
 *
 * @since 1.0
 *
 * @package Friends
 * @author Alex Kirk
 */
class Friends_Feed_Parser_RSS_Bridge extends Friends_Feed_Parser {
	/**
	 * Cache for bridge parameters.
	 *
	 * @var        array
	 */
	private $cache = array();
	/**
	 * Is RSS Bridge functional?
	 *
	 * @var        array
	 */
	private $enabled = true;

	/**
	 * Determines if this is a supported feed.
	 *
	 * @param      string $url        The url.
	 * @param      string $mime_type  The mime type.
	 * @param      string $title      The title.
	 *
	 * @return     boolean  True if supported feed, False otherwise.
	 */
	public function is_supported_feed( $url, $mime_type, $title ) {
		return false !== $this->get_bridge( $url );
	}

	/**
	 * Get the bridge for the feed.
	 *
	 * @param      string $url        The url.
	 *
	 * @return     BridgeAbstract|boolean  False if unsupported, the bridge otherwise.
	 */
	public function get_bridge( $url ) {
		if ( ! $this->enabled ) {
			return false;
		}
		if ( isset( $this->cache[ $url ] ) ) {
			return $this->cache[ $url ];
		}
		require_once( __DIR__ . '/libs/rss-bridge/lib/rssbridge.php' );

		try {
			Friends_RSS_Bridge\Configuration::verifyInstallation();
			Friends_RSS_Bridge\Configuration::loadConfiguration();
		} catch ( Exception $e ) {
			$this->enabled = false;
			return false;
		}

		$bridge_factory = new Friends_RSS_Bridge\BridgeFactory();
		$bridge_factory->setWorkingDir( PATH_LIB_BRIDGES );

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
	 * @param      array $feed_details  The feed details.
	 *
	 * @return     array  The (potentially) modified feed details.
	 */
	public function update_feed_details( $feed_details ) {
		if ( ! isset( $feed_details['url'] ) ) {
			return $feed_details;
		}

		$bridge = $this->get_bridge( $feed_details['url'] );
		if ( false === $bridge ) {
			return $feed_details;
		}
		$bridge_name = substr( get_class( $bridge ), 19, -6 );

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
	 * Gets a suggested post format for a bridge.
	 *
	 * @param      string $bridge_name  The bridge name.
	 *
	 * @return     string  The post format.
	 */
	public function get_post_format( $bridge_name ) {
		switch ( $bridge_name ) {
			case 'Twitter':
				return 'status';
		}

		return 'standard';
	}

	/**
	 * Fetches a feed and returns the processed items.
	 *
	 * @param      string $url        The url.
	 *
	 * @return     array            An array of feed items.
	 */
	public function fetch_feed( $url ) {
		$bridge = $this->get_bridge( $url );
		if ( false === $bridge ) {
			return $feed_details;
		}

		$bridge_params = $bridge->detectParameters( $url );
		if ( is_null( $bridge_params ) ) {
			return array();
		}
		try {
			$bridge->setDatas( $bridge_params );
			$bridge->collectData();
		} catch ( Exception $e ) {
			return new Wp_Error( 'RSS-Bridge Parser', $e->getMessage() );
		}

		$feed_items = array();
		foreach ( $bridge->getItems() as $item ) {
			$item = new Friends_RSS_Bridge\FeedItem( $item );

			$feed_item = (object) array(
				'permalink' => $item->getURI(),
				'title'     => $item->getTitle(),
				'content'   => $item->getContent(),
			);
			$feed_item->date = gmdate( 'Y-m-d H:i:s', $item->getTimestamp() );

			$feed_items[] = $feed_item;
		}
		return $feed_items;
	}
}
