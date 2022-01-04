<?php
namespace RSS_Bridge;
/**
 * This file is part of RSS-Bridge, a PHP project capable of generating RSS and
 * Atom feeds for websites that don't have one.
 *
 * For the full license information, please view the UNLICENSE file distributed
 * with this source code.
 *
 * @package	Core
 * @license	http://unlicense.org/ UNLICENSE
 * @link	https://github.com/rss-bridge/rss-bridge
 */

class Constants {
	/** Path to the root folder of RSS-Bridge (where index.php is located) */
	const PATH_ROOT = __DIR__ . '/../';

	/** Path to the core library */
	const PATH_LIB = __DIR__ . '/../lib/';

	/** Path to the vendor library */
	const PATH_LIB_VENDOR = __DIR__ . '/../vendor/';

	/** Path to the bridges library */
	const PATH_LIB_BRIDGES = __DIR__ . '/../bridges/';

	/** Path to the formats library */
	const PATH_LIB_FORMATS = __DIR__ . '/../formats/';

	/** Path to the caches library */
	const PATH_LIB_CACHES = __DIR__ . '/../caches/';

	/** Path to the actions library */
	const PATH_LIB_ACTIONS = __DIR__ . '/../actions/';

	/** Path to the cache folder */
	const PATH_CACHE = __DIR__ . '/../cache/';

	/** Path to the whitelist file */
	const WHITELIST = __DIR__ . '/../whitelist.txt';

	/** Path to the default whitelist file */
	const WHITELIST_DEFAULT = __DIR__ . '/../whitelist.default.txt';

	/** Path to the configuration file */
	const FILE_CONFIG = __DIR__ . '/../config.ini.php';

	/** Path to the default configuration file */
	const FILE_CONFIG_DEFAULT = __DIR__ . '/../config.default.ini.php';

	/** URL to the RSS-Bridge repository */
	const REPOSITORY = 'https://github.com/RSS-Bridge/rss-bridge/';
}

// Interfaces
require_once Constants::PATH_LIB . 'BridgeInterface.php';
require_once Constants::PATH_LIB . 'CacheInterface.php';

// Classes
require_once Constants::PATH_LIB . 'FactoryAbstract.php';
require_once Constants::PATH_LIB . 'FeedItem.php';
require_once Constants::PATH_LIB . 'Debug.php';
require_once Constants::PATH_LIB . 'Exceptions.php';
require_once Constants::PATH_LIB . 'BridgeFactory.php';
require_once Constants::PATH_LIB . 'BridgeAbstract.php';
require_once Constants::PATH_LIB . 'FeedExpander.php';
require_once Constants::PATH_LIB . 'CacheFactory.php';
require_once Constants::PATH_LIB . 'Authentication.php';
require_once Constants::PATH_LIB . 'Configuration.php';
require_once Constants::PATH_LIB . 'ParameterValidator.php';
require_once Constants::PATH_LIB . 'XPathAbstract.php';

// Functions
require_once Constants::PATH_LIB . 'html.php';
require_once Constants::PATH_LIB . 'error.php';
require_once Constants::PATH_LIB . 'contents.php';

// Vendor
require_once Constants::PATH_LIB_VENDOR . 'parsedown/Parsedown.php';
require_once Constants::PATH_LIB_VENDOR . 'php-urljoin/src/urljoin.php';
require_once Constants::PATH_LIB_VENDOR . 'simplehtmldom/simple_html_dom.php';
